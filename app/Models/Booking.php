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

    protected $fillable = ['booking_number','customer_id','provider_id','service_id','booking_type','schedule_type','customer_tier','assignment_type','scheduled_at','duration_minutes','status','service_price','total_amount'];

    public function customer(): BelongsTo { return $this->belongsTo(User::class, 'customer_id'); }
    public function provider(): BelongsTo { return $this->belongsTo(Provider::class, 'provider_id'); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class, 'service_id'); }
    public function assignments(): HasMany { return $this->hasMany(BookingAssignment::class); }
    public function location(): HasOne { return $this->hasOne(BookingLocation::class); }
    public function timeline(): HasMany { return $this->hasMany(BookingTimeline::class); }
}
