<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LocationHazards
 * @package App\Models
 * @property int location_id
 * @property int hazard_id
 * @property int status
 */
class LocationHazards extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'location_id',
        'hazard_id',
        'status'
    ];

    public function location()
    {
        return $this->belongsTo(LocationBarangays::class, 'location_id');
    }

    public function hazard()
    {
        return $this->belongsTo(Hazards::class, 'hazard_id');
    }
}
