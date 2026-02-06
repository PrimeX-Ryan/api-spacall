<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use App\Services\SmsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class AuthController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Step 1: Initial Entry.
     * Checks if user exists. If yes, go to PIN login. If no, send OTP for registration.
     */
    public function loginEntry(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mobileNumber = $request->mobile_number;
        $user = User::where('mobile_number', $mobileNumber)->first();

        // Path B: Returning User
        if ($user && $user->is_verified && !empty($user->pin_hash)) {
            return response()->json([
                'status' => 'existing_user',
                'next_step' => 'pin_login',
                'message' => 'Welcome back! Please enter your PIN.'
            ]);
        }

        // Path A: New User (or unverified/no pin) - Trigger OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        Otp::create([
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'used' => false,
        ]);

        $message = "Your 6-digit OTP for Spacall is: {$otpCode}. It will expire in 5 minutes.";
        $this->smsService->sendSms($mobileNumber, $message);

        return response()->json([
            'status' => 'new_user',
            'next_step' => 'otp_verification',
            'message' => 'OTP sent successfully'
        ]);
    }

    /**
     * Step 2: Verify OTP.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otpRecord = Otp::where('mobile_number', $request->mobile_number)
            ->where('otp_code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $otpRecord->update(['used' => true]);

        // Just mark mobile as verified in session/response context
        // The actual user creation happens in registerProfile
        return response()->json([
            'message' => 'OTP verified',
            'next_step' => 'registration'
        ]);
    }

    /**
     * Step 3: Register profile + set PIN.
     */
    public function registerProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'gender' => 'required|in:male,female,other,prefer_not_to_say',
            'date_of_birth' => 'required|date',
            'pin' => 'required|string|size:6',
            'profile_photo' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Ensure OTP was verified (simple check for demo/mvp, can use signed tokens in real prod)
        $otpVerified = Otp::where('mobile_number', $request->mobile_number)
            ->where('used', true)
            ->where('updated_at', '>', Carbon::now()->subMinutes(15))
            ->exists();

        if (!$otpVerified) {
            return response()->json(['message' => 'Mobile number not verified by OTP'], 403);
        }

        $user = User::updateOrCreate(
            ['mobile_number' => $request->mobile_number],
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'gender' => $request->gender,
                'date_of_birth' => $request->date_of_birth,
                'pin_hash' => Hash::make($request->pin),
                'is_verified' => true
            ]
        );

        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $user->update(['profile_photo_url' => Storage::url($path)]);
        }

        $token = $user->createToken('wallet-token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Secure Login with PIN.
     */
    public function loginPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
            'pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('mobile_number', $request->mobile_number)->first();

        if (!$user || !Hash::check($request->pin, $user->pin_hash)) {
            return response()->json(['message' => 'Invalid PIN'], 401);
        }

        $token = $user->createToken('wallet-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Forgot PIN: Send OTP to reset.
     */
    public function forgotPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mobileNumber = $request->mobile_number;
        $user = User::where('mobile_number', $mobileNumber)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Trigger OTP
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Otp::create([
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(5),
            'used' => false,
        ]);

        $this->smsService->sendSms($mobileNumber, "Your PIN reset code is: {$otpCode}");

        return response()->json(['message' => 'Reset code sent successfully']);
    }

    /**
     * Reset PIN using OTP.
     */
    public function resetPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
            'otp' => 'required|string|size:6',
            'new_pin' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otpRecord = Otp::where('mobile_number', $request->mobile_number)
            ->where('otp_code', $request->otp)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        $otpRecord->update(['used' => true]);

        $user = User::where('mobile_number', $request->mobile_number)->first();
        $user->update(['pin_hash' => Hash::make($request->new_pin)]);

        return response()->json(['message' => 'PIN reset successful']);
    }
}
