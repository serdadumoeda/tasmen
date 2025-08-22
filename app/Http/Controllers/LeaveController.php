<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get the user's own leave requests
        $myRequests = LeaveRequest::where('user_id', $user->id)
            ->with('leaveType', 'approver')
            ->orderBy('start_date', 'desc')
            ->get();

        // Get leave requests awaiting the user's approval
        $approvalRequests = LeaveRequest::where('current_approver_id', $user->id)
            ->where('status', '!=', 'approved')
            ->where('status', '!=', 'rejected')
            ->with('user', 'leaveType')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('leaves.index', compact('myRequests', 'approvalRequests'));
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
        $duration = $startDate->diffInDaysFiltered(fn(Carbon $date) => !$date->isWeekend(), $endDate) + 1;

        // For annual leave, check balance
        if ($leaveType->name === 'Cuti Tahunan') {
            $balance = LeaveBalance::where('user_id', $user->id)->where('year', $startDate->year)->first();
            if (!$balance || ($balance->total_days - $balance->days_taken) < $duration) {
                return back()->with('error', 'Sisa cuti tahunan tidak mencukupi.')->withInput();
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
        ]);

        // Placeholder for notification
        // if ($user->atasan) {
        //     $user->atasan->notify(new \App\Notifications\LeaveRequestSubmitted($leaveRequest));
        // }

        return redirect()->route('leaves.index')->with('success', 'Permintaan cuti berhasil diajukan.');
    }

    public function approve(LeaveRequest $leaveRequest)
    {
        $approver = Auth::user();

        if ($leaveRequest->current_approver_id !== $approver->id) {
            return back()->with('error', 'Anda tidak memiliki wewenang untuk menyetujui permintaan ini.');
        }

        // Level 1 Approval (Direct Supervisor)
        if ($leaveRequest->status === 'pending') {
            $nextApprover = $approver->atasan; // The supervisor's supervisor

            // If there is a next-level approver, forward it to them.
            if ($nextApprover) {
                $leaveRequest->status = 'approved_by_supervisor';
                $leaveRequest->current_approver_id = $nextApprover->id;
                $leaveRequest->save();
                // Placeholder for notification to next approver
                return back()->with('success', 'Permintaan cuti telah disetujui dan diteruskan ke pejabat berwenang.');
            }
        }

        // Final Approval (either by Level 1 if no Level 2, or by Level 2)
        DB::transaction(function () use ($leaveRequest) {
            $leaveRequest->status = 'approved';
            $leaveRequest->current_approver_id = null;
            $leaveRequest->save();

            // Update leave balance only for 'Cuti Tahunan'
            if ($leaveRequest->leaveType->name === 'Cuti Tahunan') {
                $balance = LeaveBalance::firstOrCreate(
                    ['user_id' => $leaveRequest->user_id, 'year' => $leaveRequest->start_date->year],
                    ['total_days' => 12] // Default to 12 if no record exists
                );
                $balance->days_taken += $leaveRequest->duration_days;
                $balance->save();
            }
        });

        // Placeholder for notification to user
        return back()->with('success', 'Permintaan cuti telah disetujui sepenuhnya.');
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

        // Placeholder for notification to user
        return back()->with('success', 'Permintaan cuti telah ditolak.');
    }
}
