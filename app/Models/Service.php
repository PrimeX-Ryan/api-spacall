<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['category_id','name','slug','description','duration_minutes','base_price','is_active','meta'];

    protected static function booted(): void
    {
        static::creating(function ($s) {
            if (empty($s->slug) && !empty($s->name)) {
                $s->slug = Str::slug($s->name);
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function providers(): BelongsToMany
    {
        return $this->belongsToMany(Provider::class, 'provider_services')->withPivot('price','is_available');
    }
}
