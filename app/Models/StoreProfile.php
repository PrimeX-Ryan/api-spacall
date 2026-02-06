<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'store_name',
        'description',
        'address',
        'barangay',
        'city',
        'province',
        'postal_code',
        'latitude',
        'longitude',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
