<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser, HasAvatar
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password',
        'avatar', 'title', 'role', 'subname', 'bio', 'birthdate',
        'phone', 'location', 'website',
        'addr', 'district', 'zip', 'country',
        'cnpj', 'ie', 'rs', 'razao_social', 'nome_fantasia',
        'links', 'github', 'linkedin', 'twitter',
        'instagram', 'tiktok', 'youtube', 'facebook', 'fb_page',
        'medium', 'devto', 'codepen', 'behance',
        'dribbble', 'deviantart', 'pinterest',
        'locale', 'access_level',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'birthdate'         => 'date',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->access_level === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->access_level === 'admin';
    }

    public function isEditor(): bool
    {
        return $this->access_level === 'editor';
    }

    public function isAtLeastAdmin(): bool
    {
        return in_array($this->access_level, ['super_admin', 'admin']);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (!$this->avatar) return null;

        // O banco guarda só o nome do arquivo (ex: foto.png)
        // O FileUpload com ->directory('avatars') cuida do path no disco
        $path = str_contains($this->avatar, '/') ? $this->avatar : 'avatars/' . $this->avatar;

        return asset('storage/' . $path);
    }

    public function getAgeAttribute(): int
    {
        return $this->birthdate ? $this->birthdate->age : 0;
    }
}
