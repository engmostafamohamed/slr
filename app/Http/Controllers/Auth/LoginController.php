<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)
                ->orWhere('phone', $request->email)
                ->first();

        if (!$user) {
            return response()->json(['message' => __('auth.invalid_credentials')], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => __('auth.account_not_verified')], 403);
        }

        if ($user->is_suspended) {
            return response()->json(['message' => __('auth.account_suspended')], 403);
        }

        $request->authenticate();

        $data = [
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => [
                'name' => $user->user_name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ];

        return response()->json($data, 200);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => __('auth.logout_success')], 200);
    }
}
