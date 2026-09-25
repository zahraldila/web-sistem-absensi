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
        $connection = Schema::getConnection();

        if ($connection->getDriverName() !== 'pgsql') {
            return;
        }

        $this->resyncSequence($connection, 'pegawai', 'pegawai_id');
        $this->resyncSequence($connection, 'akun', 'id');
    }

    protected function resyncSequence($connection, string $table, string $column): void
    {
        $sequence = $connection->selectOne(
            "SELECT pg_get_serial_sequence(?, ?) AS seq",
            [$table, $column]
        );

        if (! $sequence || ! $sequence->seq) {
            return;
        }

        $maxRow = $connection->selectOne(
            "SELECT MAX(\"{$column}\") AS max_id FROM \"{$table}\""
        );

        $maxId = $maxRow?->max_id;

        if ($maxId === null) {
            // Table is empty: make the next generated ID = 1.
            $connection->statement(
                "SELECT setval(?, 1, false)",
                [$sequence->seq]
            );

            return;
        }

        $connection->statement(
            "SELECT setval(?, ?, true)",
            [$sequence->seq, $maxId]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // no-op
    }
};
