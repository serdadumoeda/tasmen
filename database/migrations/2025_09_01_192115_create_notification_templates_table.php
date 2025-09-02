<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('subject');
            $table->text('body');
            $table->text('description')->nullable(); // To explain what placeholders are available
            $table->timestamps();
        });

        // Seed with a default template
        DB::table('notification_templates')->insert([
            [
                'key' => 'task_assigned',
                'subject' => 'Anda mendapatkan tugas baru: {{task_title}}',
                'body' => "Halo {{user_name}},\n\nAnda telah ditugaskan untuk mengerjakan tugas baru berjudul \"{{task_title}}\" dalam kegiatan \"{{project_title}}\".\n\nSilakan periksa detailnya di aplikasi.\n\nTerima kasih.",
                'description' => 'Tersedia placeholder: {{user_name}}, {{task_title}}, {{project_title}}',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
