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
        $tables = [
            'pegawai',
            'master_divisi',
            'master_jabatan',
            'jadwal_kerja',
            'lokasi_kantor'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('organization_id')
                      ->constrained('organizations', 'organization_id')
                      ->onDelete('cascade');
            });
        }

        Schema::table('settings', function (Blueprint $table) {
            // Drop existing unique constraint on 'key'
            $table->dropUnique(['key']);
            
            $table->foreignId('organization_id')
                  ->constrained('organizations', 'organization_id')
                  ->onDelete('cascade');
                  
            // Add new unique constraint combining organization_id and key
            $table->unique(['organization_id', 'key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'key']);
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
            $table->unique(['key']);
        });

        $tables = [
            'lokasi_kantor',
            'jadwal_kerja',
            'master_jabatan',
            'master_divisi',
            'pegawai'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropForeign(['organization_id']);
                $table->dropColumn('organization_id');
            });
        }
    }
};
