<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\PeminjamanRequest;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable()->after('status')->constrained('peminjaman_request_statuses');
        });

        // Data migration
        $statuses = DB::table('peminjaman_request_statuses')->pluck('id', 'key');
        PeminjamanRequest::all()->each(function ($request) use ($statuses) {
            $statusId = $statuses[strtolower($request->status)] ?? null;
            if ($statusId) {
                $request->update(['status_id' => $statusId]);
            }
        });

        // Make the new column not nullable and drop the old one
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->foreignId('status_id')->nullable(false)->change();
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('status_id');
        });

        // Restore old data
        $statuses = DB::table('peminjaman_request_statuses')->pluck('key', 'id');
        PeminjamanRequest::all()->each(function ($request) use ($statuses) {
            $statusKey = $statuses[$request->status_id] ?? 'pending';
            $request->update(['status' => $statusKey]);
        });

        Schema::table('peminjaman_requests', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
        });
    }
};
