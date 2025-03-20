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

// Start the session
session_start();

// Initialize Spotify authentication
$auth = new SpotifyAuthHandler();

// Check if we already have an access token
if (isset($_SESSION['spotify_access_token'])) {
	try {
		$api = $auth->getApi();
		$auth->setAccessToken($_SESSION['spotify_access_token']);
		
		// Verify the token still works
		$api->me();
		
		// Token is valid, continue to main application
	} catch (Exception $e) {
		// Token might be expired, try to refresh if we have a refresh token
		if (isset($_SESSION['spotify_refresh_token'])) {
			if ($auth->refreshAccessToken($_SESSION['spotify_refresh_token'])) {
				// Store the new access token
				$_SESSION['spotify_access_token'] = $auth->getAccessToken();
				
				// Redirect to refresh the page with new token
				header('Location: ' . $_SERVER['PHP_SELF']);
				exit;
			}
		}
		
		// If we get here, refresh failed or we had no refresh token
		unset($_SESSION['spotify_access_token']);
		unset($_SESSION['spotify_refresh_token']);
		header('Location: ' . $_SERVER['PHP_SELF']);
		exit;
	}
} elseif (isset($_GET['code'])) {
	// Handle the callback from Spotify
	try {
		$api = $auth->handleCallback($_GET['code']);
		$_SESSION['spotify_access_token'] = $auth->getAccessToken();
		$_SESSION['spotify_refresh_token'] = $auth->getRefreshToken();
		
		// Redirect to clean URL
		header('Location: ' . $_SERVER['PHP_SELF']);
		exit;
		
	} catch (Exception $e) {
		echo "Error during authentication: " . htmlspecialchars($e->getMessage());
		exit;
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

echo '<pre>' . print_r( $api->me(), true ) . '</pre>';
