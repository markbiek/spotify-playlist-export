<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles the export of a batch of playlists from Spotify.
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
 * Job for exporting a batch of playlists from Spotify.
 *
 * @category Job
 * @package  App\Jobs
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class ExportPlaylistBatchJob implements ShouldQueue
{
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
     * The batch of playlists to export.
     *
     * @var array
     */
    protected $playlists;

    /**
     * The storage folder path for this export.
     *
     * @var string
     */
    protected $storage_path;

    /**
     * Create a new job instance.
     *
     * @param SpotifyPlaylistExport $export    The playlist export record.
     * @param SpotifyWebAPI         $api       The initialized Spotify API instance.
     * @param array                 $playlists The batch of playlists to export.
     * @param string                $storage_path The storage path for this export.
     */
    public function __construct(SpotifyPlaylistExport $export, SpotifyWebAPI $api, array $playlists, string $storage_path)
    {
        $this->export = $export;
        $this->api = $api;
        $this->playlists = $playlists;
        $this->storage_path = $storage_path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            foreach ($this->playlists as $playlist) {
                // Fetch all tracks for this playlist.
                $tracks = $this->getPlaylistTracks($this->api, $playlist->id);

                // Save playlist tracks to JSON and CSV files.
                $this->savePlaylistJsonToFile($playlist, $tracks);
                $this->savePlaylistCsvToFile($playlist, $tracks);

                // Update progress.
                $this->export->increment('playlists_exported');
            }

            // Mark this batch as completed.
            $this->export->increment('completed_batches');

            // Check if all batches are complete.
            $this->export->refresh();
            if ($this->export->completed_batches >= $this->export->total_batches) {
                // All batches complete - mark export as finished and zip files.
                $this->export->update(['finished' => true]);
                $this->zipAndCleanupExportFolder();
            }
        } catch (\Exception $e) {
            Log::error('Error exporting playlist batch:', ['error' => $e->getMessage()]);
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
     * Generate a safe filename for a playlist export.
     *
     * @param object $playlist The playlist object.
     * @param string $extension The file extension (e.g., 'json', 'csv').
     *
     * @return string The full file path for the export file.
     */
    protected function getExportFilePath(object $playlist, string $extension): string
    {
        $safe_name = preg_replace(
            '/-+/',
            '-',
            preg_replace('/[^a-zA-Z0-9]/', '-', $playlist->owner->id . '-' . $playlist->name)
        );
        $safe_name = trim($safe_name, '-');
        return $this->storage_path . '/' . $safe_name . '.' . $extension;
    }

    /**
     * Save playlist tracks to a JSON file.
     *
     * @param object $playlist The playlist object.
     * @param array  $tracks   The playlist tracks.
     *
     * @return void
     */
    protected function savePlaylistJsonToFile(object $playlist, array $tracks): void
    {
        $filepath = $this->getExportFilePath($playlist, 'json');
        $data = [
            'playlist_name' => $playlist->name,
            'owner_id' => $playlist->owner->id,
            'tracks' => $tracks,
        ];
        file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Save playlist tracks to a CSV file.
     *
     * @param object $playlist The playlist object.
     * @param array  $tracks   The playlist tracks.
     *
     * @return void
     */
    protected function savePlaylistCsvToFile(object $playlist, array $tracks): void
    {
        $filepath = $this->getExportFilePath($playlist, 'csv');
        $fp = fopen($filepath, 'w');
        // Write CSV header
        fputcsv($fp, ['Artist', 'Song title', 'Album', 'Date added to playlist', 'Spotify URL']);
        foreach ($tracks as $item) {
            $track = $item->track;
            $artist = isset($track->artists[0]->name) ? $track->artists[0]->name : '';
            $title = $track->name ?? '';
            $album = $track->album->name ?? '';
            $date_added = $item->added_at ?? '';
            $spotify_url = isset($track->external_urls->spotify) ? $track->external_urls->spotify : '';
            fputcsv($fp, [$artist, $title, $album, $date_added, $spotify_url]);
        }
        fclose($fp);
    }

    /**
     * Zip the export folder and delete it if successful.
     *
     * @return void
     */
    protected function zipAndCleanupExportFolder(): void
    {
        $zipPath = $this->storage_path . '.zip';
        $folder = $this->storage_path;
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($folder),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folder) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
            $zip->close();
            // Delete the folder recursively if zip was created
            $this->deleteDirectory($folder);
        }
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $dir The directory path.
     *
     * @return void
     */
    protected function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
