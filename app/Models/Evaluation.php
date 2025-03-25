<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluation_date',
        'month',
        'year',
        'behavior_and_ethics',
        'areas_of_improvement',
        'supervisor_notes',
        'monthly_percentage',
        'overall_percentage',
    ];


    // العلاقة مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
