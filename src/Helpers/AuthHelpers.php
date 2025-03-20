<?php

namespace App\Helpers;

use SpotifyWebAPI\SpotifyWebAPI;
use App\Auth\SpotifyAuthHandler;

function loadAndVerifyAuthToken(): SpotifyWebAPI {
	$auth = new SpotifyAuthHandler();

	try {
		$api = $auth->getApi();
		$auth->setAccessToken($_SESSION['spotify_access_token']);
		
		// Verify the token still works
		$api->me();
		
		// Token is valid, continue to main application
		return $api;
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

		throw $e;
	}
}

function generateTokensFromAuth(string $code): array {
	$auth = new SpotifyAuthHandler();  // Create new instance
	$api = $auth->handleCallback($code);

	return [
		'access_token' => $auth->getAccessToken(),
		'refresh_token' => $auth->getRefreshToken()
	];
}