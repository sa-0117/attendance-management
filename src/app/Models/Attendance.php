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

    public function breaks() {
	    return $this->hasMany(BreakTime::class);
    }

    public function approvals() {
        return $this->hasOne(Approvals::class);
    }

    public function users() {
        return $this->belongsTo(User::class);
    }

}
