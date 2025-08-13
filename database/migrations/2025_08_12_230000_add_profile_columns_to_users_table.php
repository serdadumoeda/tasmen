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
            $table->string('nip')->unique()->nullable()->after('id');
            $table->string('tempat_lahir')->nullable()->after('name');
            $table->date('tgl_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tgl_lahir');
            $table->string('jenis_kelamin', 1)->nullable()->after('alamat');
            $table->string('agama')->nullable()->after('jenis_kelamin');
            $table->string('golongan')->nullable()->after('agama');
            $table->string('eselon')->nullable()->after('golongan');
            $table->date('tmt_eselon')->nullable()->after('eselon');
            $table->string('grade')->nullable()->after('tmt_eselon');
            $table->string('no_hp')->nullable()->after('grade');
            $table->string('telepon')->nullable()->after('no_hp');
            $table->string('npwp')->nullable()->after('telepon');
            $table->string('pendidikan_terakhir')->nullable()->after('npwp');
            $table->string('pendidikan_jurusan')->nullable()->after('pendidikan_terakhir');
            $table->string('pendidikan_universitas')->nullable()->after('pendidikan_jurusan');
            $table->string('jenis_jabatan')->nullable()->after('pendidikan_universitas');
            $table->date('tmt_cpns')->nullable()->after('jenis_jabatan');
            $table->date('tmt_pns')->nullable()->after('tmt_cpns');
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
                'tgl_lahir',
                'alamat',
                'jenis_kelamin',
                'agama',
                'golongan',
                'eselon',
                'tmt_eselon',
                'grade',
                'no_hp',
                'telepon',
                'npwp',
                'pendidikan_terakhir',
                'pendidikan_jurusan',
                'pendidikan_universitas',
                'jenis_jabatan',
                'tmt_cpns',
                'tmt_pns',
            ]);
        });
    }
};
