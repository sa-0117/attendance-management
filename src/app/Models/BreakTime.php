<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
        protected $fillable = [
        'break_start',
        'break_end',
    ];

    public function attendances() {
	    return $this->belongsTo(Attendance::class);
    }
}
