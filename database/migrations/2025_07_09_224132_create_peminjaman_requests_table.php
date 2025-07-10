<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjaman_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade'); // Yang meminta (Koordinator A)
            $table->foreignId('requested_user_id')->constrained('users')->onDelete('cascade'); // User yang diminta (Anggota tim B)
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade'); // Yang menyetujui (Koordinator B)
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('message')->nullable(); // Pesan dari requester
            $table->text('rejection_reason')->nullable(); // Alasan jika ditolak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjaman_requests');
    }
};