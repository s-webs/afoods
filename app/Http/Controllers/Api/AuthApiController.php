<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MoonshineUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthApiController extends Controller
{
    /**
     * Получить API токен (вход)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'token_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = MoonshineUser::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные'],
            ]);
        }

        // Создаём токен с названием (по умолчанию - тип устройства или "api-token")
        $tokenName = $request->token_name ?? 'api-token';
        $token = $user->createToken($tokenName);

        return response()->json([
            'success' => true,
            'message' => 'Успешная авторизация',
            'data' => [
                'token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 200);
    }

    /**
     * Удалить текущий токен (выход)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Удаляем текущий токен, который использовался для авторизации
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Вы успешно вышли из системы',
        ], 200);
    }

    /**
     * Получить информацию о текущем пользователе
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'moonshine_user_role_id' => $user->moonshine_user_role_id,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Удалить все токены пользователя (выход со всех устройств)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logoutAll(Request $request): JsonResponse
    {
        // Удаляем все токены пользователя
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Вы вышли со всех устройств',
        ], 200);
    }
}
