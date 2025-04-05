<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles the export of playlists from Spotify to various formats.
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
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

/**
 * Controller for handling playlist exports from Spotify.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class PlaylistExportController extends Controller {
    /**
     * Start a new playlist export process.
     *
     * @param  Request $request The incoming request.
     * @return \Illuminate\Http\RedirectResponse    The redirect response.
     */
    public function store(Request $request) {
        if (!session()->has('spotify_access_token')) {
            return redirect()->route('dashboard')->with('error', __('Spotify access token not found.'));
        }

        $api = new SpotifyWebAPI();
        $api->setAccessToken(session('spotify_access_token'));

        try {
            // Get first page of playlists to get total count
            $playlists = $api->getMyPlaylists(['limit' => 1]);
            $playlistCount = $playlists->total;

            // Create new export record
            $export = SpotifyPlaylistExport::create(
                [
                'user_id' => $request->user()->id,
                'finished' => false,
                'playlist_count' => $playlistCount,
                'playlists_exported' => 0
                ]
            );

            return redirect()->route('dashboard')->with('status', __('Export started successfully.'));
        } catch (\Exception $e) {
            \Log::error('Error starting playlist export:', ['error' => $e->getMessage()]);
            return redirect()->route('dashboard')->with('error', __('Failed to start export. Please try again.'));
        }
    }
}
