<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Twilio\Rest\Client;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role_id' => 2 // default user role
        ]);

        // Send OTP for phone verification
        $this->sendOTP($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string'
        ]);

        $user = User::where('email', $validated['login'])
            ->orWhere('phone', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function sendOTP($user)
    {
        $otp = rand(100000, 999999);
        $user->phone_otp = $otp;
        $user->phone_otp_expires_at = now()->addMinutes(5);
        $user->save();

        // Send via WhatsApp/Telegram/SMS
        // Implement your messaging provider here
        $this->sendWhatsAppMessage($user->phone, "Your OTP is: $otp");
        
        return $otp;
    }

    public function verifyOTP(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string'
        ]);

        $user = User::where('phone', $validated['phone'])->first();

        if (!$user || $user->phone_otp !== $validated['otp'] || 
            now()->gt($user->phone_otp_expires_at)) {
            return response()->json(['message' => 'Invalid OTP'], 400);
        }

        $user->verified_phone = true;
        $user->phone_otp = null;
        $user->phone_otp_expires_at = null;
        $user->save();

        return response()->json(['message' => 'Phone verified successfully']);
    }

    private function sendWhatsAppMessage($to, $message)
    {
        // Implementation for WhatsApp Business API or third-party service
        // Example using WATI or Twilio WhatsApp
    }

    public function socialLogin(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|in:facebook,google',
            'provider_id' => 'required|string',
            'email' => 'required|email',
            'name' => 'required|string'
        ]);

        $user = User::where('provider_id', $validated['provider_id'])
            ->orWhere('email', $validated['email'])
            ->first();

        if (!$user) {
            $user = User::create([
                'username' => strtolower(str_replace(' ', '_', $validated['name'])) . rand(1000, 9999),
                'email' => $validated['email'],
                'phone' => '',
                'password' => Hash::make(uniqid()),
                'provider' => $validated['provider'],
                'provider_id' => $validated['provider_id'],
                'role_id' => 2
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
