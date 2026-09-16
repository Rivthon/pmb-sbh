<?php

namespace App\Helpers\Responses;

use Illuminate\Http\JsonResponse as HttpJsonResponse;

final class JsonResponse
{
    /**
     * Return a success JSON response.
     */
    public static function success(
        ?string $message = null,
        int $statusCode = 200,
        mixed $data = null
    ): HttpJsonResponse {
        return response()->json([
            'code'    => $statusCode,
            'message' => $message ?? 'Success',
            'success' => true,
            'data'    => $data,
        ], $statusCode);
    }

    /**
     * Return an error JSON response.
     */
    public static function error(
        ?string $message = null,
        int $statusCode = 500,
        mixed $errors = null
    ): HttpJsonResponse {
        return response()->json([
            'code'    => $statusCode,
            'message' => $message ?? 'Something went wrong.',
            'success' => false,
            'errors'  => $errors,
        ], $statusCode);
    }

    /**
     * Return a success JSON response with an access token.
     */
    public static function token(
        string $token,
        string $tokenExpired,
        mixed $data = null,
        int $statusCode = 201
    ): HttpJsonResponse {
        return response()->json([
            'code'       => $statusCode,
            'success'    => true,
            'message'    => 'Token generated successfully.',
            'data'       => $data,
            'token_type' => 'Bearer',
            'token'      => [
                'access_token' => $token,
                'expired_at'   => $tokenExpired,
            ],
        ], $statusCode);
    }

    /**
     * Return a JSON response from an object response.
     */
    public static function fromObject(object $objectResponse): HttpJsonResponse
    {
        $array = (array) $objectResponse;

        // fallback code if property missing
        $code = $array['code'] ?? 200;

        return response()->json($array, $code);
    }
}
