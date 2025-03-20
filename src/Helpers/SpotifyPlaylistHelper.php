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
} 