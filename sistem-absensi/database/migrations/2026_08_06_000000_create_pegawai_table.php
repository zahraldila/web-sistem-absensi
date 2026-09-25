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
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id('pegawai_id');
            $table->string('nip')->nullable();
            $table->string('nama_pegawai');
            $table->string('email')->unique();
            $table->string('no_handphone')->nullable();
            $table->string('status')->nullable();
            $table->string('foto_profile')->nullable();
            $table->foreignId('divisi_id')->nullable()->constrained('master_divisi', 'divisi_id')->onDelete('set null');
            $table->foreignId('jabatan_id')->nullable()->constrained('master_jabatan', 'jabatan_id')->onDelete('set null');
            
            // Legacy columns that will be removed by later migrations
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
