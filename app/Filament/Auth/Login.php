<?php

namespace App\Filament\Auth;

use App\Support\Captcha;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;

/**
 * 后台登录页：在 Filament 默认表单之上增加验证码。
 */
class Login extends BaseLogin
{
    /**
     * 文字降级模式下的算术题题干（图片模式为 null）。
     *
     * 锁定的原因是它只能在服务端出题时被改写，不接受客户端传值。
     */
    #[Locked]
    public ?string $captchaPrompt = null;

    public function mount(): void
    {
        parent::mount();

        if ($this->isCaptchaEnabled() && ! Captcha::supportsImage()) {
            $this->captchaPrompt = Captcha::issue()['prompt'];
        }
    }

    public function authenticate(): ?LoginResponse
    {
        if ($this->isCaptchaEnabled() && ! Captcha::verify($this->data['captcha'] ?? null)) {
            $this->refreshCaptcha();

            throw ValidationException::withMessages([
                'data.captcha' => __('验证码不正确或已过期，请重新输入。'),
            ]);
        }

        try {
            return parent::authenticate();
        } catch (ValidationException $exception) {
            // 密码错误或触发限流时也换一道新题，避免拿已作废的验证码反复提交
            $this->refreshCaptcha();

            throw $exception;
        }
    }

    public function form(Schema $schema): Schema
    {
        $components = [
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
        ];

        if ($this->isCaptchaEnabled()) {
            $components[] = $this->getCaptchaFormComponent();
        }

        $components[] = $this->getRememberFormComponent();

        return $schema->components($components);
    }

    protected function getCaptchaFormComponent(): Component
    {
        return TextInput::make('captcha')
            ->label(__('验证码'))
            ->required()
            ->autocomplete('off')
            ->hint(fn (): HtmlString => new HtmlString(
                view('filament.captcha', [
                    'image' => Captcha::supportsImage(),
                    'prompt' => $this->captchaPrompt,
                ])->render()
            ));
    }

    protected function isCaptchaEnabled(): bool
    {
        return (bool) config('admin.login_captcha', true);
    }

    /**
     * 重新出题：图片模式靠渲染时的随机参数自然刷新，这里只处理文字模式
     */
    protected function refreshCaptcha(): void
    {
        $this->data['captcha'] = '';

        if (! $this->isCaptchaEnabled() || Captcha::supportsImage()) {
            return;
        }

        $this->captchaPrompt = Captcha::issue()['prompt'];
    }
}
