<?php

/**
 * Spotify authentication handler.
 * 
 * PHP version 8.2
 *
 * @category Authentication
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

namespace App\Auth;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Exception;

/**
 * Handles Spotify authentication flow.
 * 
 * @category Authentication
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */
class SpotifyAuthHandler
{
    private Session $_session;
    private SpotifyWebAPI $_api;
    private array $_config;

    /**
     * Constructor.
     */
    public function __construct()
    {
        global $config;
        $this->_config = $config['spotify'];
        $this->_session = $this->createSession();
        $this->_api = $this->createApi();
    }

    /**
     * Create a new Spotify Session instance.
     *
     * @return Session
     */
    protected function createSession(): Session
    {
        return new Session(
            $this->_config['client_id'],
            $this->_config['client_secret'],
            $this->_config['redirect_uri']
        );
    }

    /**
     * Create a new SpotifyWebAPI instance.
     *
     * @return SpotifyWebAPI
     */
    protected function createApi(): SpotifyWebAPI
    {
        return new SpotifyWebAPI();
    }

    /**
     * Get the authorization URL.
     *
     * @param array $scopes Array of Spotify API scopes.
     * 
     * @return string Authorization URL.
     */
    public function getAuthorizationUrl(array $scopes = []): string
    {
        $options = [
            'scope' => $scopes
        ];

        return $this->_session->getAuthorizeUrl($options);
    }

    /**
     * Handle the callback from Spotify.
     *
     * @param string $code Authorization code from Spotify.
     * 
     * @return SpotifyWebAPI Configured API instance.
     */
    public function handleCallback(string $code): SpotifyWebAPI
    {
        $this->_session->requestAccessToken($code);
        $this->_api->setAccessToken($this->_session->getAccessToken());

        return $this->_api;
    }

    /**
     * Get the API instance.
     *
     * @return SpotifyWebAPI
     */
    public function getApi(): SpotifyWebAPI
    {
        return $this->_api;
    }

    /**
     * Get the current access token from the session.
     *
     * @return string|null Access token if available.
     */
    public function getAccessToken(): ?string
    {
        return $this->_session->getAccessToken();
    }

    /**
     * Set an access token for the API.
     *
     * @param string $token Access token to set.
     * 
     * @return void
     */
    public function setAccessToken(string $token): void
    {
        $this->_api->setAccessToken($token);
    }

    /**
     * Get the current refresh token from the session.
     *
     * @return string|null Refresh token if available.
     */
    public function getRefreshToken(): ?string
    {
        return $this->_session->getRefreshToken();
    }

    /**
     * Refresh the access token using the refresh token.
     *
     * @param string $refreshToken The refresh token to use.
     * 
     * @return bool True if refresh was successful.
     */
    public function refreshAccessToken(string $refreshToken): bool
    {
        try {
            $this->_session->refreshAccessToken($refreshToken);
            $this->_api->setAccessToken($this->_session->getAccessToken());
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
} 