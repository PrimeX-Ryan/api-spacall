<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'customer_id',
        'provider_id',
        'service_id',
        'booking_type',
        'schedule_type',
        'customer_tier',
        'assignment_type',
        'scheduled_at',
        'duration_minutes',
        'status',
        'accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'cancellation_fee',
        'service_price',
        'distance_km',
        'distance_surcharge',
        'subtotal',
        'platform_fee',
        'promo_discount',
        'total_amount',
        'payment_method',
        'payment_status',
        'customer_notes',
        'provider_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'service_price' => 'decimal:2',
        'distance_km' => 'decimal:2',
        'distance_surcharge' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'promo_discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class, 'provider_id'); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class, 'service_id'); }
    public function assignments(): HasMany { return $this->hasMany(BookingAssignment::class); }
    public function location(): HasOne { return $this->hasOne(BookingLocation::class); }
    public function timeline(): HasMany { return $this->hasMany(BookingTimeline::class); }
}
