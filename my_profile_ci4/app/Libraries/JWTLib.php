<?php

namespace App\Libraries;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Exception;

class JWTLib
{
    private string $secretKey;
    private int $accessExpiry;
    private int $refreshExpiry;

    public function __construct()
    {
        $this->secretKey = getenv('JWT_SECRET_KEY') ?: 'default-secret-key-change-in-production';
        $this->accessExpiry = (int) (getenv('JWT_ACCESS_EXPIRY') ?: 3600); // 預設 1 小時
        $this->refreshExpiry = (int) (getenv('JWT_REFRESH_EXPIRY') ?: 604800); // 預設 7 天
    }

    /**
     * 生成 Access Token
     *
     * @param int $userId 使用者 ID
     * @param string $email 使用者 Email
     * @param string $role 使用者角色 (admin, salesperson, user)
     * @return string JWT Token
     */
    public function generateAccessToken(int $userId, string $email, string $role): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->accessExpiry;

        $payload = [
            'iss' => 'my_profile_api',          // 發行者
            'iat' => $issuedAt,                 // 發行時間
            'exp' => $expiresAt,                // 過期時間
            'sub' => $userId,                   // 主體 (使用者 ID)
            'email' => $email,                  // 使用者 Email
            'role' => $role,                    // 使用者角色
            'type' => 'access',                 // Token 類型
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * 生成 Refresh Token
     *
     * @param int $userId 使用者 ID
     * @return string JWT Token
     */
    public function generateRefreshToken(int $userId): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->refreshExpiry;

        $payload = [
            'iss' => 'my_profile_api',
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'sub' => $userId,
            'type' => 'refresh',                // Token 類型
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    /**
     * 驗證 Token 並回傳 Payload
     *
     * @param string $token JWT Token
     * @return object|null Token Payload，驗證失敗則回傳 null
     */
    public function verifyToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return $decoded;
        } catch (ExpiredException $e) {
            // Token 已過期
            log_message('warning', 'JWT Token expired: ' . $e->getMessage());
            return null;
        } catch (Exception $e) {
            // Token 無效
            log_message('error', 'JWT Token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 檢查 Token 是否過期
     *
     * @param string $token JWT Token
     * @return bool true 表示已過期，false 表示尚未過期
     */
    public function isTokenExpired(string $token): bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            return false; // 驗證成功，未過期
        } catch (ExpiredException $e) {
            return true; // Token 已過期
        } catch (Exception $e) {
            return true; // Token 無效，視為過期
        }
    }

    /**
     * 從 Token 中提取 Payload (不驗證簽章，僅用於檢查)
     *
     * @param string $token JWT Token
     * @return array|null Payload 陣列
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                return null;
            }

            $payload = json_decode(base64_decode($parts[1]), true);
            return $payload;
        } catch (Exception $e) {
            log_message('error', 'JWT Token decode failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * 驗證 Token 類型
     *
     * @param object $payload Token Payload
     * @param string $expectedType 期望的 Token 類型 (access, refresh)
     * @return bool
     */
    public function validateTokenType(object $payload, string $expectedType): bool
    {
        return isset($payload->type) && $payload->type === $expectedType;
    }
}
