<?php

namespace App\Helpers;

use App\Helpers\IntAndDateFormatter;

class ApiResponse
{
    public static function json($success, $message, $data = null, $statusCode = 200)
    {
        $response = [
            'success' => $success,
            'message' => $message,
        ];

        if (!is_null($data)) {
            $response['data'] = self::formatData($data);
        }

        return response()->json($response, $statusCode);
    }

    protected static function formatData($data)
    {
        return IntAndDateFormatter::format($data);
    }
}
