<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status_in',
        'status_out',
        'notes',
    ];

    // ربط السجل بالمستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
