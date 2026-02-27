<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Membership extends Model
{
    use SoftDeletes;
    const DELETED_AT = 'left_at';

    protected $fillable = [
        'user_id',
        'collocation_id',
        'role',
        'balance',
        'joined_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function collocation()
    {
        return $this->belongsTo(Collocation::class);
    }
}
