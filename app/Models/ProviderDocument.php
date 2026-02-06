<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'type',
        'file_path',
        'file_name',
        'mime_type',
        'uploaded_at',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}
