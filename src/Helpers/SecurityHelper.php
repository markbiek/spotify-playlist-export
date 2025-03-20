<?php

namespace App\Helpers;

/**
 * Generate a new CSRF token and store it in the session.
 *
 * @return string The generated CSRF token.
 */
function generateCsrfToken(): string {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	
	return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token from the request.
 *
 * @param string $token The token to validate.
 * @return bool True if token is valid.
 */
function validateCsrfToken(?string $token): bool {
	if (empty($_SESSION['csrf_token']) || empty($token)) {
		return false;
	}
	
	return hash_equals($_SESSION['csrf_token'], $token);
} 