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
        // master_divisi
        if (!Schema::hasTable('master_divisi')) {
            Schema::create('master_divisi', function (Blueprint $table) {
                $table->id('divisi_id');
                $table->string('nama_divisi');
                $table->timestamps();
            });
        }

        // master_jabatan
        if (!Schema::hasTable('master_jabatan')) {
            Schema::create('master_jabatan', function (Blueprint $table) {
                $table->id('jabatan_id');
                $table->string('nama_jabatan');
                $table->timestamps();
            });
        }

        // jadwal_kerja
        if (!Schema::hasTable('jadwal_kerja')) {
            Schema::create('jadwal_kerja', function (Blueprint $table) {
                $table->id('jadwal_id');
                $table->date('tanggal_berlaku');
                $table->time('jam_masuk');
                $table->time('jam_pulang');
                $table->timestamps();
            });
        }

        // lokasi_kantor
        if (!Schema::hasTable('lokasi_kantor')) {
            Schema::create('lokasi_kantor', function (Blueprint $table) {
                $table->id('lokasi_id');
                $table->string('nama_kantor', 100);
                $table->decimal('latitude', 10, 8);
                $table->decimal('longitude', 11, 8);
                $table->integer('radius_meter')->default(100);
                $table->timestamps();
            });
        }

        // wifi_kantor
        if (!Schema::hasTable('wifi_kantor')) {
            Schema::create('wifi_kantor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('lokasi_id')->constrained('lokasi_kantor', 'lokasi_id')->onDelete('cascade');
                $table->string('ssid');
                $table->string('bssid');
                $table->boolean('aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wifi_kantor');
        Schema::dropIfExists('lokasi_kantor');
        Schema::dropIfExists('jadwal_kerja');
        Schema::dropIfExists('master_jabatan');
        Schema::dropIfExists('master_divisi');
    }
};
