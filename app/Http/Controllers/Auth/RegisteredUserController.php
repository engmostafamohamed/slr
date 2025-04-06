<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Doctrine\Common\Lexer\Token;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Otp;
use App\Mail\OtpMail;
use App\Helpers\OtpHelper;
class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $user = User::create([
            'user_name' => $request->user_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);
        // Assign "customer" role
        $user->assignRole('customer');


        // Determine OTP type (email or phone)
        $type = $request->email ? 'email_verification' : 'phoneNumber_verification';

        // Generate and send OTP
        OtpHelper::generateOtp($user, $type);
        return response()->json(['message' => __('userRegistration.send_OTP_to_your_email')], 201);
    }
}
