<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
        } catch (\Exception $e) {
            return redirect(env('FRONTEND_URL') . '/auth/callback?error=socialite_error&message=' . $e->getMessage());
        }

        // Find or create user
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                // User exists with email, link Google account
                $user->google_id = $googleUser->id;
                $user->photo = $user->photo ?? $googleUser->getAvatar();
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'photo' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(24)), // Create a random, unusable password
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('student');
            }
        }
        
        // At this point, we have a valid $user object.
        // Now, generate tokens using the SAME logic as AuthController.

        // 1. Generate JWT Access Token
        $accessToken = JWTAuth::fromUser($user);

        // 2. Generate and store custom Refresh Token
        $refreshToken = Str::random(60);
        $refreshTokenExpiresAt = now()->addMinutes((int) Config::get('jwt.refresh_ttl'));

        $user->refresh_token = Hash::make($refreshToken);
        $user->refresh_token_expires_at = $refreshTokenExpiresAt;
        $user->save();

        // 3. Redirect to frontend with both tokens
        $queryParams = http_build_query([
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);

        return redirect(env('FRONTEND_URL') . '/auth/callback?' . $queryParams);
    }
}
