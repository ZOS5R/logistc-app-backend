<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = ['user_id', 'type', 'days', 'hours', 'amount', 'description', 'status', 'note'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
