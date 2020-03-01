<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    protected $fillable = [
        'name',
        'message',
        'media',
        'status',
        'incident_datetime',
        'barangay_id',
        'user_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function barangay()
    {
        return $this->belongsTo(LocationBarangays::class, 'barangay_id');
    }
}
