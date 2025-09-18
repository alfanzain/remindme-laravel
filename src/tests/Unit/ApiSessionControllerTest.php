<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Http\Controllers\Api\SessionController;
use App\Services\SessionService;
use App\Exceptions\InvalidCredentialException;
use App\Exceptions\InvalidRefreshTokenException;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class ApiSessionControllerTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_returns_success_response_on_valid_login()
    {
        $service = Mockery::mock(SessionService::class);
        $service->shouldReceive('login')
            ->once()
            ->with('john@example.com', 'secret')
            ->andReturn(['user' => ['id' => 1, 'email' => 'john@example.com']]);

        $controller = new SessionController($service);

        $request = Request::create('/api/session', 'POST', [
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $response = $controller->login($request);
        $data = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertTrue($data['ok']);
        $this->assertEquals(1, $data['data']['user']['id']);
    }

    public function test_returns_client_error_on_invalid_credentials()
    {
        $service = Mockery::mock(SessionService::class);
        $service->shouldReceive('login')
            ->once()
            ->andThrow(new InvalidCredentialException());

        $controller = new SessionController($service);

        $request = Request::create('/api/session', 'POST', [
            'email' => 'bad@example.com',
            'password' => 'wrongpass',
        ]);

        $response = $controller->login($request);
        $data = $response->getData(true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertFalse($data['ok']);
    }

    public function test_returns_validation_error_on_bad_request()
    {
        $service = Mockery::mock(SessionService::class);

        $controller = new SessionController($service);

        $request = Request::create('/api/session', 'POST', [
            // missing password
            'email' => 'not-an-email',
        ]);

        $response = $controller->login($request);
        $data = $response->getData(true);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertFalse($data['ok']);
        $this->assertEquals('ERR_BAD_REQUEST', $data['err']);
    }

    public function test_returns_new_access_token_on_valid_refresh()
    {
        $service = Mockery::mock(SessionService::class);
        $service->shouldReceive('refresh')
            ->once()
            ->with('mocked-token')
            ->andReturn(['access_token' => 'new-token']);

        $controller = new SessionController($service);

        $request = Request::create('/api/session/refresh', 'PUT');
        $request->headers->set('Authorization', 'Bearer mocked-token');

        $response = $controller->refresh($request);
        $data = $response->getData(true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('new-token', $data['data']['access_token']);
    }

    public function test_returns_client_error_on_invalid_refresh_token()
    {
        $service = Mockery::mock(SessionService::class);
        $service->shouldReceive('refresh')
            ->once()
            ->andThrow(new InvalidRefreshTokenException());

        $controller = new SessionController($service);

        $request = Request::create('/api/session/refresh', 'PUT');
        $request->headers->set('Authorization', 'Bearer bad-token');

        $response = $controller->refresh($request);
        $data = $response->getData(true);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertFalse($data['ok']);
    }
}
