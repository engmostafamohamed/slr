<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Mail\OtpMail;
use App\Helpers\OtpHelper;

class ResetPasswordController extends Controller
{
    /**
     * Send OTP for Password Reset (Email or Phone).
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Ensure the account is verified
        if (!$user->is_verified) {
            return response()->json(['message' => 'Your account is not verified.'], 403);
        }

        // Generate OTP
        $otpCode = mt_rand(100000, 999999);
        $otpType = 'reset_password';

        // Store OTP in the database
        Otp::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'password_reset'],
            ['type'=> $otpType,'otp_code' => $otpCode, 'expires_at' => Carbon::now()->addMinutes(10)]
        );

        // Send OTP via Email or SMS
        if ($user->phone) {
            // Send OTP via SMS (phone number exists)
            OtpHelper::sendOtpSms($user->phone, $otpCode);
        } else {
            // Send OTP via Email
            Mail::to($user->email)->send(new OtpMail($otpCode));
        }

        return response()->json(['message' => 'OTP sent successfully for password reset.']);
    }

    /**
     * Verify OTP and Reset Password.
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|digits:6',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        // Find OTP
        $otp = Otp::where('user_id', $user->id)
                  ->where('otp_code', $request->otp)
                  ->where('expires_at', '>', Carbon::now())
                  ->first();

        if (!$otp) {
            return response()->json(['message' => __('userRegistration.invalid_or_expired_OTP')], 422);
        }

        // Reset password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Mark OTP as used
        $otp->update(['is_used' => true]);

        return response()->json(['message' => __('userRegistration.reset_password_successfully')]);
    }
}
