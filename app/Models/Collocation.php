<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collocation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'memberships', 'collocation_id', 'user_id')
            ->withPivot('role', 'balance', 'joined_at')
            ->withTimestamps();
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function expenses()
    {
        return $this->hasManyThrough(Expense::class, Category::class);
    }

    public function invitations()
    {
        return $this->hasMany(Invitation::class);
    }

    public function owner()
    {
        return $this->users()->wherePivot('role', 'owner')->first();
    }
}
