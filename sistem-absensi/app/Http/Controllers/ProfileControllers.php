<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Helpers\logHelpers; // Impor helper log
use Illuminate\Support\Facades\Storage;

class ProfileControllers extends Controller
{
    /**
     * Memperbarui data profil pegawai (No HP, Foto, dll)
     */
    public function updateProfile(Request $request)
    {
        // 1. Ambil data user yang sedang login
        $user = Auth::user();
        $pegawaiId = $user->pegawai_id ?? session('pegawai_id');

        // Pastikan akun ini terhubung dengan data pegawai
        if (!$pegawaiId) {
            return response()->json(['message' => 'Data pegawai tidak ditemukan pada akun ini.'], 404);
        }

        // 2. Validasi input
        $request->validate([
            'no_handphone' => 'nullable|string|max:20',
            'foto_profile' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Maks 2MB
        ]);

        // Siapkan array data yang akan di-update
        $updateData = [];

        if ($request->has('no_handphone')) {
            $updateData['no_handphone'] = $request->no_handphone;
        }

        // 3. Handle upload foto profil jika ada
        if ($request->hasFile('foto_profile')) {
            // Hapus foto lama jika diperlukan (opsional, tergantung logic sistemmu)
            // $oldPegawai = DB::table('pegawai')->where('pegawai_id', $pegawaiId)->first();
            // if ($oldPegawai->foto_profile) { Storage::disk('public')->delete($oldPegawai->foto_profile); }

            $fotoPath = $request->file('foto_profile')->store('profile_photos', 'public');
            $updateData['foto_profile'] = $fotoPath;
        }

        // Jika tidak ada data yang di-update, kembalikan response
        if (empty($updateData)) {
            return response()->json(['message' => 'Tidak ada data yang diubah.'], 200);
        }

        // 4. Update tabel 'pegawai'
        DB::table('pegawai')
            ->where('organization_id', \App\Helpers\OrganizationHelper::requireActiveOrganization())
            ->where('pegawai_id', $pegawaiId)
            ->update($updateData);

        // ---------------------------------------------------------
        // INJEKSI LOG ACTIVITY: Mencatat aktivitas perubahan profil
        // ---------------------------------------------------------
        logHelpers::record($user->akun_id, 'Melakukan pembaruan data profil diri');
        // ---------------------------------------------------------

        return response()->json([
            'status'  => 'success',
            'message' => 'Profil berhasil diperbarui!',
            'data'    => $updateData
        ], 200);
    }
}