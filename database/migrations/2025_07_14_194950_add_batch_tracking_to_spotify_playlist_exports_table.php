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
        Schema::table('spotify_playlist_exports', function (Blueprint $table) {
            $table->integer('total_batches')->default(0)->after('playlists_exported');
            $table->integer('completed_batches')->default(0)->after('total_batches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spotify_playlist_exports', function (Blueprint $table) {
            $table->dropColumn(['total_batches', 'completed_batches']);
        });
    }
};
