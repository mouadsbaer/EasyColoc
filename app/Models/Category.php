<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'description',
        'collocation_id',
    ];

    public function collocation()
    {
        return $this->belongsTo(Collocation::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }
}
