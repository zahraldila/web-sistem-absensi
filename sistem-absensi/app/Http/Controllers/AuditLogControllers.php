<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Pegawai;
use App\Models\Attendance;
use App\Helpers\OrganizationHelper;

class AuditLogControllers extends Controller
{
    /**
     * Menampilkan riwayat aktivitas (Audit Log) - API Endpoint
     */
    public function index(Request $request)
    {
        // Ambil ID dari URL (misal: /api/audit-log?akun_id=1)
        $akun_id = $request->query('akun_id');

        if (!$akun_id) {
            return response()->json(['message' => 'Akun ID tidak disertakan.'], 400);
        }
        
        $user = Auth::user();
        if ($user && $user->akun_id != $akun_id) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke log audit akun ini.'], 403);
        }

        // Langsung tarik data milik akun tersebut
        $logs = \Illuminate\Support\Facades\DB::table('audit_log')
            ->select('log_id', 'aktivitas', 'waktu_log')
            ->where('akun_id', $akun_id)
            ->orderBy('waktu_log', 'desc')
            ->get();

        return response()->json([
            'status'  => 'success',
            'data'    => $logs
        ], 200);
    }

    public function webIndex(Request $request)
    {
        $user = Auth::user();

        $orgId = OrganizationHelper::requireActiveOrganization();

        $query = DB::table('audit_log')
            ->join('akun', 'audit_log.akun_id', '=', 'akun.id')
            ->leftJoin('pegawai', 'akun.pegawai_id', '=', 'pegawai.pegawai_id')
            ->select(
                'audit_log.log_id',
                'audit_log.aktivitas',
                'audit_log.waktu_log',
                'akun.username',
                'akun.role',
                'pegawai.nama_pegawai'
            )
            ->where('pegawai.organization_id', $orgId);
        
        $logs = $query->orderBy('audit_log.waktu_log', 'desc')->paginate(15);

        $totalPegawaiQuery = Pegawai::whereDoesntHave('akun', function ($query) {
            $query->whereRaw('LOWER(role) = ?', ['admin']);
        })->where('status', 'Aktif')
          ->where('organization_id', $orgId);
        
        $totalPegawai = $totalPegawaiQuery->count(); 
        
        $hadirQuery = Attendance::where('tanggal_absensi', today())
            ->whereHas('pegawai', function($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            });
            
        $hadirHariIni = (clone $hadirQuery)->count();
        
        $wfoCount = (clone $hadirQuery)
            ->where(function($query) {
                $query->whereNull('skema_kerja')
                      ->orWhere('skema_kerja', 'WFO');
            })
            ->count();

        $wfhWfcCount = (clone $hadirQuery)
            ->whereIn('skema_kerja', ['WFH', 'WFC'])
            ->count();

        // PASTIKAN NAMA FILE BLADE DI BAWAH INI SESUAI DENGAN YANG ADA DI FOLDERMU
        return view('admin.log-aktivitas', compact(
            'logs',
            'totalPegawai',
            'hadirHariIni',
            'wfoCount',
            'wfhWfcCount'
        ));
    }
}