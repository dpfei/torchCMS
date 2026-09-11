<?php

namespace App\Support;

/**
 * 以最小侵入的方式读写 .env 文件：只覆盖指定键，保留原有注释与顺序。
 */
class EnvWriter
{
    public function __construct(protected string $path)
    {
    }

    public static function forApplication(): self
    {
        return new self(base_path('.env'));
    }

    public function path(): string
    {
        return $this->path;
    }

    public function exists(): bool
    {
        return is_file($this->path);
    }

    public function isWritable(): bool
    {
        return $this->exists()
            ? is_writable($this->path)
            : is_writable(dirname($this->path));
    }

    /**
     * 不存在时从示例文件复制一份
     */
    public function ensureExists(string $example = '.env.example'): bool
    {
        if ($this->exists()) {
            return true;
        }

        $source = base_path($example);

        if (! is_file($source)) {
            return false;
        }

        return copy($source, $this->path);
    }

    /**
     * 读取单个配置项（自动去除包裹的引号）
     */
    public function get(string $key, ?string $default = null): ?string
    {
        if (! $this->exists()) {
            return $default;
        }

        foreach ($this->lines() as $line) {
            if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=\s*(.*)$/', $line, $matches)) {
                $value = $this->stripQuotes(trim($matches[1]));

                return $value === '' ? $default : $value;
            }
        }

        return $default;
    }

    /**
     * 批量写入配置项
     *
     * @param  array<string, string|int|float|bool|null>  $values
     */
    public function set(array $values): void
    {
        $lines = $this->exists() ? $this->lines() : [];
        $handled = [];

        foreach ($lines as $index => $line) {
            foreach ($values as $key => $value) {
                if (in_array($key, $handled, true)) {
                    continue;
                }

                if (preg_match('/^\s*'.preg_quote($key, '/').'\s*=/', $line)) {
                    $lines[$index] = $key.'='.$this->formatValue($value);
                    $handled[] = $key;
                    continue 2;
                }
            }
        }

        foreach ($values as $key => $value) {
            if (! in_array($key, $handled, true)) {
                $lines[] = $key.'='.$this->formatValue($value);
            }
        }

        $this->write(implode(PHP_EOL, $lines));
    }

    /**
     * @return array<int, string>
     */
    protected function lines(): array
    {
        $content = (string) @file_get_contents($this->path);

        return preg_split('/\r\n|\r|\n/', $content) ?: [];
    }

    protected function write(string $content): void
    {
        file_put_contents($this->path, rtrim($content, PHP_EOL).PHP_EOL);
    }

    protected function stripQuotes(string $value): string
    {
        if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'") && str_ends_with($value, $value[0])) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    protected function formatValue(string|int|float|bool|null $value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;

        if ($value === '') {
            return '';
        }

        // 无需引号即可安全解析的简单值
        if (! preg_match('/[\s#\'"$\\\\]/', $value)) {
            return $value;
        }

        // Dotenv 只会对双引号（或裸值）做变量插值，单引号内的 $ 是字面量。
        // 因此优先用单引号包裹，避免密码里的 $ 被当成变量而丢失。
        if (str_contains($value, "'")) {
            return '"'.str_replace(['\\', '"', '$'], ['\\\\', '\\"', '\\$'], $value).'"';
        }

        return "'".$value."'";
    }
}
