<?php // Code within app\Helpers\Helper.php

namespace App\Helper;

use Illuminate\Support\Facades\Log;

class Helper
{
    public static function successMessage(string $message, $data): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'status' => true,
                'error' => 'N/A',
                'message' => $message,
                'data' => $data,
            ],
            200
        );
    }

    public static function errorMessage(string $message, $error): \Illuminate\Http\JsonResponse
    {
        return response()->json(
            [
                'status' => false,
                'data' => 'N/A',
                'message' => $message,
                'error' => $error,
            ],
            500
        );
    }

    public static function errorMessagePrint($e)
    {

        $errorMessage = $e->getMessage();
        $errorCode = $e->getCode();
        $slack_error_message = "Something happened! \n\n\n ERROR MESSAGE: $errorMessage\n\n\n CODE: $errorCode\n\n";
        Log::channel('slack')->info($slack_error_message);
    }

    public static function isObjectExist($record): bool
    {
        return ((!is_null($record)) && $record->exists());
    }
}
