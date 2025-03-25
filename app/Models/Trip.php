<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    // تحديد اسم الجدول في قاعدة البيانات
    protected $table = 'trips';

    // الأعمدة التي يمكن تعبئتها تلقائياً
    protected $fillable = [
        'driver_id',
        'trip_description',
        'pick_up_latitude',
        'pick_up_longitude',
        'drop_off_latitude',
        'drop_off_longitude',
        'status',
        "trip_date",
        'start_trip_at',
        'load_at',
        'arrival_at',
        'unload_at',
        'accepted_at',
        'completed_at'
    ];
}
