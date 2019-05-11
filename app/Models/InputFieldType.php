<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class InputFieldType
 * @package App\Models
 * @property string name
 * @property \DateTime created_at
 * @property \DateTime updated_at
 * @property \DateTime deleted_at
 */
class InputFieldType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name'
    ];
}
