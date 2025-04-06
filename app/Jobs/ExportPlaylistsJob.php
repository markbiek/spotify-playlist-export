<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles the export of playlists from Spotify.
 *
 * @category Job
 * @package  App\Jobs
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace App\Jobs;

use App\Models\SpotifyPlaylistExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use SpotifyWebAPI\SpotifyWebAPI;

/**
 * Job for exporting playlists from Spotify.
 *
 * @category Job
 * @package  App\Jobs
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class ExportPlaylistsJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The playlist export record.
     *
     * @var SpotifyPlaylistExport
     */
    protected $export;

    /**
     * The Spotify API instance.
     *
     * @var SpotifyWebAPI
     */
    protected $api;

    /**
     * The storage folder path for this export.
     *
     * @var string
     */
    protected $storage_path;

    /**
     * Create a new job instance.
     *
     * @param SpotifyPlaylistExport $export The playlist export record.
     * @param SpotifyWebAPI         $api    The initialized Spotify API instance.
     */
    public function __construct(SpotifyPlaylistExport $export, SpotifyWebAPI $api) {
        $this->export = $export;
        $this->api = $api;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void {
        try {
            // Create unique storage folder for this export.
            $this->createStorageFolder();

            $offset = 0;
            $limit = 50; // Maximum allowed by Spotify API.
            $playlists = [];

            // Fetch all playlists with pagination.
            do {
                $response = $this->api->getMyPlaylists(
                    [
                    'limit' => $limit,
                    'offset' => $offset,
                    ]
                );

                foreach ($response->items as $playlist) {
                    $playlists[] = $playlist;

                    // Fetch all tracks for this playlist.
                    $tracks = $this->getPlaylistTracks($this->api, $playlist->id);

                    // Save playlist tracks to file.
                    $this->savePlaylistToFile($playlist, $tracks);

                    // Update progress.
                    $this->export->increment('playlists_exported');
                }

                $offset += $limit;
            } while ($offset < $response->total);

            // Mark export as finished.
            $this->export->update(['finished' => true]);
        } catch (\Exception $e) {
            Log::error('Error exporting playlists:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get all tracks for a playlist.
     *
     * @param SpotifyWebAPI $api         The Spotify API instance.
     * @param string        $playlist_id The ID of the playlist.
     *
     * @return array The playlist tracks.
     */
    protected function getPlaylistTracks(SpotifyWebAPI $api, string $playlist_id): array
    {
        $offset = 0;
        $limit = 100; // Maximum allowed by Spotify API.
        $tracks = [];

        do {
            $response = $api->getPlaylistTracks(
                $playlist_id,
                [
                'limit' => $limit,
                'offset' => $offset,
                ]
            );

            $tracks = array_merge($tracks, $response->items);
            $offset += $limit;
        } while ($offset < $response->total);

        return $tracks;
    }

    /**
     * Create a unique storage folder for this export.
     *
     * @return void
     */
    protected function createStorageFolder(): void
    {
        $folder_name = sprintf(
            '%d-%s',
            $this->export->user_id,
            now()->format('YmdHis')
        );
        
        $this->storage_path = storage_path("app/exports/{$folder_name}");
        
        if (! is_dir($this->storage_path)) {
            mkdir($this->storage_path, 0755, true);
        }
    }

    /**
     * Save playlist tracks to a file.
     *
     * @param object $playlist The playlist object.
     * @param array  $tracks   The playlist tracks.
     *
     * @return void
     */
    protected function savePlaylistToFile(object $playlist, array $tracks): void
    {
        // Generate safe filename from user ID and playlist name.
        $safe_name = preg_replace(
            '/-+/',
            '-',
            preg_replace('/[^a-zA-Z0-9]/', '-', $playlist->owner->id . '-' . $playlist->name)
        );
        $safe_name = trim($safe_name, '-');
        $filepath = $this->storage_path . '/' . $safe_name . '.json';

        $data = [
            'playlist_name' => $playlist->name,
            'owner_id' => $playlist->owner->id,
            'tracks' => $tracks,
        ];

        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }
} 