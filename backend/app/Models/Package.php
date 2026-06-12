<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'received_at',
        'tracking_number',
        'recipient_name',
        'delivery_address',
        'district',
        'city',
        'latitude',
        'longitude',
    ];

    public function routePackage(): HasOne
    {
        return $this->hasOne(RoutePackage::class);
    }

    public function getAssignedAttribute(): bool
    {
        return $this->relationLoaded('routePackage')
            ? $this->routePackage !== null
            : $this->routePackage()->exists();
    }

    protected $appends = ['assigned'];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'received_at' => 'datetime',
        ];
    }
}
