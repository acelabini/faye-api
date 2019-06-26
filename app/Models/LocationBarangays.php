<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class LocationBarangays
 * @package App\Models
 * @property integer location_id
 * @property string name
 * @property string center
 * @property float area
 * @property Carbon created_at
 * @property Carbon deleted_at
 */
class LocationBarangays extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'location_id',
        'name',
        'center',
        'area'
    ];
}
