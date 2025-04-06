<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\OtpHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
class OtpController extends Controller
{
    public function resendOtp(Request $request)
    {
        // The validation should be done in middleware
        $user = User::where('email', $request->email)->orWhere('phone', $request->phone)->firstOrFail();

        // Determine OTP type (email or phone)
        $type = $request->email ? 'email_verification' : 'phoneNumber_verification';

        // Generate and send OTP
        OtpHelper::generateOtp($user, $type);

        return response()->json(['message' => __('userRegistration.send_OTP_to_your_email')], 201);
    }
}


