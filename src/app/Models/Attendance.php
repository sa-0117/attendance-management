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
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
    ];

    public function breaks() {
	    return $this->hasMany(BreakTime::class);
    }

    public function approval() {
        return $this->hasOne(Approval::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

        public function getBreakMinutesAttribute()
    {
        return $this->breaks->sum(function($break) {
            return Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
        });
    }

    public function getBreakTimeFormattedAttribute()
    {
        $minutes = $this->break_minutes;
        return $minutes ? gmdate('H:i', $minutes * 60) : '';
    }

    public function getWorkMinutesAttribute()
    {
        if ($this->clock_in && $this->clock_out) {
            return \Carbon\Carbon::parse($this->clock_out)
                ->diffInMinutes(\Carbon\Carbon::parse($this->clock_in))
                - $this->break_minutes;
        }
        return null;
    }

}
