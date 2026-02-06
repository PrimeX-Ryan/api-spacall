<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'latitude',
        'longitude',
        'is_online',
        'recorded_at',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
