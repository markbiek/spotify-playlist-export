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
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

use function App\Helpers\loadAndVerifyAuthToken;
use function App\Helpers\generateTokensFromAuth;

// Load configuration
$config = include_once __DIR__ . '/config.php';

// Initialize Twig
$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader, [
	'cache' => __DIR__ . '/cache/twig',
	'debug' => $config['app']['debug']
]);

error_reporting(0);
ini_set('display_errors', '0');

if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

	// Add the debug extension
	$twig->addExtension(new \Twig\Extension\DebugExtension());
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

$userData = $api->me();
$playlistsData = $api->getUserPlaylists($userData->id);

// Create array of playlists and sort by name
$playlists = array_values((array) $playlistsData->items);
usort($playlists, function($a, $b) {
	return strcasecmp($a->name, $b->name);
});

echo $twig->render('dashboard.twig', [
	'user' => $userData,
	'playlists' => $playlists
]);
