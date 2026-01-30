<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class HealthApiController extends Controller
{
    /**
     * Health check для пингования сервера
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Server is healthy',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
