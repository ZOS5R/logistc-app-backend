<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'image',
        'position',
        'date_of_birth',
        'gender',
        'nationality',
        'marital_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Relationship to the User model
    }
}
