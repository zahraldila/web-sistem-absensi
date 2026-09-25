<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan kolom `created_by` dan `source` ke tabel absensi & pengajuan
     * agar sistem dapat mencatat:
     *   - Siapa admin/role yang membuat data (created_by → akun.id)
     *   - Dari mana data berasal (source: MANUAL, EDC, NFC, GPS, WIFI)
     *
     * Kedua kolom nullable agar data existing tidak terpengaruh.
     *
     * Juga menambahkan privilege `catat_absensi` untuk fitur admin
     * mencatat kondisi absensi pegawai.
     */
    public function up(): void
    {
        // 1. Tambah kolom ke tabel absensi
        if (! Schema::hasColumn('absensi', 'created_by')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('catatan');
                $table->string('source', 20)->nullable()->after('created_by');
                $table->foreign('created_by')->references('id')->on('akun')->onDelete('set null');
            });
        }

        // 2. Tambah kolom ke tabel pengajuan
        if (! Schema::hasColumn('pengajuan', 'created_by')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable()->after('status_pengajuan');
                $table->string('source', 20)->nullable()->after('created_by');
                $table->foreign('created_by')->references('id')->on('akun')->onDelete('set null');
            });
        }

        // 3. Seed privilege catat_absensi
        $now = Carbon::now();

        DB::table('privilege')->updateOrInsert(
            ['nama_privilege' => 'catat_absensi'],
            [
                'label_privilege' => 'Catat Absensi Pegawai',
                'kategori' => 'Persetujuan',
                'deskripsi' => 'Mencatat kondisi absensi pegawai (WFO/WFH/WFC/Sakit/Izin/Cuti/dll) atas nama pegawai.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $privilegeId = DB::table('privilege')
            ->where('nama_privilege', 'catat_absensi')
            ->value('privilege_id');

        if ($privilegeId) {
            // Super Admin global (organization_id IS NULL) mendapat semua privilege otomatis
            // via Role::hasPrivilege(), tapi kita seed juga agar eksplisit.
            $superAdminRoleId = DB::table('role')
                ->where('nama_role', 'Super Admin')
                ->whereNull('organization_id')
                ->value('role_id');

            if ($superAdminRoleId) {
                DB::table('role_privilege')->updateOrInsert(
                    ['role_id' => $superAdminRoleId, 'privilege_id' => $privilegeId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }

            // HR / HRD mendapat privilege ini
            $hrRoleId = DB::table('role')
                ->where('nama_role', 'HR / HRD')
                ->whereNull('organization_id')
                ->value('role_id');

            if ($hrRoleId) {
                DB::table('role_privilege')->updateOrInsert(
                    ['role_id' => $hrRoleId, 'privilege_id' => $privilegeId],
                    ['created_at' => $now, 'updated_at' => $now]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus privilege
        $privilegeId = DB::table('privilege')
            ->where('nama_privilege', 'catat_absensi')
            ->value('privilege_id');

        if ($privilegeId) {
            DB::table('role_privilege')->where('privilege_id', $privilegeId)->delete();
            DB::table('privilege')->where('privilege_id', $privilegeId)->delete();
        }

        // Drop kolom dari pengajuan
        if (Schema::hasColumn('pengajuan', 'created_by')) {
            Schema::table('pengajuan', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn(['created_by', 'source']);
            });
        }

        // Drop kolom dari absensi
        if (Schema::hasColumn('absensi', 'created_by')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
                $table->dropColumn(['created_by', 'source']);
            });
        }
    }
};
