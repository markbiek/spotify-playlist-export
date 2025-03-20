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

	/**
	 * Export all playlists.
	 */
	public function exportAllPlaylists() {
		$timestamp = date('Y-m-d_H-i-s');
		$exportDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'spotify_export_' . $timestamp;

		if (!mkdir($exportDir, 0755, true)) {
			throw new \RuntimeException('Failed to create export directory.');
		}

		foreach ($this->playlists as $playlist) {
			$this->exportPlaylist($playlist, $exportDir);
		}
	} 

	/**
	 * Export a single playlist.
	 *
	 * @param array $playlist The playlist to export
	 * @param string $exportDir The directory to export the playlist to
	 */
	public function exportPlaylist(array $playlist, string $exportDir) {
		$playlistId = $playlist['id'];
		$playlistName = $playlist['name'];
		$playlistCleanName = preg_replace('/-+/', '-', preg_replace('/[^a-zA-Z0-9]/', '-', $playlistName));

		// Get all tracks from the playlist
		$tracks = $this->api->getPlaylistTracks($playlistId);

		// Convert tracks to JSON
		$jsonContent = json_encode($tracks, JSON_PRETTY_PRINT);
		if ($jsonContent === false) {
			throw new \RuntimeException('Failed to encode playlist tracks to JSON.');
		}

		// Write JSON to file
		$filePath = $exportDir . DIRECTORY_SEPARATOR . $playlistCleanName . '.json';
		if (file_put_contents($filePath, $jsonContent) === false) {
			throw new \RuntimeException('Failed to write playlist tracks to file.');
		}
	}
}