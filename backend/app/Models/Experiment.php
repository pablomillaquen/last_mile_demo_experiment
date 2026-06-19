<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experiment extends Model
{
    protected $fillable = [
        'identifier',
        'name',
        'description',
        'objective',
        'hypothesis',
        'baseline_evaluation_id',
        'evaluation_ids',
        'author',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_ids' => 'array',
            'baseline_evaluation_id' => 'integer',
        ];
    }

    public function getEvaluations(): Collection
    {
        return Evaluation::whereIn(
            'id',
            $this->evaluation_ids ?? []
        )->get();
    }

    public function baselineEvaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class, 'baseline_evaluation_id');
    }
}
