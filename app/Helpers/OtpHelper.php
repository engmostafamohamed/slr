<?php

namespace App\Helpers;

use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Twilio\Rest\Client;

class OtpHelper
{
    public static function generateOtp(User $user, string $type): int
    {
        $otpCode = rand(100000, 999999);

        // Remove existing OTPs for this user and type
        // Otp::where('user_id', $user->id)->where('type', $type)->delete();

        // Store new OTP
        Otp::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'type' => $type,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        // Send OTP based on type
        if ($type === 'email_verification') {
            self::sendOtpEmail($user->email, $otpCode);
        } elseif ($type === 'phoneNumber_verification') {
            self::sendOtpSms($user->phone, $otpCode);
        }

        // Log::info("OTP sent: {$otpCode}, Type: {$type}, User ID: {$user->id}");
        return $otpCode;
    }

    public static function sendOtpEmail(string $email, int $otpCode)
    {
        Mail::raw("Your OTP is: $otpCode", function ($message) use ($email) {
            $message->to($email)->subject('Your OTP Code');
        });
        // Log::info("OTP Email sent to: $email");
    }

    public static function sendOtpSms(string $phone, int $otpCode)
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from = config('services.twilio.from');

        if (!$sid || !$token || !$from) {
            Log::error("Twilio configuration missing!");
            return;
        }
        try {
            $client = new Client($sid, $token);
            $client->messages->create($phone, [
                'from' => $from,
                'body' => "Your OTP code is: $otpCode",
            ]);

            Log::info("OTP SMS sent to: $phone");
        } catch (\Exception $e) {
            Log::error("Failed to send OTP to $phone: " . $e->getMessage());
        }
    }
}
