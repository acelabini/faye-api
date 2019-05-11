<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Roles
 * @package App\Models
 * @property string name
 */
class Roles extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];
}
