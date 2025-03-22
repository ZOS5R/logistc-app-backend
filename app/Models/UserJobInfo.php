<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserJobInfo extends Model
{
    use HasFactory;

    protected $table = 'users_job_info';

    protected $fillable = [
        'user_id',
        'basic_salary',
        'direct_manger_id',
        'employemnt_date',
        'department',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function directManager()
    {
        return $this->belongsTo(User::class, 'direct_manger_id');
    }
}
