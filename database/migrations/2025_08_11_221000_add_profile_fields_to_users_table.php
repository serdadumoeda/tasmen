<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nip')->nullable()->after('email');
            $table->string('tempat_lahir')->nullable()->after('nip');
            $table->text('alamat')->nullable()->after('tempat_lahir');
            $table->string('tgl_lahir')->nullable()->after('alamat');
            $table->string('jenis_kelamin')->nullable()->after('tgl_lahir');
            $table->string('golongan')->nullable()->after('jenis_kelamin');
            $table->string('eselon')->nullable()->after('golongan');
            $table->string('tmt_eselon')->nullable()->after('eselon');
            $table->string('grade')->nullable()->after('tmt_eselon');
            $table->string('agama')->nullable()->after('grade');
            $table->string('telepon')->nullable()->after('agama');
            $table->string('no_hp')->nullable()->after('telepon');
            $table->string('npwp')->nullable()->after('no_hp');
            $table->string('tmt_gol')->nullable()->after('npwp');
            $table->string('pendidikan_terakhir')->nullable()->after('tmt_gol');
            $table->string('jenis_jabatan')->nullable()->after('pendidikan_terakhir');
            $table->string('tmt_cpns')->nullable()->after('jenis_jabatan');
            $table->string('tmt_pns')->nullable()->after('tmt_cpns');
            $table->string('tmt_jabatan')->nullable()->after('tmt_pns');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nip',
                'tempat_lahir',
                'alamat',
                'tgl_lahir',
                'jenis_kelamin',
                'golongan',
                'eselon',
                'tmt_eselon',
                'grade',
                'agama',
                'telepon',
                'no_hp',
                'npwp',
                'tmt_gol',
                'pendidikan_terakhir',
                'jenis_jabatan',
                'tmt_cpns',
                'tmt_pns',
                'tmt_jabatan',
            ]);
        });
    }
};
