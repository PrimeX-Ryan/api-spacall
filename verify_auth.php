<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Otp;
use App\Models\User;
use App\Models\Provider;
use App\Models\TherapistProfile;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

$mobile = '09111111111';

// Cleanup if exists
User::where('mobile_number', $mobile)->delete();
Otp::where('mobile_number', $mobile)->delete();

echo "1. Simulating OTP Verification...\n";
Otp::create([
    'mobile_number' => $mobile,
    'otp_code' => '123456',
    'expires_at' => now()->addMinutes(5),
    'used' => true
]);

echo "2. Simulating Registration as Therapist...\n";
$request = new Request([
    'mobile_number' => $mobile,
    'first_name' => 'Test',
    'last_name' => 'Therapist',
    'gender' => 'male',
    'date_of_birth' => '1990-01-01',
    'pin' => '123456',
    'role' => 'therapist'
]);

$controller = app(AuthController::class);
$response = $controller->registerProfile($request);
echo "Registration Response: " . json_encode($response->getData(), JSON_PRETTY_PRINT) . "\n";

echo "3. Verifying Database Records...\n";
$user = User::where('mobile_number', $mobile)->first();
if (!$user) {
    echo "FAIL: User not created.\n";
    exit(1);
}
echo "User Role: {$user->role}\n";

$provider = Provider::where('user_id', $user->id)->first();
echo "Provider Created: " . ($provider ? "YES [ID: {$provider->id}]" : "NO") . "\n";

if ($provider) {
    $profile = TherapistProfile::where('provider_id', $provider->id)->first();
    echo "Therapist Profile Created: " . ($profile ? "YES" : "NO") . "\n";
}

echo "4. Simulating Login...\n";
$loginRequest = new Request([
    'mobile_number' => $mobile,
    'pin' => '123456'
]);
$loginResponse = $controller->loginPin($loginRequest);
echo "Login Response: " . json_encode($loginResponse->getData(), JSON_PRETTY_PRINT) . "\n";

echo "\nVerification Complete! ✅\n";
