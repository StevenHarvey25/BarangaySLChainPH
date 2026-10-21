<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'email_verified_at',
    'password',
    'google_id',
    'role',
    'is_active',
    'email_verification_otp',
    'email_verification_otp_expires_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isCitizen(): bool
    {
        return $this->role === 'citizen';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function citizenProfile(): HasOne
    {
        return $this->hasOne(CitizenProfile::class);
    }

    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    // All identity verification applications of this user
    public function identityVerifications(): HasMany
    {
        return $this->hasMany(IdentityVerification::class);
    }

    // The most recent application (or null if the citizen never submitted)
    public function latestIdentityVerification(): HasOne
    {
        return $this->hasOne(IdentityVerification::class)->latestOfMany();
    }

    // Returns: unverified, pending, verified, or rejected
    public function identityStatus(): string
    {
        return $this->latestIdentityVerification?->status ?? 'unverified';
    }

    // True only after staff or admin approved the application
    public function isIdentityVerified(): bool
    {
        return $this->identityStatus() === IdentityVerification::VERIFIED;
    }
}
