<?php

/**
 * Tests for SpotifyAuthHandler class.
 * 
 * @category Tests
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace Tests\Auth;

use App\Auth\SpotifyAuthHandler;
use PHPUnit\Framework\TestCase;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

/**
 * Test cases for SpotifyAuthHandler class.
 * 
 * @category Tests
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */
class SpotifyAuthHandlerTest extends TestCase
{
    private SpotifyAuthHandler $_handler;
    private array $_testConfig = [
        'spotify' => [
            'client_id' => 'test_client_id',
            'client_secret' => 'test_client_secret',
            'redirect_uri' => 'http://localhost:8000/test-callback'
        ]
    ];

    /**
     * Set up test environment before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        global $config;
        $config = $this->_testConfig;
        $this->_handler = new SpotifyAuthHandler();
    }

    /**
     * Test that getAuthorizationUrl returns correct URL with scopes.
     *
     * @return void
     */
    public function testGetAuthorizationUrl(): void
    {
        $scopes = ['user-read-email', 'playlist-read-private'];
        $url = $this->_handler->getAuthorizationUrl($scopes);
        
        // Verify URL contains client ID and scopes
        $this->assertStringContainsString('client_id=test_client_id', $url);
        $this->assertStringContainsString(
            'scope=' . urlencode(implode(' ', $scopes)), 
            $url
        );
        $this->assertStringContainsString(
            'redirect_uri=' . urlencode('http://localhost:8000/test-callback'),
            $url
        );
    }

    /**
     * Test that handleCallback returns configured SpotifyWebAPI instance.
     *
     * @return void
     */
    public function testHandleCallback(): void
    {
        global $config;

        // Create mock Session object
        $mockSession = $this->createMock(Session::class);
        $mockSession->expects($this->once())
            ->method('requestAccessToken')
            ->with('test_auth_code');
        
        $mockSession->expects($this->once())
            ->method('getAccessToken')
            ->willReturn('mock_access_token');

        // Create mock API object
        $mockApi = $this->createMock(SpotifyWebAPI::class);
        $mockApi->expects($this->once())
            ->method('setAccessToken')
            ->with('mock_access_token');

        // Create a mock handler that uses our mock objects
        $handler = $this->getMockBuilder(SpotifyAuthHandler::class)
            ->setConstructorArgs([])
            ->onlyMethods(['createSession', 'createApi'])
            ->getMock();

        // Configure the handler to use our mocks
        $handler->expects($this->any())
            ->method('createSession')
            ->willReturn($mockSession);
        
        $handler->expects($this->any())
            ->method('createApi')
            ->willReturn($mockApi);

        // Ensure the config is set before constructing
        $config = $this->_testConfig;
        
        // Call the constructor manually after mocking
        $handler->__construct();

        $result = $handler->handleCallback('test_auth_code');
        $this->assertSame($mockApi, $result);
    }
} 