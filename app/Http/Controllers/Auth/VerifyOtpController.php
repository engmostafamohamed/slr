<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;

class VerifyOtpController extends Controller
{
    public function verify(Request $request)
    {

        // Find OTP for email or phone
        $otpQuery = Otp::where('otp_code', $request->otp_code)
            ->where('expires_at', '>', Carbon::now());

        if ($request->email) {
            $user = User::where('email', $request->email)->first();
            $otpQuery->where('user_id', optional($user)->id)->where([['type', 'email_verification'],['is_used',0]]);
        } else {
            $user = User::where('phone', $request->phone)->first();
            $otpQuery->where('user_id', optional($user)->id)->where([['type', 'phoneNumber_verification'],['is_used',0]]);
        }

        $otp = $otpQuery->first();

        if (!$otp) {
            return response()->json(['message' => __('userRegistration.invalid_or_expired_OTP')], 400);
        }

        // Mark email or phone as verified
        if ($request->email) {
            $user->email_verified_at = Carbon::now();
            $user->is_verified=true;

        } else {
            $user->phone_verified_at = Carbon::now();
            $user->is_verified=true;
        }
        $user->save();

        $otp->is_used=true;
        $otp->save();

        return response()->json(['message' => __('userRegistration.account_verified_successfully')], 200);
    }
}
