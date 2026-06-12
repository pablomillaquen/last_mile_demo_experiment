<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoutePackage extends Model
{
    protected $fillable = [
        'route_id',
        'package_id',
        'sequence',
        'assigned_at',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
        ];
    }
}
