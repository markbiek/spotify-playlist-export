<?php

/**
 * Application entry point.
 * 
 * PHP version 8.2
 *
 * @category Bootstrap
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

require_once __DIR__ . '/vendor/autoload.php';

session_start();

use App\Auth\SpotifyAuthHandler;

use function App\Helpers\loadAndVerifyAuthToken;
use function App\Helpers\generateTokensFromAuth;

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

// Check if we already have an access token
if (isset($_SESSION['spotify_access_token'])) {
	$api = loadAndVerifyAuthToken();
} elseif (isset($_GET['code'])) {
	// Handle the callback from Spotify
	$tokens = generateTokensFromAuth($_GET['code']);
	$_SESSION['spotify_access_token'] = $tokens['access_token'];
	$_SESSION['spotify_refresh_token'] = $tokens['refresh_token'];

	// Redirect to clean URL
	header('Location: ' . $_SERVER['PHP_SELF']);
	exit;
} else {
	$auth = new SpotifyAuthHandler();

	// Request authorization from user
	$scopes = [
		'user-read-email',
		'playlist-read-private',
		'playlist-read-collaborative'
	];
	
	header('Location: ' . $auth->getAuthorizationUrl($scopes));
	exit;
}

echo '<pre>' . print_r( $api->me(), true ) . '</pre>';
