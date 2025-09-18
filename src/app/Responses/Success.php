<?php

namespace App\Responses;

class Success
{
    public static function response($data = null) {
        return response()->json([
            "ok" => true,
            "data" => $data,
        ]);
    }

    public static function ok() {
        return response()->json([
            "ok" => true,
        ]);
    }
}