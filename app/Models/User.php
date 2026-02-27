<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'userName',
        'firstName',
        'lastName',
        'email',
        'phone',
        'password',
        'about',
        'linkedin',
        'datOfBirth',
        'image',
        'reputation_points',
        'is_admin',
        'is_banned',
        'balance',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function collocations()
    {
        return $this->hasManyThrough(Collocation::class, Membership::class, 'user_id', 'id', 'id', 'collocation_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function sentPayments()
    {
        return $this->hasMany(Payment::class, 'sender_id');
    }

    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'receiver_id');
    }

    public function reputationLogs()
    {
        return $this->hasMany(ReputationLog::class);
    }

    public function getCurrentCollocation()
    {
        return $this->collocations()->first();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }
}
