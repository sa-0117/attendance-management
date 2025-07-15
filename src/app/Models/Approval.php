<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approvals extends Model
{
    protected $fillable = [
        'attendance_id',
        'status',
    ];

    public function attendances() {
	    return $this->belongsTo(Attendance::class);
}

}
