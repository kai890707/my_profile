<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        $password = is_string($data['password']) ? $data['password'] : '';

        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => Hash::make($password),
            'role' => $data['role'] ?? 'user',
            'status' => 'active',
        ]);

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        /** @var int $ttl */
        $ttl = config('jwt.ttl', 60);

        return [
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
        ];
    }

    /**
     * Authenticate user and generate tokens.
     *
     * @param  array<string, string>  $credentials
     * @return array<string, mixed>|null
     */
    public function login(array $credentials): ?array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            return null;
        }

        // Check if user is not deleted
        if ($user->trashed()) {
            return null;
        }

        // Check if user status is active
        if ($user->status !== 'active') {
            return null;
        }

        $token = JWTAuth::fromUser($user);
        $refreshToken = $this->createRefreshToken($user);

        /** @var int $ttl */
        $ttl = config('jwt.ttl', 60);

        return [
            'user' => $user,
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
        ];
    }

    /**
     * Refresh the authentication token.
     */
    public function refresh(): string
    {
        return JWTAuth::refresh();
    }

    /**
     * Refresh the authentication token using a refresh token.
     *
     * @return array<string, mixed>
     */
    public function refreshWithToken(string $refreshToken): array
    {
        // Verify and decode the refresh token
        $payload = JWTAuth::setToken($refreshToken)->getPayload();

        // Verify it's a refresh token
        if ($payload->get('type') !== 'refresh') {
            throw new \Exception('Invalid refresh token');
        }

        // Get the user
        $userId = $payload->get('sub');
        $user = User::find($userId);

        if (! $user) {
            throw new \Exception('User not found');
        }

        // Generate new tokens
        $newAccessToken = JWTAuth::fromUser($user);
        $newRefreshToken = $this->createRefreshToken($user);

        /** @var int $ttl */
        $ttl = config('jwt.ttl', 60);

        return [
            'access_token' => $newAccessToken,
            'refresh_token' => $newRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $ttl * 60,
        ];
    }

    /**
     * Log out the user (invalidate the token).
     */
    public function logout(): void
    {
        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Exception $e) {
            // Token already invalid or not found
        }
    }

    /**
     * Get the authenticated user.
     */
    public function user(): ?User
    {
        $user = JWTAuth::user();

        return $user instanceof User ? $user : null;
    }

    /**
     * Create a refresh token for the user.
     */
    private function createRefreshToken(User $user): string
    {
        // Use JWT with longer TTL for refresh token
        return JWTAuth::customClaims([
            'type' => 'refresh',
            'exp' => now()->addDays(7)->timestamp,
        ])->fromUser($user);
    }
}
