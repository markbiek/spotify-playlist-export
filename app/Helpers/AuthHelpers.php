/**
 * Helper class for Spotify authentication.
 */
namespace App\Helpers;

use SpotifyWebAPI\SpotifyWebAPI;
use App\Auth\SpotifyAuthHandler;
use Illuminate\Support\Facades\Session;
use RuntimeException;

class SpotifyAuthHelper
{
	/**
	 * Load and verify the Spotify auth token.
	 */
	public function loadAndVerifyAuthToken(): SpotifyWebAPI
	{
		$auth = new SpotifyAuthHandler();

		try {
			$api = $auth->getApi();
			
			// If we have a refresh token, always try to get a fresh access token
			if (Session::has('spotify_refresh_token')) {
				if ($auth->refreshAccessToken(Session::get('spotify_refresh_token'))) {
					Session::put('spotify_access_token', $auth->getAccessToken());
					$api->setAccessToken(Session::get('spotify_access_token'));
					return $api;
				}
			}

			// If no refresh token or refresh failed, try the existing access token
			if (Session::has('spotify_access_token')) {
				$auth->setAccessToken(Session::get('spotify_access_token'));
				
				// Verify the token works
				$api->me();
				return $api;
			}

			// If we get here, we have no valid tokens
			throw new RuntimeException('No valid authentication tokens available');
		} catch (\Exception $e) {
			// Clear invalid tokens
			Session::forget(['spotify_access_token', 'spotify_refresh_token']);

			throw $e;
		}
	}

	/**
	 * Generate tokens from auth code.
	 */
	public function generateTokensFromAuth(string $code): array
	{
		$auth = new SpotifyAuthHandler();
		$api = $auth->handleCallback($code);

		return [
			'access_token' => $auth->getAccessToken(),
			'refresh_token' => $auth->getRefreshToken()
		];
	}
}