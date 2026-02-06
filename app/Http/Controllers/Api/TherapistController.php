<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TherapistController extends Controller
{
    /**
     * Display a listing of all verified therapists.
     */
    public function index(Request $request): JsonResponse
    {
        $therapists = Provider::with(['user', 'therapistProfile'])
            ->where('type', 'therapist')
            ->where('verification_status', 'verified')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'therapists' => $therapists
        ]);
    }

    /**
     * Display the specified therapist.
     */
    public function show(string $uuid): JsonResponse
    {
        $therapist = Provider::with(['user', 'therapistProfile', 'services'])
            ->where('uuid', $uuid)
            ->where('type', 'therapist')
            ->where('verification_status', 'verified')
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'therapist' => $therapist
        ]);
    }

    /**
     * Get the authenticated therapist's profile.
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Find the provider profile of type therapist
        $provider = $user->providers()
            ->with(['therapistProfile'])
            ->where('type', 'therapist')
            ->first();

        if (!$provider) {
            return response()->json([
                'message' => 'Therapist profile not found for this user.'
            ], 404);
        }

        return response()->json([
            'user' => $user,
            'provider' => $provider
        ]);
    }
}
