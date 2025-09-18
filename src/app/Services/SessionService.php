<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\{InvalidRefreshTokenException, InvalidCredentialException};
use Tymon\JWTAuth\Facades\JWTAuth;

class SessionService
{
    private const ACCESS_TOKEN_EXPIRATION = 20; // seconds
    private const REFRESH_TOKEN_EXPIRATION = 60; // seconds (1 minute)
    // private const ACCESS_TOKEN_EXPIRATION = 1;
    // private const REFRESH_TOKEN_EXPIRATION = 5;

    public function login(string $email, string $password): ?array
    {
        $user = $this->check($email, $password);
        
        if (!$user) {
            throw new InvalidCredentialException();
        }

        $accessToken = $this->generateAccessToken($user);
        $refreshToken = $this->generateRefreshToken($user);

        return [
            "user" => [
                "id"    => $user->id,
                "email" => $user->email,
                "name"  => $user->name,
            ],
            "access_token"  => $accessToken,
            "refresh_token" => $refreshToken,
        ];
    }

    public function refresh(string $token): array
    {
        $payload = JWTAuth::setToken($token)->getPayload();

        if ($payload->get('type') !== 'refresh') {
            throw new InvalidRefreshTokenException();
        }
        
        $user = User::find($payload->get('sub'));

        if (!$user) {
            throw new InvalidRefreshTokenException();
        }

        $accessToken = $this->generateAccessToken($user);

        return [
            'access_token' => $accessToken,
        ];
    }

    public function getUserFromToken(string $token): ?User
    {
        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            return User::find($payload->get('sub'));
        } catch (\Exception $e) {
            return null;
        }
    }

    private function check(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    private function generateAccessToken(User $user): string
    {
        $payload = [
            'sub' => $user->id,
            'type' => 'access',
            'exp' => now()->addSeconds(self::ACCESS_TOKEN_EXPIRATION)->timestamp,
        ];

        return JWTAuth::customClaims($payload)->fromUser($user);
    }

    private function generateRefreshToken(User $user): string
    {
        $payload = [
            'sub' => $user->id,
            'type' => 'refresh',
            'exp' => now()->addSeconds(self::REFRESH_TOKEN_EXPIRATION)->timestamp,
        ];

        return JWTAuth::customClaims($payload)->fromUser($user);
    }
}