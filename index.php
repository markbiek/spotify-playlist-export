<?php

/**
 * Application entry point.
 * 
 * @category Bootstrap
 * @package  SpotifyPlaylistExport
 * @author   Application Developer <developer@example.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/user/spotify-playlist-export
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = include_once __DIR__ . '/config.php';

// Set error reporting based on environment
if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Initialize your application here
// TODO: Add application initialization code 