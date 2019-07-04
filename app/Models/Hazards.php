<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Hazards
 * @package App\Models
 * @property string name
 * @property int created_by
 * @property int status
 */
class Hazards extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'created_by',
        'name',
        'status'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
