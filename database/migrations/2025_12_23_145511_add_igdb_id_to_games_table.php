<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add igdb_id column to track which games came from IGDB.
     * This allows for:
     * - Preventing duplicate imports
     * - Syncing/updating games from IGDB
     * - Tracking data source
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->unsignedBigInteger('igdb_id')
                ->nullable()
                ->unique()
                ->after('id')
                ->comment('IGDB game ID for reference and deduplication');

            $table->index('igdb_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropIndex(['igdb_id']);
            $table->dropColumn('igdb_id');
        });
    }
};
