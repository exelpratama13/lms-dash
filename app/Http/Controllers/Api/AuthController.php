<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // 1. Otorisasi Role: Hanya izinkan Admin/Mentor
            if (!$user->hasAnyRole(['admin', 'mentor', 'student'])) { 
                Auth::logout();
                return response()->json([
                    'message' => 'Unauthorized role. Access restricted to Admin or Mentor.',
                ], 403); 
            }

            // 2. Token Generation (Sanctum)
            $user->tokens()->where('name', 'api_token')->delete();
            $token = $user->createToken('api_token', ['server:admin'])->plainTextToken;

            // 3. Sukses
            return response()->json([
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(), // Mengambil semua peran yang dimiliki
                ],
                'token' => $token, 
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials.',
        ], 401);
    }
}