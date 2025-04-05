<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Support\Facades\Log;
use App\Models\SpotifyPlaylistExport;

class DashboardController extends Controller {
    /**
     * Display the dashboard page.
     */
    public function index() {
        $hasSpotifyToken = session()->has('spotify_access_token');
        $userData = null;
        $unfinishedExports = [];

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

                // Get unfinished exports for the current user
                $unfinishedExports = SpotifyPlaylistExport::where('user_id', auth()->id())
                 ->where('finished', false)
                 ->orderBy('created_at', 'desc')
                 ->get();
            } catch (\Exception $e) {
                Log::error('Error fetching Spotify user data:', ['error' => $e->getMessage()]);
            }
        }

        return view(
            'dashboard', [
            'hasSpotifyToken' => $hasSpotifyToken,
            'userData' => $userData,
            'unfinishedExports' => $unfinishedExports
            ]
        );
    }
}
