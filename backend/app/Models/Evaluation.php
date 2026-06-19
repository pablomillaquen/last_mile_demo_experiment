<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'executed_at',
        'parameters',
        'total_deliveries',
        'total_routes',
        'metrics_summary',
        'output_path',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'parameters' => 'array',
            'metrics_summary' => 'array',
        ];
    }
}
