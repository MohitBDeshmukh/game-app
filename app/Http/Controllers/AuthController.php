<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    // 1 - Send OTP API
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Hardcoded OTP: 1234 (real app would generate dynamically)
        $otp = '1234';
        $expiresAt = Carbon::now()->addMinute();

        // Upsert OTP by phone
        Otp::updateOrCreate(
            ['phone' => $request->phone],
            ['otp' => $otp, 'expires_at' => $expiresAt]
        );

        // Don't return OTP in response
        return response()->json(['message' => 'OTP sent successfully']);
    }

    // 2 - Register API
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string|unique:users,phone|regex:/^[0-9]{10,15}$/',
            'name' => 'required|string|max:255',
            'dob' => 'required|date',
            'email' => 'required|email',
            'otp' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Check OTP
        $otpRecord = Otp::where('phone', $request->phone)->first();

        if (!$otpRecord || $otpRecord->otp !== $request->otp) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        if (Carbon::parse($otpRecord->expires_at)->isPast()) {
            return response()->json(['error' => 'OTP expired'], 400);
        }

        // Create user
        $user = User::create([
            'phone' => $request->phone,
            'name' => $request->name,
            'dob' => $request->dob,
            'email' => $request->email,
            'password' => Hash::make('password'), // set default or generate randomly
        ]);

        // Delete OTP after successful registration
        $otpRecord->delete();

        // Create JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
}
