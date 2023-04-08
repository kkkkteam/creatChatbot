<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Phone number validation
        $request->validate([
            'phone_number' => ['required', 'regex:/^[4-9][0-9]{7}$/'],
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if ($user) {
            // Redirect to password page
            return redirect()->route('password_page');
        } else {
            // Show OTP button
            return view('auth.register', ['phone_number' => $request->phone_number]);
        }
    }

    public function showRegistrationForm(Request $request)
    {
        return view('auth.register', ['phone_number' => $request->phone_number]);
    }

    public function register(Request $request)
    {
        // Validate OTP and passwords
        $request->validate([
            'otp' => ['required', 'integer'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Verify OTP (use your third-party service here)
        if ($this->verifyOTP($request->otp)) {
            // Create user and redirect to password page
            User::create([
                'phone_number' => $request->phone_number,
                'password' => bcrypt($request->password),
            ]);
            
            return redirect()->route('password_page');
        } else {
            // Show re-send OTP button
            return view('auth.register', ['phone_number' => $request->phone_number, 'error' => 'Invalid OTP']);
        }
    }

    private function verifyOTP($otp)
    {
        // Implement your OTP verification logic with the third-party service here
        return true; // For demonstration purposes only
    }
}
