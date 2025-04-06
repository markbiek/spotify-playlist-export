<?php
/**
 * This file is part of the Spotify Playlist Export application.
 *
 * Handles the dashboard display.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Support\Facades\Log;
use App\Models\SpotifyPlaylistExport;

/**
 * Controller for the dashboard page.
 *
 * @category Controller
 * @package  App\Http\Controllers
 * @author   Mark Biek <mark.biek@automattic.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/user/spotify-playlist-export
 */
class DashboardController extends Controller {
    /**
     * Display the dashboard page.
     *
     * @return \Illuminate\View\View The dashboard view.
     */
    public function index() {
        $hasSpotifyToken = session()->has('spotify_access_token');
        $userData = null;
        $unfinishedExports = [];
        $completedExports = [];

        if ($hasSpotifyToken) {
            $api = new SpotifyWebAPI();
            $api->setAccessToken(session('spotify_access_token'));

            try {
                $me = $api->me();
                $userData = [
                    'name' => $me->display_name,
                    'profile_url' => $me->external_urls->spotify
                ];
                Log::info('Spotify user data:', ['user' => $me]);

                // Get unfinished exports for the current user.
                $unfinishedExports = SpotifyPlaylistExport::where('user_id', auth()->id())
                    ->where('finished', false)
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Get completed exports for the current user.
                $completedExports = SpotifyPlaylistExport::where('user_id', auth()->id())
                    ->where('finished', true)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching Spotify user data:', ['error' => $e->getMessage()]);
            }
        }

        return view(
            'dashboard',
            [
                'hasSpotifyToken' => $hasSpotifyToken,
                'userData' => $userData,
                'unfinishedExports' => $unfinishedExports,
                'completedExports' => $completedExports
            ]
        );
    }
}
