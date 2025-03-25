<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewardItem extends Model
{
    protected $fillable = ['name', 'description', 'points_cost'];

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }
}
