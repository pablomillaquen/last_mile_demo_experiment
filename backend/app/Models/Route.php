<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    protected $fillable = [
        'name',
        'route_date',
        'notes',
    ];

    public function routePackages(): HasMany
    {
        return $this->hasMany(RoutePackage::class);
    }

    protected function casts(): array
    {
        return [
            'route_date' => 'date',
        ];
    }
}
