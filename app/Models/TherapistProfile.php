<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TherapistProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'specialization',
        'gender',
        'rating',
        'total_reviews',
        'bio',
        'base_address',
        'base_latitude',
        'base_longitude',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
