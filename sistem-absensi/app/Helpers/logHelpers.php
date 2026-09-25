<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class logHelpers {
    // Untuk mencatat aktivitas user ke tabel audit_log
    public static function record($akunId, $aktivitas) {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->akun_id) {
            $akunId = $user->akun_id;
        }

        DB::table('audit_log')->insert([
            'akun_id'   => $akunId,
            'aktivitas' => $aktivitas,
            'waktu_log' => Carbon::now(),
        ]);
    }
}