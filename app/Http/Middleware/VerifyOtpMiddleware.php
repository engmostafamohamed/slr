<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class VerifyOtpMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect language from request header
        $locale = $request->header('Accept-Language', 'en');
        App::setLocale(in_array($locale, ['en', 'ar']) ? $locale : 'en');

        // Define validation rules
        $rules = [
            'email' => 'nullable|email|exists:users,email',
            'phone' => [
                'nullable',
                'string',
                'regex:/^(010|011|012|015)\d{8}$/',
                'exists:users,phone',
            ],
            'otp_code' => 'required|digits:6',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $this->getCustomMessages());

        // Ensure at least one of `email` or `phone` is provided
        if (!$request->has('email') && !$request->has('phone')) {
            return response()->json([
                'errors' => ['email_or_phone' => __('userRegistration.email_or_phone_required')]
            ], 422);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        return $next($request);
    }

    private function getCustomMessages()
    {
        return [
            'email.email' => __('userRegistration.email_invalid'),
            'email.exists' => __('userRegistration.email_not_found'),
            'phone.regex' => __('userRegistration.phone_invalid'),
            'phone.exists' => __('userRegistration.phone_not_found'),
            'otp_code.required' => __('userRegistration.otp_required'),
            'otp_code.digits' => __('userRegistration.otp_invalid'),
            'email_or_phone' => __('userRegistration.email_or_phone_required'),
        ];
    }
}
