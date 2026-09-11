{{-- 登录验证码：优先图片，服务器无 GD 时显示算术题 --}}
@if ($image)
    <img src="{{ route('captcha.admin-login', ['t' => uniqid()]) }}"
         alt="验证码"
         title="看不清？点击刷新"
         style="height:2.25rem;width:auto;cursor:pointer;border-radius:.375rem;border:1px solid rgba(0,0,0,.12);vertical-align:middle;"
         onclick="this.src='{{ route('captcha.admin-login') }}?t=' + Date.now()">
@else
    <span style="font-weight:600;font-size:.95rem;letter-spacing:.08em;vertical-align:middle;">{{ $prompt }}</span>
@endif
