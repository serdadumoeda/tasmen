<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use App\Models\Unit;
use App\Notifications\LeaveRequestForwarded;
use App\Notifications\LeaveRequestStatusUpdated;
use App\Notifications\LeaveRequestSubmitted;
use App\Services\LeaveApprovalService;
use App\Services\LeaveDurationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class LeaveController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $user = Auth::user();

        // Get the user's own leave requests
        $myRequests = LeaveRequest::where('user_id', $user->id)
            ->with('leaveType', 'approver')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get leave requests for the manager's direct subordinates
        $subordinateIds = $user->bawahan()->pluck('id');

        $approvalRequestsQuery = LeaveRequest::whereIn('user_id', $subordinateIds)
            ->whereIn('status', ['pending', 'approved_by_supervisor'])
            ->with('user.unit', 'leaveType');

        // Apply filters
        if ($request->filled('filter_unit')) {
            $approvalRequestsQuery->whereHas('user', function ($query) use ($request) {
                $query->where('unit_id', $request->filter_unit);
            });
        }

        if ($request->filled('filter_status')) {
            $approvalRequestsQuery->where('status', $request->filter_status);
        }

        // Get all units of the direct subordinates for the filter dropdown
        $unitsInHierarchy = Unit::whereIn('id', $user->bawahan()->select('unit_id')->distinct())->get();

        $approvalRequests = $approvalRequestsQuery->orderBy('created_at', 'asc')->get();

        // Get the user's annual leave balance for the summary
        $annualLeaveBalance = LeaveBalance::firstOrCreate(
            ['user_id' => $user->id, 'year' => now()->year],
            ['total_days' => 12, 'carried_over_days' => 0] // Defaults for new users
        );

        return view('leaves.index', compact('myRequests', 'approvalRequests', 'unitsInHierarchy', 'annualLeaveBalance'));
    }

    public function calendar()
    {
        $user = Auth::user();
        $teamIds = $user->getAllSubordinateIds();
        $teamIds[] = $user->id; // Include the manager themselves in the calendar

        $leaves = LeaveRequest::whereIn('user_id', $teamIds)
            ->where('status', 'approved')
            ->with('user')
            ->get();

        $events = $leaves->map(function ($leave) {
            // FullCalendar's end date is exclusive, so add a day.
            $endDate = $leave->end_date->addDay()->format('Y-m-d');

            return [
                'title' => $leave->user->name,
                'start' => $leave->start_date->format('Y-m-d'),
                'end' => $endDate,
                'allDay' => true,
            ];
        });

        return view('leaves.calendar', ['events' => $events->toJson()]);
    }

    public function create()
    {
        $leaveTypes = LeaveType::orderBy('name')->get();
        $user = Auth::user();
        $currentYear = now()->year;

        $annualLeaveBalance = LeaveBalance::where('user_id', $user->id)
            ->where('year', $currentYear)
            ->first();

        // If no balance record exists for the current year, create one based on Cuti Tahunan default
        if (!$annualLeaveBalance) {
            $annualLeaveType = LeaveType::where('name', 'Cuti Tahunan')->first();
            $defaultDays = $annualLeaveType->default_days ?? 12;
            $annualLeaveBalance = LeaveBalance::create([
                'user_id' => $user->id,
                'year' => $currentYear,
                'total_days' => $defaultDays,
                'days_taken' => 0,
            ]);
        }

        $remainingDays = $annualLeaveBalance->total_days - $annualLeaveBalance->days_taken;

        return view('leaves.create', compact('leaveTypes', 'remainingDays'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorize('view', $leaveRequest); // Placeholder for authorization
        $leaveRequest->load('user', 'leaveType');
        return view('leaves.show', compact('leaveRequest'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'address_during_leave' => 'nullable|string|max:255',
            'contact_during_leave' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $leaveType = LeaveType::find($validated['leave_type_id']);
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);

        // Use the new service to calculate the duration accurately
        $durationService = new LeaveDurationService();
        $duration = $durationService->calculate($startDate, $endDate);

        // For annual leave, check balance
        if ($leaveType->name === 'Cuti Tahunan') {
            $balance = LeaveBalance::where('user_id', $user->id)->where('year', $startDate->year)->first();
            $remainingCarriedOver = $balance ? $balance->carried_over_days : 0;
            $remainingAnnual = $balance ? ($balance->total_days - $balance->days_taken) : 0;
            $totalRemaining = $remainingCarriedOver + $remainingAnnual;

            if (!$balance || $totalRemaining < $duration) {
                return back()->with('error', "Sisa cuti tahunan tidak mencukupi. Total sisa cuti Anda: {$totalRemaining} hari.")->withInput();
            }
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leave_attachments', 'private');
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_days' => $duration,
            'reason' => $validated['reason'],
            'address_during_leave' => $validated['address_during_leave'],
            'contact_during_leave' => $validated['contact_during_leave'],
            'status' => 'pending',
            'current_approver_id' => $user->atasan_id,
            'attachment_path' => $attachmentPath,
            'last_approved_step' => 0,
        ]);

        // Notify the direct supervisor
        if ($user->atasan) {
            $user->atasan->notify(new LeaveRequestSubmitted($leaveRequest));
        }

        return redirect()->route('leaves.index')->with('success', 'Permintaan cuti berhasil diajukan.');
    }

    public function downloadAttachment(LeaveRequest $leaveRequest)
    {
        $user = Auth::user();

        // Authorization: User must be the owner OR a manager of the owner.
        $isOwner = $leaveRequest->user_id === $user->id;
        $subordinateIds = $user->getAllSubordinateIds();
        $isManager = in_array($leaveRequest->user_id, $subordinateIds);

        if (!$isOwner && !$isManager) {
            abort(403, 'Unauthorized action.');
        }

        if (!$leaveRequest->attachment_path || !Storage::disk('private')->exists($leaveRequest->attachment_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('private')->download($leaveRequest->attachment_path);
    }

    public function approve(LeaveRequest $leaveRequest, LeaveApprovalService $approvalService)
    {
        $approver = Auth::user();

        if ($leaveRequest->current_approver_id !== $approver->id) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menyetujui permintaan ini.');
        }

        // Delegate the logic to the service
        $nextState = $approvalService->processApproval($leaveRequest, $approver);

        // Update the request based on the service's decision
        $leaveRequest->status = $nextState['status'];
        $leaveRequest->current_approver_id = $nextState['next_approver_id'];
        $leaveRequest->last_approved_step = $nextState['last_approved_step'];

        // Handle final approval logic (database transaction and notification)
        if ($nextState['status'] === 'approved') {
            DB::transaction(function () use ($leaveRequest) {
                $leaveRequest->save();

                // Update leave balance only for 'Cuti Tahunan'
                if ($leaveRequest->leaveType->name === 'Cuti Tahunan') {
                    $balance = LeaveBalance::firstOrCreate(
                        ['user_id' => $leaveRequest->user_id, 'year' => $leaveRequest->start_date->year],
                        ['total_days' => 12, 'carried_over_days' => 0]
                    );

                    $daysToDeduct = $leaveRequest->duration_days;
                    $deductedFromCarryOver = min($balance->carried_over_days, $daysToDeduct);
                    $balance->carried_over_days -= $deductedFromCarryOver;
                    $daysToDeduct -= $deductedFromCarryOver;

                    if ($daysToDeduct > 0) {
                        $balance->days_taken += $daysToDeduct;
                    }
                    $balance->save();
                }
            });

            // Notify the applicant of the final approval
            $leaveRequest->user->notify(new LeaveRequestStatusUpdated($leaveRequest));
            return back()->with('success', 'Permintaan cuti telah disetujui sepenuhnya.');

        } else {
            // This is a forwarded approval
            $leaveRequest->save();
            $nextApprover = User::find($nextState['next_approver_id']);
            if ($nextApprover) {
                $nextApprover->notify(new LeaveRequestForwarded($leaveRequest));
            }
            return back()->with('success', 'Permintaan cuti telah disetujui dan diteruskan ke pejabat berwenang.');
        }
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $approver = Auth::user();

        if ($leaveRequest->current_approver_id !== $approver->id) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menolak permintaan ini.');
        }

        $validated = $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $leaveRequest->status = 'rejected';
        $leaveRequest->rejection_reason = $validated['rejection_reason'];
        $leaveRequest->current_approver_id = null;
        $leaveRequest->save();

        // Notify the applicant of the rejection
        $leaveRequest->user->notify(new LeaveRequestStatusUpdated($leaveRequest));

        return back()->with('success', 'Permintaan cuti telah ditolak.');
    }
}
