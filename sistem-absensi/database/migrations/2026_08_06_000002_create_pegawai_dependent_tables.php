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
        // pengajuan
        if (!Schema::hasTable('pengajuan')) {
            Schema::create('pengajuan', function (Blueprint $table) {
                $table->id('pengajuan_id');
                $table->foreignId('pegawai_id')->constrained('pegawai', 'pegawai_id')->onDelete('cascade');
                $table->string('jenis_pengajuan');
                $table->string('lampiran')->nullable();
                $table->date('tanggal_pengajuan');
                $table->text('keterangan')->nullable();
                $table->string('status_pengajuan');
                $table->timestamps();
            });
        }
        
        // approval
        if (!Schema::hasTable('approval')) {
            Schema::create('approval', function (Blueprint $table) {
                $table->id('approval_id');
                $table->foreignId('pengajuan_id')->constrained('pengajuan', 'pengajuan_id')->onDelete('cascade');
                $table->foreignId('akun_id')->constrained('akun', 'id')->onDelete('cascade');
                $table->string('status_approval');
                $table->text('catatan_admin')->nullable();
                $table->dateTime('tanggal_approval');
                $table->timestamps();
            });
        }

        // absensi
        if (!Schema::hasTable('absensi')) {
            Schema::create('absensi', function (Blueprint $table) {
                $table->id('absensi_id');
                $table->foreignId('pegawai_id')->constrained('pegawai', 'pegawai_id')->onDelete('cascade');
                $table->date('tanggal_absensi');
                $table->dateTime('jam_checkin')->nullable();
                $table->dateTime('jam_checkout')->nullable();
                $table->string('skema_kerja');
                $table->string('status_kehadiran');
                $table->decimal('latitude', 10, 8)->nullable();
                $table->decimal('longitude', 11, 8)->nullable();
                $table->decimal('latitude_checkout', 10, 8)->nullable();
                $table->decimal('longitude_checkout', 11, 8)->nullable();
                $table->string('foto_selfie')->nullable();
                $table->text('catatan')->nullable();
                $table->foreignId('jadwal_id')->nullable()->constrained('jadwal_kerja', 'jadwal_id')->onDelete('set null');
                $table->timestamps();
            });
        }

        // nfc
        if (!Schema::hasTable('nfc')) {
            Schema::create('nfc', function (Blueprint $table) {
                $table->id('nfc_id');
                $table->foreignId('pegawai_id')->constrained('pegawai', 'pegawai_id')->onDelete('cascade');
                $table->string('nfc_serial_number')->unique();
                $table->timestamps();
            });
        }

        // notifikasi
        if (!Schema::hasTable('notifikasi')) {
            Schema::create('notifikasi', function (Blueprint $table) {
                $table->id('notifikasi_id');
                $table->foreignId('pegawai_id')->constrained('pegawai', 'pegawai_id')->onDelete('cascade');
                $table->string('judul');
                $table->text('isi_pesan');
                $table->dateTime('tanggal_kirim');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('nfc');
        Schema::dropIfExists('absensi');
        Schema::dropIfExists('approval');
        Schema::dropIfExists('pengajuan');
    }
};
