<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'status',
    ];

    protected $casts = [
        'work_date' => 'datetime',
    ];

    public function breaks() {
	    return $this->hasMany(BreakTime::class);
    }

    public function approvals() {
        return $this->hasOne(Approvals::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

}
