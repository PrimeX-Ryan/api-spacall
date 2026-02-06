<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingLocation;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * List therapists available for immediate booking.
     */
    public function availableTherapists(Request $request): JsonResponse
    {
        try {
            $therapists = Provider::with(['user', 'therapistProfile', 'services'])
                ->where('type', 'therapist')
                ->where('is_available', true)
                ->where('verification_status', 'verified')
                ->where('is_active', true)
                ->get();

            return response()->json([
                'therapists' => $therapists
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching therapists',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Create a new "Book Now" booking.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'provider_id' => 'required|exists:providers,id',
            'address' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'city' => 'required|string',
            'province' => 'required|string',
            'customer_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = Service::find($request->service_id);
        $provider = Provider::find($request->provider_id);

        if (!$provider->is_available || !$provider->is_active) {
            return response()->json(['message' => 'Therapist is no longer available.'], 422);
        }

        DB::beginTransaction();
        try {
            $booking = Booking::create([
                'customer_id' => $request->user()->id,
                'provider_id' => $provider->id,
                'service_id' => $service->id,
                'booking_type' => 'home_service',
                'schedule_type' => 'now',
                'status' => 'pending',
                'service_price' => $service->base_price,
                'total_amount' => $service->base_price, // Simplifying for now
                'customer_notes' => $request->customer_notes,
                'payment_method' => 'cash', // Default for now
            ]);

            BookingLocation::create([
                'booking_id' => $booking->id,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // Set therapist to unavailable once booked (simplified logic)
            $provider->update(['is_available' => false]);

            DB::commit();

            return response()->json([
                'message' => 'Booking created successfully',
                'booking' => $booking->load(['location', 'provider.user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create booking', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update booking status (Therapist only).
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $booking = Booking::findOrFail($id);
        
        // Ensure only the assigned provider can update
        if ($booking->provider_id !== $request->user()->providers()->first()?->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:accepted,en_route,arrived,in_progress,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $oldStatus = $booking->status;
        $newStatus = $request->status;

        $booking->update(['status' => $newStatus]);

        if ($newStatus === 'accepted') {
            $booking->update(['accepted_at' => now()]);
        } elseif ($newStatus === 'in_progress') {
            $booking->update(['started_at' => now()]);
        } elseif ($newStatus === 'completed') {
            $booking->update(['completed_at' => now()]);
            // Make therapist available again
            $booking->provider->update(['is_available' => true]);
        } elseif ($newStatus === 'cancelled') {
            $booking->update(['cancelled_at' => now(), 'cancelled_by' => 'provider']);
            $booking->provider->update(['is_available' => true]);
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'booking' => $booking
        ]);
    }

    /**
     * Track booking status and therapist location.
     */
    public function track($id): JsonResponse
    {
        $booking = Booking::with(['provider.user', 'provider.locations' => function($q) {
            $q->latest()->limit(1);
        }])->findOrFail($id);

        return response()->json([
            'booking_status' => $booking->status,
            'therapist_location' => $booking->provider->locations->first(),
            'eta_minutes' => 15, // Placeholder for actual ETA logic
        ]);
    }
}
