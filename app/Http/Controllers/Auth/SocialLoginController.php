<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SocialLoginRequest;
use App\Models\LinkedSocialAccount;
use App\Models\User;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;
use Illuminate\Support\Facades\Auth;
class SocialLoginController extends Controller
{
    public function login(SocialLoginRequest $request)
    {
        try {
            $provider = $request->get('provider');
            $accessToken = $request->get('access_token');

            // Fetch user details from provider
            $providerUser = Socialite::driver($provider)
                ->stateless()
                ->userFromToken($accessToken);

        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Invalid credentials or access token',
                'error' => $exception->getMessage(),
            ], 400);
        }

        if (!$providerUser) {
            return response()->json(['message' => 'Authentication failed'], 401);
        }

        $user = $this->findOrCreateUser($providerUser, $provider);

        // Generate API Token
        $token = $user->createToken('API Token')->plainTextToken;

        return response()->json([
            'message' => 'Logged in successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
        ]);
    }

    protected function findOrCreateUser(ProviderUser $providerUser, string $provider): User
    {
        // Check if social account exists
        $linkedSocialAccount = LinkedSocialAccount::where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->user;
        }

        // Find user by email
        $user = User::where('email', $providerUser->getEmail())->first();

        if (!$user) {
            // Create new user
            $user = User::create([
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'password' => bcrypt(str()->random(16)), // Random password
            ]);
            $user->markEmailAsVerified(); // Auto verify email
        }

        // Link social account
        $user->linkedSocialAccounts()->create([
            'provider_id' => $providerUser->getId(),
            'provider_name' => $provider,
        ]);

        return $user;
    }
}
