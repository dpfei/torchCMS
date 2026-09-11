<?php

namespace App\Models;

use App\Support\PasswordSalt;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable, HasRoles;

    /**
     * 后台管理员使用 admin 守卫
     *
     * @var string
     */
    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * 密码写入时统一做加盐哈希。
     *
     * 传入的值已经是哈希（例如从数据库读回再写回）时原样保留，避免二次哈希。
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): ?string {
                if (blank($value) || Hash::isHashed($value)) {
                    return $value;
                }

                return PasswordSalt::hash($value);
            },
        );
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
