<?php

/**
 * Handles exporting of Spotify playlists.
 * 
 * PHP version 8.2
 *
 * @category Export
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export.
 */

namespace App\Export;

use SpotifyWebAPI\SpotifyWebAPI;

/**
 * SpotifyPlaylistExporter class handles the export functionality for playlists.
 */
class SpotifyPlaylistExporter {
	/**
	 * Spotify Web API instance.
	 *
	 * @var SpotifyWebAPI
	 */
	private SpotifyWebAPI $api;

	/**
	 * Array of playlists to export.
	 *
	 * @var array
	 */
	private array $playlists;

	/**
	 * Constructor for the SpotifyPlaylistExporter.
	 *
	 * @param SpotifyWebAPI $api       Spotify Web API instance
	 * @param array        $playlists Array of playlists to export
	 */
	public function __construct(SpotifyWebAPI $api, array $playlists) {
		$this->api = $api;
		$this->playlists = $playlists;
	}
} 