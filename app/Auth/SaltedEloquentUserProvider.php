<?php

namespace App\Auth;

use App\Support\PasswordSalt;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Throwable;

/**
 * 支持应用级密码盐的管理员用户提供者。
 *
 * 校验时先用「盐 + 明文」比对；不一致再回退为原始明文比对，
 * 命中说明该账号仍是加盐改造之前写入的历史密码，此时自动升级为加盐哈希。
 * 这样老部署升级后无需重置任何账号密码即可平滑过渡。
 */
class SaltedEloquentUserProvider extends EloquentUserProvider
{
    /**
     * @param  array<string, mixed>  $credentials
     */
    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = (string) ($credentials['password'] ?? '');
        $hashed = (string) $user->getAuthPassword();

        if (PasswordSalt::check($plain, $hashed)) {
            return true;
        }

        if ($plain === '') {
            return false;
        }

        // 历史密码（未加盐）：校验通过后顺手升级为加盐哈希
        try {
            $isLegacyPassword = $this->hasher->check($plain, $hashed);
        } catch (Throwable) {
            // 哈希格式非法或已损坏，按失败处理，避免登录流程抛 500
            return false;
        }

        if (! $isLegacyPassword) {
            return false;
        }

        $user->forceFill(['password' => $plain])->saveQuietly();

        return true;
    }
}
