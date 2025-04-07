<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles the deletion of playlist exports.
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

/**
 * Controller for deleting playlist exports.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class DeleteExportController extends Controller
{
    /**
     * Delete a playlist export.
     *
     * @param Request               $request The incoming request.
     * @param SpotifyPlaylistExport $export  The export to delete.
     *
     * @return \Illuminate\Http\RedirectResponse The redirect response.
     */
    public function __invoke(Request $request, SpotifyPlaylistExport $export)
    {
        // Verify the export belongs to the current user.
        if ($export->user_id !== $request->user()->id) {
            return redirect()
                ->route('dashboard')
                ->with('error', __('You do not have permission to delete this export.'));
        }

        // Delete the export.
        $export->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', __('Export deleted successfully.'));
    }
} 