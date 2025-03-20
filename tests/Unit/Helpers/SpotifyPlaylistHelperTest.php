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

	/**
	 * Test retrieving tracks from a single-page playlist.
	 */
	public function testGetPlaylistTracksWithSinglePage(): void {
		$playlistId = 'test_playlist_id';
		$mockTracks = (object) [
			'items' => [
				(object) ['track' => (object) ['name' => 'Track 1']],
				(object) ['track' => (object) ['name' => 'Track 2']],
			],
			'total' => 2
		];

		$this->mockApi->expects($this->once())
			->method('getPlaylistTracks')
			->with(
				$this->equalTo($playlistId),
				$this->equalTo(['limit' => 50, 'offset' => 0])
			)
			->willReturn($mockTracks);

		$result = $this->helper->getPlaylistTracks($playlistId);

		$this->assertCount(2, $result);
		$this->assertEquals('Track 1', $result[0]->track->name);
		$this->assertEquals('Track 2', $result[1]->track->name);
	}

	/**
	 * Test retrieving tracks from a multi-page playlist.
	 */
	public function testGetPlaylistTracksWithMultiplePages(): void {
		$playlistId = 'test_playlist_id';
		$page1 = (object) [
			'items' => [
				(object) ['track' => (object) ['name' => 'Track 1']],
				(object) ['track' => (object) ['name' => 'Track 2']],
			],
			'total' => 3
		];

		$page2 = (object) [
			'items' => [
				(object) ['track' => (object) ['name' => 'Track 3']],
			],
			'total' => 3
		];

		$expectedCalls = [
			['offset' => 0, 'response' => $page1],
			['offset' => 50, 'response' => $page2]
		];
		$callCount = 0;

		$this->mockApi->expects($this->exactly(2))
			->method('getPlaylistTracks')
			->willReturnCallback(function($id, $options) use ($playlistId, $expectedCalls, &$callCount) {
				$this->assertEquals($playlistId, $id);
				$this->assertEquals($expectedCalls[$callCount]['offset'], $options['offset']);
				$this->assertEquals(50, $options['limit']);
				
				return $expectedCalls[$callCount++]['response'];
			});

		$result = $this->helper->getPlaylistTracks($playlistId);

		$this->assertCount(3, $result);
		$this->assertEquals('Track 1', $result[0]->track->name);
		$this->assertEquals('Track 2', $result[1]->track->name);
		$this->assertEquals('Track 3', $result[2]->track->name);
	}

	/**
	 * Test retrieving tracks from an empty playlist.
	 */
	public function testGetPlaylistTracksWithEmptyPlaylist(): void {
		$playlistId = 'test_playlist_id';
		$mockTracks = (object) [
			'items' => [],
			'total' => 0
		];

		$this->mockApi->expects($this->once())
			->method('getPlaylistTracks')
			->with(
				$this->equalTo($playlistId),
				$this->equalTo(['limit' => 50, 'offset' => 0])
			)
			->willReturn($mockTracks);

		$result = $this->helper->getPlaylistTracks($playlistId);

		$this->assertEmpty($result);
		$this->assertIsArray($result);
	}
} 