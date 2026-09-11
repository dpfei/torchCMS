<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;
use Throwable;

/**
 * 管理员密码的加盐哈希。
 *
 * bcrypt 自身已包含随机盐，这里附加的是「应用级固定盐」：
 * 让同一口令在本系统内产生与其他系统完全不同的哈希，
 * 避免数据库泄露后被拿去其他站点撞库。
 */
class PasswordSalt
{
    /**
     * 当前生效的盐值。
     *
     * 优先使用 ADMIN_PASSWORD_SALT；未配置时退化为基于 APP_KEY 派生，
     * 确保加盐逻辑始终成立，不会出现「无盐」的中间状态。
     */
    public static function value(): string
    {
        $salt = (string) config('admin.password_salt', '');

        if ($salt !== '') {
            return $salt;
        }

        return 'derived:'.hash('sha256', (string) config('app.key', '').'|admin-password');
    }

    /**
     * 明文密码 -> 加盐哈希
     */
    public static function hash(string $plain): string
    {
        return Hash::make(self::digest($plain));
    }

    /**
     * 校验明文密码是否匹配已有哈希。
     *
     * 哈希为空、格式非法或已损坏（例如被手工改过）时一律按失败处理，
     * 不能让底层 hasher 抛出的异常冒到登录流程里变成 500。
     */
    public static function check(?string $plain, ?string $hashed): bool
    {
        if (blank($plain) || blank($hashed)) {
            return false;
        }

        try {
            return Hash::check(self::digest($plain), $hashed);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 把「明文 + 盐」压成定长摘要。
     *
     * 必须先摘要再交给 bcrypt：bcrypt 只取前 72 字节，若把盐直接拼在
     * 密码前面，超出 72 字节的密码部分会被静默丢弃，导致任何输入都能通过校验。
     * HMAC 输出固定 64 字节，既规避截断，也让超长密码不再被削弱。
     */
    protected static function digest(string $plain): string
    {
        return hash_hmac('sha256', $plain, self::value());
    }
}
