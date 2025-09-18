<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Services\SessionService;
use App\Responses\{ClientError, ServerError, Success};
use App\Exceptions\{InvalidRefreshTokenException, InvalidCredentialException};

class SessionController extends Controller
{
    private SessionService $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->middleware('auth:api', ['only' => ['refresh']]);
        $this->sessionService = $sessionService;
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $data = $this->sessionService->login($request->email, $request->password);

            return Success::response($data);
        } catch (InvalidCredentialException $e) {
            return ClientError::invalidCredentials();
        } catch (ValidationException $e) {
            return ClientError::badRequest([
                'message' => 'invalid request payload',
                'errors'  => $e->errors(),
            ]);
        } catch (\Throwable $e) {
            return ServerError::internalError($e->getMessage());
        }
    }

    public function refresh(Request $request)
    {
        try {
            $data = $this->sessionService->refresh($request->bearerToken());

            return Success::response($data);
        } catch (InvalidRefreshTokenException $e) {
            return ClientError::invalidRefreshToken();
        } catch (\Exception $e) {
            return ServerError::internalError($e->getMessage());
        }
    }
}
