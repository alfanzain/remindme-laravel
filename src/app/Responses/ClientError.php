<?php

namespace App\Responses;

use App\Enums\ErrorType;

class ClientError
{
    public static function badRequest($message = "bad request")
    {
        return self::response(
            errorType: ErrorType::ERR_BAD_REQUEST->name, 
            message: $message,
            errorCode: 400,
        );
    }

    public static function invalidCredentials()
    {
        return self::response(
            errorType: ErrorType::ERR_INVALID_CREDS->name, 
            message: "incorrect username or password",
            errorCode: 401,
        );
    }

    public static function invalidToken()
    {
        return self::response(
            errorType: ErrorType::ERR_INVALID_TOKEN->name, 
            message: "invalid token",
            errorCode: 401,
        );
    }
    
    public static function invalidAccessToken()
    {
        return self::response(
            errorType: ErrorType::ERR_INVALID_ACCESS_TOKEN->name, 
            message: "invalid access token",
            errorCode: 401,
        );
    }

    public static function invalidRefreshToken()
    {
        return self::response(
            errorType: ErrorType::ERR_INVALID_REFRESH_TOKEN->name, 
            message: "invalid refresh token",
            errorCode: 401,
        );
    }

    public static function forbidden()
    {
        return self::response(
            errorType: ErrorType::ERR_FORBIDDEN_ACCESS->name, 
            message: "user doesn't have enough authorization",
            errorCode: 403,
        );
    }

    public static function notFound()
    {
        return self::response(
            errorType: ErrorType::ERR_NOT_FOUND->name, 
            message: "resource is not found",
            errorCode: 404,
        );
    }

    public static function response(
        $errorType = "", 
        $message = "Client Error", 
        $errorCode = 400
    ) {
        return response()->json([
            "ok"      => false,
            "err" => $errorType,
            "msg" => $message,
        ], $errorCode);
    }
}