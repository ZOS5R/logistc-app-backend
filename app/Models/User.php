<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'points'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function pointsTransactions()
    {
        return $this->hasMany(PointsTransaction::class);
    }

    // تحديث النقاط وإنشاء سجل لها
    public function updatePoints($points, $reason, $related = null)
    {
        $this->increment('points', $points);

        $transaction = new PointsTransaction([
            'points_change' => $points,
            'reason' => $reason,
        ]);

        if ($related) {
            $transaction->related_id = $related->id;
            $transaction->related_type = get_class($related);
        }

        $this->pointsTransactions()->save($transaction);
    }

}
