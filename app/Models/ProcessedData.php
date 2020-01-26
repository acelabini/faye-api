<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProcessedData
 * @package App\Models
 */
class ProcessedData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'data',
        'publish',
        'processed_by'
    ];

    protected $casts = [
        'data' => 'array'
    ];

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
