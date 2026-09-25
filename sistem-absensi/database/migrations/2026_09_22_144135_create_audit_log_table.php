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
        Schema::create('audit_log', function (Blueprint $table) {
            $table->id('log_id');
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->text('aktivitas');
            $table->timestamp('waktu_log')->useCurrent();
            
            // Set foreign key to id in akun table
            $table->foreign('akun_id')->references('id')->on('akun')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_log');
    }
};
