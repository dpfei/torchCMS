<?php

namespace App\Support;

use Illuminate\Http\Response;

/**
 * 后台登录验证码。
 *
 * 优先用 GD 绘制 PNG 图片；服务器未启用 GD 时自动降级为文字算术题。
 * 两种形式都把答案写入会话，校验入口统一为 verify()。
 */
class Captcha
{
    /**
     * 会话中的存储键
     */
    public const SESSION_KEY = 'admin_login_captcha';

    /**
     * 字符表：剔除 0/O、1/I/L 等易混淆字符
     */
    protected const ALPHABET = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

    /**
     * 当前环境是否具备图片渲染能力
     */
    public static function supportsImage(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    /**
     * 出一题并写入会话。
     *
     * 图片模式下题干即图片本身，返回的 prompt 为 null。
     *
     * @return array{answer: string, prompt: string|null}
     */
    public static function issue(): array
    {
        if (self::supportsImage()) {
            $code = self::randomCode();
            self::store($code);

            return ['answer' => $code, 'prompt' => null];
        }

        return self::issueArithmetic();
    }

    /**
     * 校验答案并立刻作废本题（一次性，防重放）
     */
    public static function verify(?string $input): bool
    {
        $payload = session()->pull(self::SESSION_KEY);

        if (! is_array($payload)) {
            return false;
        }

        if ((int) ($payload['expires_at'] ?? 0) < time()) {
            return false;
        }

        $answer = (string) ($payload['answer'] ?? '');
        $input = mb_strtoupper(trim((string) $input));

        return $answer !== '' && $input !== '' && hash_equals($answer, $input);
    }

    /**
     * 输出 PNG 图片，同时生成一道新题
     */
    public static function image(): Response
    {
        $code = self::randomCode();
        self::store($code);

        // 内置位图字体 font 5 已是最大字号，字符尺寸固定（约 9×15 像素）无法再调大。
        // 而图片在页面上按固定高度等比缩放，因此画布留白越多、字符显示得越小。
        // 这里把画布收紧到刚好容纳字符，显示时字就被明显放大。
        $font = 5;
        $charWidth = imagefontwidth($font);
        $charHeight = imagefontheight($font);

        $length = strlen($code);
        $marginX = 6;
        $step = $charWidth + 6;

        $width = $marginX * 2 + $step * $length;
        $height = $charHeight + 12;

        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 245, 246, 248);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        // 干扰线
        for ($i = 0; $i < 3; $i++) {
            $line = imagecolorallocate(
                $image,
                random_int(175, 220),
                random_int(175, 220),
                random_int(175, 220)
            );

            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $line);
        }

        // 噪点
        for ($i = 0; $i < 25; $i++) {
            $dot = imagecolorallocate(
                $image,
                random_int(160, 225),
                random_int(160, 225),
                random_int(160, 225)
            );

            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $dot);
        }

        // 逐字符绘制，位置与颜色随机
        for ($i = 0; $i < $length; $i++) {
            $color = imagecolorallocate(
                $image,
                random_int(20, 110),
                random_int(20, 110),
                random_int(20, 110)
            );

            imagestring(
                $image,
                $font,
                $marginX + $i * $step + random_int(-2, 2),
                random_int(3, 9),
                $code[$i],
                $color
            );
        }

        ob_start();
        imagepng($image);
        $binary = (string) ob_get_clean();
        imagedestroy($image);

        return new Response($binary, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    /**
     * 生成随机验证码字符
     */
    protected static function randomCode(): string
    {
        $length = max(3, (int) config('admin.captcha_length', 4));
        $alphabet = self::ALPHABET;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $code;
    }

    /**
     * 文字降级：两位数的加减法
     *
     * @return array{answer: string, prompt: string}
     */
    protected static function issueArithmetic(): array
    {
        $left = random_int(2, 9);
        $right = random_int(2, 9);

        if (random_int(0, 1) === 0) {
            $answer = (string) ($left + $right);
            $prompt = "{$left} + {$right} = ?";
        } else {
            $max = max($left, $right);
            $min = min($left, $right);
            $answer = (string) ($max - $min);
            $prompt = "{$max} - {$min} = ?";
        }

        self::store($answer);

        return ['answer' => $answer, 'prompt' => $prompt];
    }

    /**
     * 写入会话（统一大写，便于忽略大小写比对）
     */
    protected static function store(string $answer): void
    {
        session([
            self::SESSION_KEY => [
                'answer' => mb_strtoupper($answer),
                'expires_at' => time() + (int) config('admin.captcha_ttl', 300),
            ],
        ]);
    }
}
