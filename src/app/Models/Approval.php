<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'attendance_id',
        'user_id',
        'admin_id',
        'clock_in',
        'clock_out',
        'breaks',
        'remarks',
        'status',
    ];

    protected $casts = [
        'breaks' => 'array',
    ];

    public function getStatusLabelAttribute()
    {
        if ($this->status === 'pending') {
            return '承認待ち';
        } elseif ($this->status === 'approved') {
            return '承認済み';
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attendance() {
	    return $this->belongsTo(Attendance::class, 'attendance_id');
}

}
