<?php

/**
 * Application configuration settings.
 * 
 * @category Configuration
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

return [
    'app' => [
        'name' => 'Spotify Playlist Export',
        'environment' => 'development',
        'debug' => true
    ],

    'spotify' => [
    'client_id' => '***REMOVED***',
    'client_secret' => '***REMOVED***',
    'redirect_uri' => 'http://localhost:8000/callback'
    ],
    
    'database' => [
        'host' => 'localhost',
        'name' => 'spotify_export',
        'user' => 'root',
        'password' => ''
    ]
]; 