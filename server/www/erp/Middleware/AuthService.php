<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthService
{
    private $key;

    public function __construct()
    {
        $this->key = getenv('API_SECRET'); // Secret key for JWT
    }

    /**
     * Get the authenticated user ID from the token.
     * It first checks for a Bearer token in the Authorization header,
     * and if not found, it falls back to checking for a session cookie.
     *
     * @return string|null
     */
    public function getAuthenticatedUserId()
    {
        // First, try to authenticate using Bearer token
        $authResult = $this->authenticate();
        if (is_array($authResult)) {
            return $authResult['userId'];
        }

        // If Bearer token authentication fails, try to authenticate with the session cookie
        return $this->authenticateWithCookie();
    }

    /**
     * Decodes JWT token and returns the userId.
     *
     * @return string|null
     */
    private function getTokenFromHeader()
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
        if ($authHeader) {

            list($type, $token) = explode(" ", $authHeader, 2);
            if (strcasecmp($type, "Bearer") == 0) {
                return $token;
            }
        }
        return null; // Token not found
    }

    /**
     * Authenticate using Bearer token from the Authorization header.
     *
     * @return array|string
     */
    public function authenticate()
    {
        $jwt = $this->getTokenFromHeader();
        $jwt = $this->getTokenFromHeader() ?? $this->getTokenFromCookie();


        if ($jwt) {
            try {
                $decoded = JWT::decode($jwt, new Key($this->key, 'HS256'));
                return (array) $decoded->data;
            } catch (Exception $e) {
                return 'Invalid token!'; // Invalid or expired token
            }
        }
        return 'Not authenticated!'; // Token not found
    }
    /**
     * Authenticate Cookie http only
     *
     * @return array|string
     */
    private function getTokenFromCookie()
    {
        return $_COOKIE['token'] ?? null;
    }

    /**
     * Authenticate with a session cookie, if available.
     *
     * @return array|string|null
     */
    private function authenticateWithCookie()
    {
        // Check for session cookie (for cookie-based auth)
        $sessionId = $_COOKIE['session_id'] ?? null;
        if ($sessionId) {
            // Here you would typically check session validity from a session store or database
            // For demonstration purposes, we'll just assume it's valid
            // Replace with actual session lookup logic
            return ['userId' => 'user-from-cookie']; // Replace with actual session lookup logic
        }

        return null; // No session cookie found
    }

    /**
     * Generates a temporary JWT token for a user.
     *
     * @param string $userId
     * @return string
     */
    public function generateTemporaryToken($userId)
    {
        $payload = [
            "iss" => "http://localhost",
            "aud" => "http://localhost",
            "iat" => time(),
            "exp" => time() + (60 * 60), // Token expires in 1 hour
            "data" => [
                "userId" => $userId,
                "isAdminSwitch" => true // Admin switch flag
            ]
        ];

        $temporaryToken = JWT::encode($payload, $this->key, 'HS256');
        return $temporaryToken;
    }

    /**
     * Get the Admin User ID from the JWT token.
     *
     * @return string|null
     */
    public function getAdminUserId()
    {
        $temporaryToken = $this->getTokenFromHeader();
        if ($temporaryToken) {
            try {
                $decoded = JWT::decode($temporaryToken, new Key($this->key, 'HS256'));
                if (isset($decoded->data->userId)) {
                    return $decoded->data->userId;
                }
            } catch (Exception $e) {
                return null; // Invalid or expired token
            }
        }
        return null; // Token not found
    }
}
