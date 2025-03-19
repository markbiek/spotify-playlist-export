<?php

/**
 * Application entry point.
 * 
 * @category Bootstrap
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Auth\SpotifyAuthHandler;

// Load configuration
$config = include_once __DIR__ . '/config.php';

// Set error reporting based on environment
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Initialize Spotify authentication
$auth = new SpotifyAuthHandler();

// If we have a code in the URL, handle the callback
if (isset($_GET['code'])) {
    try {
        $api = $auth->handleCallback($_GET['code']);
        // Successfully authenticated, you can now use $api to make requests
        $me = $api->me();
        echo "Logged in as: " . htmlspecialchars($me->display_name);

		echo '<pre>' . print_r($me, true) . '</pre>';
    } catch (Exception $e) {
        echo "Error during authentication: " . htmlspecialchars($e->getMessage());
    }
} else {
    // Request authorization from user
    $scopes = [
        'user-read-email',
        'playlist-read-private',
        'playlist-read-collaborative'
    ];
    
    header('Location: ' . $auth->getAuthorizationUrl($scopes));
    exit;
}

// Initialize your application here
// TODO: Add application initialization code 