{{-- 登录验证码：优先图片，服务器无 GD 时显示算术题。渲染在输入框右侧，需与输入框等高对齐 --}}
@if ($image)
    <img src="{{ route('captcha.admin-login', ['t' => uniqid()]) }}"
         alt="验证码"
         title="看不清？点击刷新"
         style="height:2.5rem;width:auto;flex:none;margin-left:.5rem;cursor:pointer;border-radius:.5rem;border:1px solid rgba(0,0,0,.12);"
         onclick="this.src='{{ route('captcha.admin-login') }}?t=' + Date.now()">
@else
    <span style="display:inline-flex;align-items:center;height:2.5rem;margin-left:.75rem;font-weight:600;font-size:.95rem;letter-spacing:.08em;white-space:nowrap;">{{ $prompt }}</span>
@endif
