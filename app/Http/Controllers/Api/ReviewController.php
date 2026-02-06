<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    /**
     * Rate and review a therapist after a completed booking.
     */
    public function store(Request $request, $booking_id): JsonResponse
    {
        $booking = Booking::findOrFail($booking_id);

        if ($booking->customer_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'You can only review completed bookings.'], 422);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'body' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $review = Review::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'provider_id' => $booking->provider_id,
                    'user_id' => $booking->customer_id,
                    'rating' => $request->rating,
                    'body' => $request->body,
                ]
            );

            // Update provider's average rating
            $provider = Provider::find($booking->provider_id);
            $avgRating = Review::where('provider_id', $provider->id)->avg('rating');
            $totalReviews = Review::where('provider_id', $provider->id)->count();

            $provider->update([
                'average_rating' => $avgRating,
                'total_reviews' => $totalReviews
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Review submitted successfully',
                'review' => $review
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to submit review', 'error' => $e->getMessage()], 500);
        }
    }
}
