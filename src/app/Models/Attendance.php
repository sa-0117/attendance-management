<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendances extends Model
{
    protected $fillable = [
        'clock_in',
        'clock_out',
        'status'
    ];

    public function breakTimes() {
	    return $this->hasMany(BreakTime::class);
    }

    public function approvals() {
        return $this->hasOne(Approvals::class);
    }

    public function users() {
        return $this->belongsTo(User::class);
    }

}
