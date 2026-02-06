<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingLocation extends Model
{
    use HasFactory;

    protected $fillable = ['booking_id','address','barangay','city','province','postal_code','latitude','longitude','landmark','delivery_instructions'];

    public function booking(): BelongsTo { return $this->belongsTo(Booking::class); }
}
