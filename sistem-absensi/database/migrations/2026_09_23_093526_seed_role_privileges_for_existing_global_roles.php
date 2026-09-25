<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = Carbon::now();

        // Cari role HR / HRD
        $hrRole = DB::table('role')->where('nama_role', 'HR / HRD')->whereNull('organization_id')->first();
        if ($hrRole) {
            $hrPrivileges = [
                'lihat_dashboard', 'lihat_laporan_kehadiran', 'export_laporan_kehadiran', 
                'lihat_manajemen_akun', 'tambah_pegawai', 'edit_pegawai', 'export_pegawai', 
                'lihat_persetujuan', 'approve_pengajuan', 'reject_pengajuan', 
                'lihat_log_aktivitas', 'kelola_jadwal_kerja'
            ];
            $this->seedPrivilegesForRole($hrRole->role_id, $hrPrivileges, $now);
        }

        // Cari role Direktur
        $dirRole = DB::table('role')->where('nama_role', 'Direktur')->whereNull('organization_id')->first();
        if ($dirRole) {
            $dirPrivileges = [
                'lihat_dashboard', 'lihat_laporan_kehadiran', 'export_laporan_kehadiran', 
                'lihat_manajemen_akun', 'export_pegawai', 'lihat_persetujuan', 
                'lihat_log_aktivitas'
            ];
            $this->seedPrivilegesForRole($dirRole->role_id, $dirPrivileges, $now);
        }
    }

    private function seedPrivilegesForRole($roleId, array $privilegeNames, $now)
    {
        $privileges = DB::table('privilege')->whereIn('nama_privilege', $privilegeNames)->get();
        foreach ($privileges as $privilege) {
            $exists = DB::table('role_privilege')
                ->where('role_id', $roleId)
                ->where('privilege_id', $privilege->privilege_id)
                ->exists();
                
            if (!$exists) {
                DB::table('role_privilege')->insert([
                    'role_id' => $roleId,
                    'privilege_id' => $privilege->privilege_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe down: do nothing or remove the mapped privileges
    }
};
