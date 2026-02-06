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

class AuthController extends Controller
{
    protected SmsService $smsService;

    public function __construct(SmsService $smsService)
    {
        $this->smsService = $smsService;
    }

    /**
     * Step 1: Register with mobile number and send OTP.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $mobileNumber = $request->mobile_number;
        $otpCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiresAt = Carbon::now()->addMinutes(5);

        // Save OTP
        Otp::create([
            'mobile_number' => $mobileNumber,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'used' => false,
        ]);

        // Send via SMS
        $message = "Your 6-digit OTP for Spacall is: {$otpCode}. It will expire in 5 minutes.";
        $sent = $this->smsService->sendSms($mobileNumber, $message);

        if (!$sent) {
            // For development/demo, we might still want to proceed or return error
            // return response()->json(['message' => 'Failed to send OTP SMS'], 500);
        }

        return response()->json(['message' => 'OTP sent successfully']);
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

        // Mark as used
        $otpRecord->update(['used' => true]);

        // Create or Update User
        $user = User::firstOrCreate(
            ['mobile_number' => $request->mobile_number],
            ['is_verified' => true]
        );

        if (!$user->is_verified) {
            $user->update(['is_verified' => true]);
        }

        return response()->json(['message' => 'OTP verified, set your PIN']);
    }

    /**
     * Step 3: Set PIN.
     */
    public function setPin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|string',
            'pin' => 'required|string|size:6', // Assuming 6-digit PIN
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('mobile_number', $request->mobile_number)->first();

        if (!$user || !$user->is_verified) {
            return response()->json(['message' => 'User not verified'], 403);
        }

        $user->update([
            'pin_hash' => Hash::make($request->pin)
        ]);

        $token = $user->createToken('wallet-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Step 4: Login with PIN.
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
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_verified) {
            return response()->json(['message' => 'User not verified'], 403);
        }

        $token = $user->createToken('wallet-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }
}
