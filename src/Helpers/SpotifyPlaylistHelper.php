<?php

namespace App\Helpers;

use SpotifyWebAPI\SpotifyWebAPI;

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
	 * @return array The sorted playlists.
	 */
	public function getSortedPlaylists(): array {
		$playlists = $this->api->getUserPlaylists($this->userData->id);
		$playlists = array_values((array) $playlists->items);
		usort($playlists, function($a, $b) {
			return strcasecmp($a->name, $b->name);
		});	

		return $playlists;
	}

	/**
	 * Get all tracks from a playlist.
	 *
	 * @param string $playlistId The Spotify playlist ID.
	 * @return array The playlist tracks.
	 */
	public function getPlaylistTracks(string $playlistId): array {
		$tracks = [];
		$options = [
			'limit' => 50, // Maximum allowed by the API
			'offset' => 0,
		];

		do {
			$response = $this->api->getPlaylistTracks($playlistId, $options);
			$tracks = array_merge($tracks, $response->items);
			$options['offset'] += $options['limit'];
		} while (count($tracks) < $response->total);

		return $tracks;
	}
} 