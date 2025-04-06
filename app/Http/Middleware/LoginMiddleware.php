<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Validation\Rules;
class LoginMiddleware
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

        // Get the route name
        $routeName = $request->route()->getName();

        // Define userRegistration rules
        $rules = $this->getValidationRules($routeName);

        if ($rules) {
            $validator = Validator::make($request->all(), $rules, $this->getCustomMessages());

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
        }
        return $next($request);
    }
    private function getValidationRules($routeName)
    {
        $rules = [
            'email' => 'required_without:phone|email|exists:users,email',
            'phone' => 'required_without:email|string|max:20|exists:users,phone',
            'password' => ['required', 'string','min:8'],
        ];

        return $rules[$routeName] ?? null;
    }

    private function getCustomMessages()
    {
        return [
            'email.required' => __('userRegistration.email_required'),
            'email.email' => __('userRegistration.email_invalid'),
            'email.lowercase' => __('userRegistration.email_invalid'),
            'email.unique' => __('userRegistration.email_unique'),
            'phone.required' => __('userRegistration.phone_required'),
            'phone.unique' => __('userRegistration.phone_unique'),
            'phone.regex' => __('userRegistration.phone_invalid'),
            'password.required' => __('userRegistration.password_required'),
            'password.min' => __('userRegistration.password_min')
        ];
    }
}
