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
            $table->text('alamat')->nullable()->after('tempat_lahir');
            $table->date('tgl_lahir')->nullable()->after('alamat');
            $table->char('jenis_kelamin', 1)->nullable()->after('tgl_lahir');
            $table->string('golongan')->nullable()->after('jenis_kelamin');
            $table->string('eselon')->nullable()->after('golongan');
            $table->date('tmt_eselon')->nullable()->after('eselon');
            $table->integer('grade')->nullable()->after('tmt_eselon');
            $table->string('agama')->nullable()->after('grade');
            $table->string('telepon')->nullable()->after('agama');
            $table->string('no_hp')->nullable()->after('telepon');
            $table->string('npwp')->nullable()->after('no_hp');
            $table->string('pendidikan_terakhir')->nullable()->after('npwp');
            $table->string('pendidikan_jurusan')->nullable()->after('pendidikan_terakhir');
            $table->string('pendidikan_instansi')->nullable()->after('pendidikan_jurusan');
            $table->string('jenis_jabatan')->nullable()->after('pendidikan_instansi');
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
                'nip', 'tempat_lahir', 'alamat', 'tgl_lahir', 'jenis_kelamin',
                'golongan', 'eselon', 'tmt_eselon', 'grade', 'agama', 'telepon',
                'no_hp', 'npwp', 'pendidikan_terakhir', 'pendidikan_jurusan',
                'pendidikan_instansi', 'jenis_jabatan', 'tmt_cpns', 'tmt_pns'
            ]);
        });
    }
};
