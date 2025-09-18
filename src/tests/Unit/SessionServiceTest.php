<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\SessionService;
use App\Models\User;
use App\Exceptions\{InvalidRefreshTokenException, InvalidCredentialException};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Payload;
use Mockery;

class SessionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SessionService $sessionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sessionService = new SessionService();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function login_with_valid_credentials_returns_user_and_tokens()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'name' => 'Test User'
        ]);

        $mockAccessToken = 'mock.access.token';
        $mockRefreshToken = 'mock.refresh.token';

        // Mock the chain for access token generation
        JWTAuth::shouldReceive('customClaims')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with(Mockery::any())
            ->andReturn($mockAccessToken);

        // Mock the chain for refresh token generation  
        JWTAuth::shouldReceive('customClaims')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with(Mockery::any())
            ->andReturn($mockRefreshToken);

        $result = $this->sessionService->login('test@example.com', 'password123');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertArrayHasKey('refresh_token', $result);
        
        $this->assertEquals($user->id, $result['user']['id']);
        $this->assertEquals($user->email, $result['user']['email']);
        $this->assertEquals($user->name, $result['user']['name']);
        $this->assertEquals($mockAccessToken, $result['access_token']);
        $this->assertEquals($mockRefreshToken, $result['refresh_token']);
    }

    public function login_with_invalid_email_throws_exception()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->expectException(InvalidCredentialException::class);
        $this->sessionService->login('wrong@example.com', 'password123');
    }

    public function login_with_invalid_password_throws_exception()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $this->expectException(InvalidCredentialException::class);
        $this->sessionService->login('test@example.com', 'wrongpassword');
    }

    public function login_with_nonexistent_user_throws_exception()
    {
        $this->expectException(InvalidCredentialException::class);
        $this->sessionService->login('nonexistent@example.com', 'password123');
    }

    public function refresh_with_valid_refresh_token_returns_new_access_token()
    {
        $user = User::factory()->create();
        $refreshToken = 'valid.refresh.token';
        $newAccessToken = 'new.access.token';

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('type')
            ->andReturn('refresh');
        $mockPayload->shouldReceive('get')
            ->with('sub')
            ->andReturn($user->id);

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($refreshToken)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        JWTAuth::shouldReceive('customClaims')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with(Mockery::any())
            ->andReturn($newAccessToken);

        $result = $this->sessionService->refresh($refreshToken);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('access_token', $result);
        $this->assertEquals($newAccessToken, $result['access_token']);
    }

    public function refresh_with_access_token_throws_exception()
    {
        $accessToken = 'access.token';

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('type')
            ->andReturn('access');

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($accessToken)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        $this->expectException(InvalidRefreshTokenException::class);
        $this->sessionService->refresh($accessToken);
    }

    public function refresh_with_nonexistent_user_throws_exception()
    {
        $refreshToken = 'valid.refresh.token';
        $nonExistentUserId = 99999;

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('type')
            ->andReturn('refresh');
        $mockPayload->shouldReceive('get')
            ->with('sub')
            ->andReturn($nonExistentUserId);

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($refreshToken)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        $this->expectException(InvalidRefreshTokenException::class);
        $this->sessionService->refresh($refreshToken);
    }

    public function get_user_from_token_with_valid_token_returns_user()
    {
        $user = User::factory()->create();
        $token = 'valid.token';

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('sub')
            ->andReturn($user->id);

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($token)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        $result = $this->sessionService->getUserFromToken($token);

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user->id, $result->id);
    }

    public function get_user_from_token_with_invalid_token_returns_null()
    {
        $invalidToken = 'invalid.token';

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($invalidToken)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andThrow(new \Exception('Invalid token'));

        $result = $this->sessionService->getUserFromToken($invalidToken);

        $this->assertNull($result);
    }

    public function get_user_from_token_with_nonexistent_user_returns_null()
    {
        $token = 'valid.token';
        $nonExistentUserId = 99999;

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('sub')
            ->andReturn($nonExistentUserId);

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($token)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        $result = $this->sessionService->getUserFromToken($token);

        $this->assertNull($result);
    }

    public function login_generates_tokens_with_correct_payload_structure()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $capturedPayloads = [];

        JWTAuth::shouldReceive('customClaims')
            ->twice()
            ->with(Mockery::on(function ($payload) use (&$capturedPayloads) {
                $capturedPayloads[] = $payload;
                return true;
            }))
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('fromUser')
            ->twice()
            ->with(Mockery::any())
            ->andReturn('mock.token');

        $this->sessionService->login('test@example.com', 'password123');

        $this->assertCount(2, $capturedPayloads);
        
        // Check access token payload
        $accessPayload = $capturedPayloads[0];
        $this->assertEquals($user->id, $accessPayload['sub']);
        $this->assertEquals('access', $accessPayload['type']);
        $this->assertArrayHasKey('exp', $accessPayload);
        
        // Check refresh token payload
        $refreshPayload = $capturedPayloads[1];
        $this->assertEquals($user->id, $refreshPayload['sub']);
        $this->assertEquals('refresh', $refreshPayload['type']);
        $this->assertArrayHasKey('exp', $refreshPayload);
        
        // Refresh token should expire later than access token
        $this->assertGreaterThan($accessPayload['exp'], $refreshPayload['exp']);
    }

    public function refresh_generates_new_access_token_with_correct_payload()
    {
        // Arrange
        $user = User::factory()->create();
        $refreshToken = 'valid.refresh.token';

        $mockPayload = Mockery::mock(Payload::class);
        $mockPayload->shouldReceive('get')
            ->with('type')
            ->andReturn('refresh');
        $mockPayload->shouldReceive('get')
            ->with('sub')
            ->andReturn($user->id);

        JWTAuth::shouldReceive('setToken')
            ->once()
            ->with($refreshToken)
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('getPayload')
            ->once()
            ->andReturn($mockPayload);

        $capturedPayload = null;
        JWTAuth::shouldReceive('customClaims')
            ->once()
            ->with(Mockery::on(function ($payload) use (&$capturedPayload) {
                $capturedPayload = $payload;
                return true;
            }))
            ->andReturnSelf();
        
        JWTAuth::shouldReceive('fromUser')
            ->once()
            ->with(Mockery::any())
            ->andReturn('new.access.token');

        $this->sessionService->refresh($refreshToken);

        $this->assertNotNull($capturedPayload);
        $this->assertEquals($user->id, $capturedPayload['sub']);
        $this->assertEquals('access', $capturedPayload['type']);
        $this->assertArrayHasKey('exp', $capturedPayload);
    }
}