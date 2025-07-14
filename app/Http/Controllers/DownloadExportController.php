<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles downloading of export .zip files.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace App\Http\Controllers;

use App\Models\SpotifyPlaylistExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

/**
 * Controller for downloading export .zip files.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class DownloadExportController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request The incoming request.
     * @param SpotifyPlaylistExport $export The export model.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function __invoke(Request $request, SpotifyPlaylistExport $export)
    {
        // Ensure the export belongs to the current user.
        if ($export->user_id !== $request->user()->id) {
            abort(403, __('You do not have permission to download this export.'));
        }

        // Use the folder_name accessor, which checks export_folder if present.
        $zipPath = storage_path('app/exports/' . $export->folder_name . '.zip');

        if (!file_exists($zipPath)) {
            abort(404, __("Export file not found ({$zipPath})."));
        }

        $downloadName = 'spotify-playlists-' . $export->created_at->format('Ymd_His') . '.zip';
        return response()->download($zipPath, $downloadName);
    }
} 