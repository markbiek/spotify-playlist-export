<?php

namespace App\Http\Controllers;

use SpotifyWebAPI\Session;
use Illuminate\Http\Request;

class SpotifyController extends Controller
{
	/**
	 * Initiates the Spotify OAuth flow.
	 */
	public function connect()
	{
		$session = new Session(
			config('services.spotify.client_id'),
			config('services.spotify.client_secret'),
			config('services.spotify.redirect_uri')
		);

		$options = [
			'scope' => [
				'playlist-read-private',
				'playlist-read-collaborative',
				'user-read-private'
			],
		];

		return redirect($session->getAuthorizeUrl($options));
	}

	/**
	 * Handles the callback from Spotify OAuth and stores tokens.
	 */
	public function callback(Request $request)
	{
		if ($request->has('error')) {
			return redirect()->route('home')->with('error', 'Spotify authorization failed: ' . $request->get('error'));
		}

		try {
			$session = new Session(
				config('services.spotify.client_id'),
				config('services.spotify.client_secret'),
				config('services.spotify.redirect_uri')
			);

			// Request access token using the code from Spotify
			$session->requestAccessToken($request->get('code'));

			// Store tokens in session
			session([
				'spotify_access_token' => $session->getAccessToken(),
				'spotify_refresh_token' => $session->getRefreshToken(),
				'spotify_token_expires_in' => $session->getTokenExpiration()
			]);

			return redirect()->route('dashboard')->with('success', 'Successfully connected to Spotify!');
		} catch (\Exception $e) {
			return redirect()->route('home')->with('error', 'Failed to connect to Spotify: ' . $e->getMessage());
		}
	}
}
