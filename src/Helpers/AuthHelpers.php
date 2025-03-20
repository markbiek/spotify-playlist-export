<?php

namespace App\Helpers;

use SpotifyWebAPI\SpotifyWebAPI;
use App\Auth\SpotifyAuthHandler;

function loadAndVerifyAuthToken(): SpotifyWebAPI {
	$auth = new SpotifyAuthHandler();

	try {
		$api = $auth->getApi();
		
		// If we have a refresh token, always try to get a fresh access token
		if (isset($_SESSION['spotify_refresh_token'])) {
			if ($auth->refreshAccessToken($_SESSION['spotify_refresh_token'])) {
				$_SESSION['spotify_access_token'] = $auth->getAccessToken();
				$api->setAccessToken($_SESSION['spotify_access_token']);
				return $api;
			}
		}

		// If no refresh token or refresh failed, try the existing access token
		if (isset($_SESSION['spotify_access_token'])) {
			$auth->setAccessToken($_SESSION['spotify_access_token']);
			
			// Verify the token works
			$api->me();
			return $api;
		}

		// If we get here, we have no valid tokens
		throw new Exception('No valid authentication tokens available');
	} catch (Exception $e) {
		// Clear invalid tokens
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