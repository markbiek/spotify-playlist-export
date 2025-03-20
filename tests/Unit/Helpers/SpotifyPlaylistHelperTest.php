<?php

namespace Tests\Unit\Helpers;

use App\Helpers\SpotifyPlaylistHelper;
use PHPUnit\Framework\TestCase;
use SpotifyWebAPI\SpotifyWebAPI;

/**
 * Unit tests for the SpotifyPlaylistHelper class.
 */
class SpotifyPlaylistHelperTest extends TestCase {
	/**
	 * @var SpotifyWebAPI|\PHPUnit\Framework\MockObject\MockObject
	 */
	private $mockApi;

	/**
	 * @var object
	 */
	private $mockUserData;

	/**
	 * @var SpotifyPlaylistHelper
	 */
	private $helper;

	/**
	 * Set up test environment.
	 */
	protected function setUp(): void {
		$this->mockApi = $this->createMock(SpotifyWebAPI::class);
		$this->mockUserData = (object) [
			'id' => 'test_user_id'
		];
		$this->helper = new SpotifyPlaylistHelper($this->mockApi, $this->mockUserData);
	}

	/**
	 * Test that playlists are correctly sorted by name.
	 */
	public function testGetSortedPlaylistsSortsAlphabetically(): void {
		// Create mock playlist data
		$mockPlaylists = (object) [
			'items' => [
				(object) ['name' => 'Zebra Playlist'],
				(object) ['name' => 'Apple Music'],
				(object) ['name' => 'My Favorites']
			]
		];

		// Configure mock API response
		$this->mockApi->expects($this->once())
			->method('getUserPlaylists')
			->with($this->mockUserData->id)
			->willReturn($mockPlaylists);

		// Get sorted playlists
		$result = $this->helper->getSortedPlaylists();

		// Assert the playlists are sorted alphabetically
		$this->assertEquals('Apple Music', $result[0]->name);
		$this->assertEquals('My Favorites', $result[1]->name);
		$this->assertEquals('Zebra Playlist', $result[2]->name);
	}

	/**
	 * Test handling of empty playlist response.
	 */
	public function testGetSortedPlaylistsWithEmptyResponse(): void {
		// Create mock empty playlist data
		$mockPlaylists = (object) [
			'items' => []
		];

		// Configure mock API response
		$this->mockApi->expects($this->once())
			->method('getUserPlaylists')
			->with($this->mockUserData->id)
			->willReturn($mockPlaylists);

		// Get sorted playlists
		$result = $this->helper->getSortedPlaylists();

		// Assert empty array is returned
		$this->assertEmpty($result);
		$this->assertIsArray($result);
	}
} 