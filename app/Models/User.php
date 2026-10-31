<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\PhoneNumber;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'password', 'is_active', 'can_access_admin', 'can_access_pos', 'can_access_service', 'pos_shares_admin_login'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'service' => $this->can_access_service,
            'pos'     => $this->can_access_pos,
            default   => $this->can_access_admin,
        };
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function setPhoneAttribute(?string $value): void
    {
        $this->attributes['phone'] = $value ? PhoneNumber::normalize($value) : null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
            'can_access_admin'  => 'boolean',
            'can_access_pos'    => 'boolean',
            'can_access_service' => 'boolean',
            'pos_shares_admin_login' => 'boolean',
        ];
    }
}
