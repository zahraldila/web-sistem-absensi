<?php

namespace App\Http\Controllers;

use App\Helpers\logHelpers;
use App\Models\Approval;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\OrganizationHelper;

class DashboardControllers extends Controller
{
    public function admin(Request $request)
    {
        $orgId = OrganizationHelper::requireActiveOrganization();
        $today = now()->toDateString();

        $totalPegawai = Pegawai::where('organization_id', $orgId)
            ->whereDoesntHave('akun', function ($query) {
                $query->whereRaw('LOWER(role) = ?', ['admin']);
            })->where('status', 'Aktif')->count();

        $hadirHariIni = Attendance::whereHas('pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereDate('tanggal_absensi', $today)
            ->distinct('pegawai_id')
            ->count('pegawai_id');

        $wfoCount = Attendance::whereHas('pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereDate('tanggal_absensi', $today)
            ->where('skema_kerja', 'WFO')
            ->distinct('pegawai_id')
            ->count('pegawai_id');

        $wfhWfcCount = Attendance::whereHas('pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereDate('tanggal_absensi', $today)
            ->whereIn('skema_kerja', ['WFH', 'WFC'])
            ->count();

        $liveCheckIns = Attendance::whereHas('pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->whereDate('tanggal_absensi', $today)
            ->whereNotNull('jam_checkin')
            ->with('pegawai')
            ->orderByDesc('jam_checkin')
            ->limit(5)
            ->get()
            ->map(function ($attendance) {
                return [
                    'nama'   => $attendance->pegawai?->nama_pegawai ?? 'Unknown',
                    'foto'   => $attendance->pegawai?->foto_profile
                        ? supabase_public_url($attendance->pegawai->foto_profile)
                        : null,
                    'status' => $attendance->skema_kerja ?? 'WFO',
                    'jam'    => $attendance->jam_checkin
                        ? Carbon::parse($attendance->jam_checkin)->format('H:i')
                        : '-',
                ];
            });

        $pendingApprovals = Approval::whereHas('pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->where('status_pengajuan', 'Pending')
            ->with('pegawai')
            ->orderByDesc('tanggal_pengajuan')
            ->limit(4)
            ->get()
            ->map(function ($approval) {
                return [
                    'nama'     => $approval->pegawai?->nama_pegawai ?? 'Unknown',
                    'jenis'    => $approval->jenis_pengajuan ?? '-',
                    'tanggal'  => $approval->tanggal_pengajuan
                        ? Carbon::parse($approval->tanggal_pengajuan)->translatedFormat('d F Y')
                        : '-',
                    'status'   => $approval->status_pengajuan ?? 'Pending',
                ];
            });

        $activities = AuditLog::query()
            ->whereHas('akun.pegawai', function ($q) use ($orgId) {
                $q->where('organization_id', $orgId);
            })
            ->with('akun.pegawai')
            ->orderByDesc('waktu_log')
            ->limit(4)
            ->get()
            ->map(function ($log) {
                $namaPegawai = $log->akun?->pegawai?->nama_pegawai;
                $aktivitas   = $log->aktivitas ?? 'Aktivitas terbaru';

                return [
                    'title' => $namaPegawai ? $namaPegawai . ' - ' . $aktivitas : $aktivitas,
                    'time'  => $log->waktu_log ? Carbon::parse($log->waktu_log)->diffForHumans() : '-',
                    'color' => 'green',
                ];
            });

        if ($activities->isEmpty()) {
            $activities = collect([[
                'title' => 'Belum ada aktivitas',
                'time'  => '-',
                'color' => 'blue',
            ]]);
        }

        $jadwal = DB::table('jadwal_kerja')
            ->where('organization_id', $orgId)
            ->orderByDesc('jadwal_id')
            ->first();
        $jamMasuk = $jadwal ? Carbon::parse($jadwal->jam_masuk)->format('H:i') : '08:00';
        $jamPulang = $jadwal ? Carbon::parse($jadwal->jam_pulang)->format('H:i') : '17:00';

        return view('admin.index', compact(
            'totalPegawai',
            'hadirHariIni',
            'wfoCount',
            'wfhWfcCount',
            'liveCheckIns',
            'pendingApprovals',
            'activities',
            'jamMasuk',
            'jamPulang'
        ));
    }

    /**
     * Return attendance chart data as JSON for the given filter.
     * filter: 'minggu' (default) | 'bulan' | 'tahun'
     */
    public function chartStatistik(Request $request)
    {
        $orgId = OrganizationHelper::requireActiveOrganization();
        
        $filter = $request->query('filter', 'minggu');
        $skemas = ['WFO', 'WFH/WFC', 'Izin', 'Alfa', 'Dinas'];

        $tipeExpr = DB::raw("CASE
            WHEN skema_kerja IN ('WFH','WFC') THEN 'WFH/WFC'
            WHEN status_kehadiran = 'Izin'   THEN 'Izin'
            WHEN status_kehadiran = 'Alfa'   THEN 'Alfa'
            WHEN skema_kerja = 'Dinas'       THEN 'Dinas'
            ELSE skema_kerja
        END AS tipe");

        if ($filter === 'minggu') {
            $start    = Carbon::now()->startOfWeek(Carbon::MONDAY);
            $end      = Carbon::now()->endOfWeek(Carbon::SUNDAY);
            $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

            $labels = [];
            $period = [];
            for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
                $labels[] = $dayNames[$d->dayOfWeek === 0 ? 6 : $d->dayOfWeek - 1];
                $period[] = $d->toDateString();
            }

            $rows = Attendance::select(
                    DB::raw('DATE(tanggal_absensi) as tgl'),
                    $tipeExpr,
                    DB::raw("CASE
                        WHEN (CASE
                            WHEN skema_kerja IN ('WFH','WFC') THEN 'WFH/WFC'
                            WHEN status_kehadiran = 'Izin'   THEN 'Izin'
                            WHEN status_kehadiran = 'Alfa'   THEN 'Alfa'
                            WHEN skema_kerja = 'Dinas'       THEN 'Dinas'
                            ELSE skema_kerja
                        END) = 'WFO' THEN COUNT(DISTINCT pegawai_id)
                        ELSE COUNT(*)
                    END as total")
                )
                ->whereHas('pegawai', function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })
                ->whereBetween('tanggal_absensi', [$start->toDateString(), $end->toDateString()])
                ->groupBy('tgl', 'tipe')
                ->get()
                ->groupBy('tgl');

            $datasets = [];
            foreach ($skemas as $skema) {
                $data = [];
                foreach ($period as $date) {
                    $found = $rows->get($date, collect())->firstWhere('tipe', $skema);
                    $data[] = $found ? (int) $found->total : 0;
                }
                $datasets[] = ['label' => $skema, 'data' => $data];
            }

            return response()->json([
                'labels'   => $labels,
                'datasets' => $datasets,
                'subtitle' => '7 hari terakhir',
            ]);

        } elseif ($filter === 'bulan') {
            $start = Carbon::now()->startOfMonth();
            $end   = Carbon::now()->endOfMonth();

            $rows = Attendance::select(
                    $tipeExpr,
                    DB::raw('EXTRACT(WEEK FROM tanggal_absensi) as minggu_ke'),
                    DB::raw("CASE
                        WHEN (CASE
                            WHEN skema_kerja IN ('WFH','WFC') THEN 'WFH/WFC'
                            WHEN status_kehadiran = 'Izin'   THEN 'Izin'
                            WHEN status_kehadiran = 'Alfa'   THEN 'Alfa'
                            WHEN skema_kerja = 'Dinas'       THEN 'Dinas'
                            ELSE skema_kerja
                        END) = 'WFO' THEN COUNT(DISTINCT CONCAT(pegawai_id, '_', DATE(tanggal_absensi)))
                        ELSE COUNT(*)
                    END as total")
                )
                ->whereHas('pegawai', function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })
                ->whereBetween('tanggal_absensi', [$start->toDateString(), $end->toDateString()])
                ->groupBy('tipe', 'minggu_ke')
                ->orderBy('minggu_ke')
                ->get()
                ->groupBy('minggu_ke');

            $weekNos = $rows->keys()->sort()->values();
            $labels  = $weekNos->map(fn ($w, $i) => 'Minggu ' . ($i + 1))->toArray();

            $datasets = [];
            foreach ($skemas as $skema) {
                $data = [];
                foreach ($weekNos as $wk) {
                    $found = $rows->get($wk, collect())->firstWhere('tipe', $skema);
                    $data[] = $found ? (int) $found->total : 0;
                }
                $datasets[] = ['label' => $skema, 'data' => $data];
            }

            return response()->json([
                'labels'   => $labels,
                'datasets' => $datasets,
                'subtitle' => Carbon::now()->translatedFormat('F Y'),
            ]);

        } else {
            // tahun – grouped by month
            $year       = Carbon::now()->year;
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
                           'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            $rows = Attendance::select(
                    $tipeExpr,
                    DB::raw('EXTRACT(MONTH FROM tanggal_absensi) as bulan_ke'),
                    DB::raw("CASE
                        WHEN (CASE
                            WHEN skema_kerja IN ('WFH','WFC') THEN 'WFH/WFC'
                            WHEN status_kehadiran = 'Izin'   THEN 'Izin'
                            WHEN status_kehadiran = 'Alfa'   THEN 'Alfa'
                            WHEN skema_kerja = 'Dinas'       THEN 'Dinas'
                            ELSE skema_kerja
                        END) = 'WFO' THEN COUNT(DISTINCT CONCAT(pegawai_id, '_', DATE(tanggal_absensi)))
                        ELSE COUNT(*)
                    END as total")
                )
                ->whereHas('pegawai', function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId);
                })
                ->whereYear('tanggal_absensi', $year)
                ->groupBy('tipe', 'bulan_ke')
                ->orderBy('bulan_ke')
                ->get()
                ->groupBy('bulan_ke');

            $datasets = [];
            foreach ($skemas as $skema) {
                $data = [];
                foreach (range(1, 12) as $m) {
                    $found = $rows->get($m, collect())->firstWhere('tipe', $skema);
                    $data[] = $found ? (int) $found->total : 0;
                }
                $datasets[] = ['label' => $skema, 'data' => $data];
            }

            return response()->json([
                'labels'   => $monthNames,
                'datasets' => $datasets,
                'subtitle' => 'Tahun ' . $year,
            ]);
        }
    }

    /**
     * Handle saving Jam Masuk & Jam Pulang from the modal form.
     */
    public function simpanJamKerja(Request $request)
    {
        $orgId = OrganizationHelper::requireActiveOrganization();
        
        $request->validate([
            'jam_masuk'  => ['required', 'date_format:H:i'],
            'jam_pulang' => ['required', 'date_format:H:i', 'after:jam_masuk'],
        ], [
            'jam_masuk.required'       => 'Jam masuk wajib diisi.',
            'jam_masuk.date_format'    => 'Format jam masuk tidak valid (HH:MM).',
            'jam_pulang.required'      => 'Jam pulang wajib diisi.',
            'jam_pulang.date_format'   => 'Format jam pulang tidak valid (HH:MM).',
            'jam_pulang.after'         => 'Jam pulang harus setelah jam masuk.',
        ]);
    
        // Update atau buat jadwal_kerja baru
        $jadwalAktif = DB::table('jadwal_kerja')
            ->where('organization_id', $orgId)
            ->orderByDesc('jadwal_id')
            ->first();
        
        if ($jadwalAktif) {
            DB::table('jadwal_kerja')
                ->where('organization_id', $orgId)
                ->where('jadwal_id', $jadwalAktif->jadwal_id)
                ->update([
                    'jam_masuk'  => $request->jam_masuk,
                    'jam_pulang' => $request->jam_pulang,
                    'tanggal_berlaku' => now()->toDateString(),
                ]);
        } else {
            DB::table('jadwal_kerja')->insert([
                'organization_id' => $orgId,
                'jam_masuk'  => $request->jam_masuk,
                'jam_pulang' => $request->jam_pulang,
                'tanggal_berlaku' => now()->toDateString(),
            ]);
        }

        // Catat aktivitas admin
        $user = \Illuminate\Support\Facades\Auth::user();
    
        if ($user && $user->akun_id) {
            logHelpers::record(
                $user->akun_id,
                "Mengubah jam kerja: {$request->jam_masuk} - {$request->jam_pulang}"
            );
        }
    
        return redirect()
            ->route('admin.dashboard')
            ->with(
                'success',
                'Jam kerja berhasil diperbarui: Masuk ' .
                $request->jam_masuk .
                ' – Pulang ' .
                $request->jam_pulang
            );
    }
}
