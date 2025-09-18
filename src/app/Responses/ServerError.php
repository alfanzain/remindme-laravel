<?php

namespace App\Responses;

use App\Enums\ErrorType;

class ServerError
{
    public static function internalError($message = "internal server error")
    {
        return self::response(
            errorType: ErrorType::ERR_INTERNAL_ERROR->name, 
            message: $message,
        );
    }

    public static function response(
        $errorType = "", 
        $message = "Server Error", 
    ) {
        return response()->json([
            "status"      => 500,
            "err" => $errorType,
            "msg" => $message,
        ], 500);
    }
}