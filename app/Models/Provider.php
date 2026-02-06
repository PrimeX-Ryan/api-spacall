<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'verification_status',
        'is_active',
        'is_accepting_bookings',
        'uuid',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function therapistProfile(): HasMany
    {
        return $this->hasMany(TherapistProfile::class);
    }

    public function storeProfile(): HasMany
    {
        return $this->hasMany(StoreProfile::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProviderDocument::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(ProviderLocation::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'provider_services')->withPivot('price','is_available');
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
