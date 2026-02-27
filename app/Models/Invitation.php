<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    protected $fillable = [
        'collocation_id',
        'sendBy_id',
        'acceptedBy_id',
        'email',
        'token',
        'status',
    ];

    public function collocation()
    {
        return $this->belongsTo(Collocation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sendBy_id');
    }

    public function acceptor()
    {
        return $this->belongsTo(User::class, 'acceptedBy_id');
    }
}
