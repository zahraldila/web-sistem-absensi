<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\logHelpers; // Impor helper log
use Carbon\Carbon;
use App\Helpers\OrganizationHelper;

class SubmissionControllers extends Controller
{
    /**
     * Proses membuat pengajuan baru oleh Pegawai
     */
    public function store(Request $request)
    {
        // 1. Ambil data user dan pegawai_id yang sedang login
        $user = Auth::user();
        $orgId = OrganizationHelper::requireActiveOrganization();
        
        $pegawaiId = $request->input('pegawai_id', $user->pegawai_id ?? session('pegawai_id'));
        
        $pegawai = \App\Models\Pegawai::where('pegawai_id', $pegawaiId)
            ->where('organization_id', $orgId)
            ->first();

        if (!$pegawai) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data pegawai tidak ditemukan atau bukan milik organisasi Anda.'
            ], 403);
        }

        // 2. Validasi input dari aplikasi mobile
        $request->validate([
            'jenis_pengajuan' => 'required|string', // Contoh: 'Cuti', 'Sakit', 'Izin'
            'keterangan'      => 'required|string',
            'lampiran'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Opsional, maks 2MB
        ]);

        // 3. Handle upload file lampiran (jika pegawai menyertakan file/foto)
        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            // File akan disimpan di folder storage/app/public/lampiran
            $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
        }

        // 4. Simpan data ke tabel 'pengajuan' dan dapatkan ID-nya
        $pengajuanId = DB::table('pengajuan')->insertGetId([
            'pegawai_id'        => $pegawaiId,
            'jenis_pengajuan'   => $request->jenis_pengajuan,
            'lampiran'          => $lampiranPath,
            'tanggal_pengajuan' => Carbon::today(),
            'keterangan'        => $request->keterangan,
            'status_pengajuan'  => 'Pending', // Status awal pengajuan
        ]);

        // ---------------------------------------------------------
        // INJEKSI LOG ACTIVITY: Mencatat aktivitas pembuatan pengajuan
        // ---------------------------------------------------------
        // Contoh output di database: "Membuat pengajuan Sakit baru"
        logHelpers::record($user->akun_id, "Membuat pengajuan {$request->jenis_pengajuan} baru");
        // ---------------------------------------------------------

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengajuan berhasil dikirim!',
            'data'    => ['pengajuan_id' => $pengajuanId]
        ], 201);
    }
}