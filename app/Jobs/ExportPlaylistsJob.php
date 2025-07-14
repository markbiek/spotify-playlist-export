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

use App\Jobs\ExportPlaylistBatchJob;
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

            // Fetch all playlists.
            $all_playlists = $this->getAllPlaylists();
            
            // Update playlist count.
            $this->export->update(['playlist_count' => count($all_playlists)]);

            // Split playlists into batches of 12.
            $batch_size = 12;
            $batches = array_chunk($all_playlists, $batch_size);
            
            // Update total batches count.
            $this->export->update(['total_batches' => count($batches)]);

            // Dispatch batch jobs.
            foreach ($batches as $batch) {
                ExportPlaylistBatchJob::dispatch($this->export, $this->api, $batch, $this->storage_path);
            }
        } catch (\Exception $e) {
            Log::error('Error setting up playlist export batches:', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Fetch all playlists from Spotify API.
     *
     * @return array All user playlists.
     */
    protected function getAllPlaylists(): array {
        $offset = 0;
        $limit = 50; // Maximum allowed by Spotify API.
        $playlists = [];

        do {
            $response = $this->api->getMyPlaylists(
                [
                'limit' => $limit,
                'offset' => $offset,
                ]
            );

            foreach ($response->items as $playlist) {
                $playlists[] = $playlist;
            }

            $offset += $limit;
        } while ($offset < $response->total);

        return $playlists;
    }


    /**
     * Create a unique storage folder for this export.
     *
     * @return void
     */
    protected function createStorageFolder(): void {
        $folder_name = sprintf(
            '%d-%s',
            $this->export->user_id,
            now()->format('YmdHis')
        );
        
        $this->storage_path = storage_path("app/exports/{$folder_name}");
        
        if (! is_dir($this->storage_path)) {
            mkdir($this->storage_path, 0755, true);
        }
        // Save the folder name to the export model
        $this->export->export_folder = $folder_name;
        $this->export->save();
    }





} 