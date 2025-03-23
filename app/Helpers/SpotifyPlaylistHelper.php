<?php

namespace App\Helpers;

use SpotifyWebAPI\SpotifyWebAPI;
use Illuminate\Support\Collection;

/**
 * Helper class for managing Spotify playlist operations.
 */
class SpotifyPlaylistHelper {
	/**
	 * The Spotify Web API instance.
	 *
	 * @var SpotifyWebAPI
	 */
	protected SpotifyWebAPI $api;

	/**
	 * The user data.
	 *
	 * @var object
	 */
	protected object $userData;

	/**
	 * Constructor.
	 *
	 * @param SpotifyWebAPI $api The Spotify Web API instance.
	 * @param object $userData The user data.
	 */
	public function __construct(SpotifyWebAPI $api, object $userData) {
		$this->api = $api;
		$this->userData = $userData;
	}

	/**
	 * Get the sorted playlists.
	 *
	 * @return Collection The sorted playlists.
	 */
	public function getSortedPlaylists(): Collection {
		$playlists = $this->api->getUserPlaylists($this->userData->id);
		$playlists = collect((array) $playlists->items)->sortBy('name');

		return $playlists;
	}

	/**
	 * Get all tracks from a playlist.
	 *
	 * @param string $playlistId The Spotify playlist ID.
	 * @return Collection The playlist tracks.
	 */
	public function getPlaylistTracks(string $playlistId): Collection {
		$tracks = collect();
		$options = [
			'limit' => 50, // Maximum allowed by the API
			'offset' => 0,
		];

		do {
			$response = $this->api->getPlaylistTracks($playlistId, $options);
			$tracks = $tracks->merge($response->items);
			$options['offset'] += $options['limit'];
		} while ($tracks->count() < $response->total);

		return $tracks;
	}
} 