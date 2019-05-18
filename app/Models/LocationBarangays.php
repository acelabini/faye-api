<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LocationBarangays
 * @package App\Models
 * @property integer location_id
 * @property string name
 * @property string center
 * @property float area
 * @property \DateTime created_at
 * @property \DateTime deleted_at
 */
class LocationBarangays extends Model
{
    use SoftDeletes;

    const UPDATED_AT = null;

    protected $fillable = [
        'location_id',
        'name',
        'center',
        'area'
    ];
}
