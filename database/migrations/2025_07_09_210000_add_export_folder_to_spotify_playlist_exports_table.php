<?php
/**
 * Migration to add export_folder field to spotify_playlist_exports table.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('spotify_playlist_exports', function (Blueprint $table) {
            $table->string('export_folder')->nullable()->after('playlists_exported');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('spotify_playlist_exports', function (Blueprint $table) {
            $table->dropColumn('export_folder');
        });
    }
}; 