<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. ORGANIZATIONS: DISPLAY TOKEN
        Schema::table('organizations', function (Blueprint $table) {
            // Kita set nullable dulu agar bisa diisi untuk data existing, setelah itu bisa diubah menjadi NOT NULL
            $table->string('display_token')->nullable()->after('status');
        });

        // Seed display_token for existing organizations
        $organizations = DB::table('organizations')->get();
        foreach ($organizations as $org) {
            DB::table('organizations')
                ->where('organization_id', $org->organization_id)
                ->update(['display_token' => (string) Str::uuid()]);
        }

        // Buat UNIQUE constraint setelah diisi
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('display_token')->nullable(false)->unique()->change();
        });

        // 2. ROLE: ORGANIZATION_ID
        Schema::table('role', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('deskripsi');
            $table->foreign('organization_id')->references('organization_id')->on('organizations')->onDelete('cascade');
        });

        // 3. ABSENSI: LOKASI_ID
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('lokasi_id')->nullable()->after('status_kehadiran');
            $table->foreign('lokasi_id')->references('lokasi_id')->on('lokasi_kantor')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['lokasi_id']);
            $table->dropColumn('lokasi_id');
        });

        Schema::table('role', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('display_token');
        });
    }
};
