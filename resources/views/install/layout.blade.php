<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', '安装向导') · torchCMS</title>
    <style>
        :root {
            --amber: #f59e0b;
            --amber-dark: #b45309;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 44px 16px 56px;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "PingFang SC", "Microsoft YaHei", sans-serif;
            font-size: 14px;
            color: var(--ink);
            background: radial-gradient(circle at 15% 0%, #fef3c7 0%, #f8fafc 45%, #eef2f7 100%);
            display: flex;
            justify-content: center;
        }

        .page { width: 100%; max-width: 780px; }

        .brand { display: flex; align-items: center; gap: 14px; margin-bottom: 22px; }
        .brand .mark {
            width: 46px; height: 46px; border-radius: 14px; flex: none;
            display: grid; place-items: center;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff; font-size: 22px; font-weight: 700;
            box-shadow: 0 10px 22px -10px rgba(217, 119, 6, .8);
        }
        .brand h1 { margin: 0; font-size: 19px; font-weight: 700; }
        .brand p { margin: 3px 0 0; font-size: 13px; color: var(--muted); }

        .steps { display: flex; gap: 8px; list-style: none; margin: 0 0 20px; padding: 0; }
        .steps li { flex: 1; position: relative; display: flex; flex-direction: column; align-items: center; gap: 8px; }
        .steps li::before,
        .steps li::after { content: ''; position: absolute; top: 15px; height: 2px; background: var(--line); z-index: 0; }
        .steps li::before { left: 0; right: 50%; }
        .steps li::after { left: 50%; right: 0; }
        .steps li:first-child::before,
        .steps li:last-child::after { display: none; }
        .steps li.done::before,
        .steps li.done::after { background: #fcd34d; }
        .steps .dot {
            position: relative; z-index: 1;
            width: 32px; height: 32px; border-radius: 50%;
            display: grid; place-items: center;
            background: #fff; border: 2px solid var(--line);
            color: var(--muted); font-size: 13px; font-weight: 700;
        }
        .steps li.active .dot { border-color: var(--amber); background: #fffbeb; color: var(--amber-dark); box-shadow: 0 0 0 4px rgba(245, 158, 11, .15); }
        .steps li.done .dot { border-color: var(--amber); background: var(--amber); color: #fff; }
        .steps .text { font-size: 12px; color: var(--muted); }
        .steps li.active .text,
        .steps li.done .text { color: var(--ink); font-weight: 600; }

        .card {
            background: #fff; border-radius: 18px; padding: 26px 28px;
            border: 1px solid rgba(229, 231, 235, .9);
            box-shadow: 0 24px 48px -30px rgba(15, 23, 42, .35);
        }

        .card-title { margin: 0 0 6px; font-size: 17px; font-weight: 700; }
        .card-desc { margin: 0 0 20px; font-size: 13px; color: var(--muted); line-height: 1.6; }

        .group { margin-bottom: 18px; }
        .group h3 { margin: 0 0 8px; font-size: 12px; font-weight: 700; color: var(--muted); letter-spacing: .06em; }

        .check-list { list-style: none; margin: 0; padding: 0; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
        .check-list li {
            display: grid; grid-template-columns: 1fr 1.1fr 1fr 62px;
            align-items: center; gap: 10px;
            padding: 10px 14px; font-size: 13px;
            border-bottom: 1px solid var(--line); background: #fff;
        }
        .check-list li:last-child { border-bottom: none; }
        .check-list .name { font-weight: 600; }
        .check-list .value,
        .check-list .req { color: var(--muted); }
        .check-list li.fail .value { color: #b91c1c; }
        .badge { justify-self: end; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: #ecfdf5; color: #047857; white-space: nowrap; }
        li.fail .badge { background: #fef2f2; color: #b91c1c; }
        li.warn .badge { background: #fffbeb; color: var(--amber-dark); }

        .alert { padding: 12px 14px; border-radius: 10px; font-size: 13px; line-height: 1.6; margin-bottom: 18px; word-break: break-word; }
        .alert.error { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert.info { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .alert.success { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
        .alert.warn { background: #fffbeb; color: var(--amber-dark); border: 1px solid #fde68a; }

        .field { margin-bottom: 16px; }
        .field label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
        .field input,
        .field select {
            width: 100%; padding: 11px 13px; font-size: 14px; font-family: inherit;
            color: var(--ink); background: #fff;
            border: 1px solid var(--line); border-radius: 10px; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field input:focus,
        .field select:focus { border-color: var(--amber); box-shadow: 0 0 0 3px rgba(245, 158, 11, .16); }
        .field .hint { margin: 5px 0 0; font-size: 12px; color: var(--muted); }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; }

        /* 密码框内的「显示 / 隐藏」切换按钮 */
        .input-affix { position: relative; }
        .input-affix input { padding-right: 58px; }
        .input-affix .reveal {
            position: absolute; top: 50%; right: 6px; transform: translateY(-50%);
            padding: 5px 10px; border: none; border-radius: 8px;
            background: transparent; color: var(--muted);
            font-family: inherit; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: background .15s, color .15s;
        }
        .input-affix .reveal:hover { background: #f3f4f6; color: var(--ink); }

        /* 明文展示的初始密码，附带一键复制 */
        .secret {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 14px; margin: 0 0 18px;
            background: #fffbeb; border: 1px dashed #fcd34d; border-radius: 12px;
        }
        .secret .label { display: block; margin-bottom: 4px; font-size: 12px; color: var(--amber-dark); }
        .secret code {
            display: block; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            font-size: 15px; font-weight: 700; letter-spacing: .04em; color: var(--ink);
            word-break: break-all;
        }
        .secret .copy {
            flex: none; padding: 8px 16px; border: 1px solid #fcd34d; border-radius: 9px;
            background: #fff; color: var(--amber-dark);
            font-family: inherit; font-size: 12px; font-weight: 600;
            cursor: pointer; transition: background .15s, border-color .15s, color .15s;
        }
        .secret .copy:hover { background: #fef3c7; }
        .secret .copy.done { background: #ecfdf5; border-color: #a7f3d0; color: #047857; }

        .switch { display: flex; align-items: center; gap: 9px; margin: 4px 0 18px; font-size: 13px; color: #374151; cursor: pointer; }
        .switch input { width: 16px; height: 16px; accent-color: var(--amber); }

        .actions { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; margin-top: 22px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 24px; border: none; border-radius: 12px;
            font-family: inherit; font-size: 14px; font-weight: 600; text-decoration: none;
            color: #fff; background: linear-gradient(135deg, #f59e0b, #d97706);
            box-shadow: 0 12px 22px -12px rgba(217, 119, 6, .9);
            cursor: pointer; transition: transform .15s, box-shadow .15s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn.ghost { background: #fff; color: var(--ink); border: 1px solid var(--line); box-shadow: none; }
        .btn.disabled { background: #e5e7eb; color: #9ca3af; box-shadow: none; cursor: not-allowed; }

        .hero { text-align: center; padding: 6px 0 4px; }
        .hero-icon {
            width: 62px; height: 62px; margin: 0 auto 14px; border-radius: 50%;
            display: grid; place-items: center; font-size: 30px; color: #fff;
            background: linear-gradient(135deg, #34d399, #059669);
            box-shadow: 0 14px 28px -14px rgba(5, 150, 105, .9);
        }
        .hero h2 { margin: 0 0 6px; font-size: 20px; }
        .hero p { margin: 0; font-size: 13px; color: var(--muted); }

        .logs { list-style: none; margin: 20px 0; padding: 0; font-size: 13px; }
        .logs li { padding: 7px 12px; border-radius: 8px; background: #f9fafb; margin-bottom: 6px; color: #374151; }
        .logs li::before { content: '✓'; color: #059669; font-weight: 700; margin-right: 8px; }

        .result { border: 1px solid var(--line); border-radius: 12px; overflow: hidden; margin-bottom: 18px; }
        .result div { display: flex; justify-content: space-between; gap: 12px; padding: 11px 14px; font-size: 13px; border-bottom: 1px solid var(--line); }
        .result div:last-child { border-bottom: none; }
        .result span { color: var(--muted); }
        .result a { color: var(--amber-dark); text-decoration: none; font-weight: 600; }
        .result a:hover { text-decoration: underline; }

        .foot { margin-top: 18px; text-align: center; font-size: 12px; color: #9ca3af; }

        @media (max-width: 640px) {
            .card { padding: 20px 18px; }
            .grid { grid-template-columns: 1fr; }
            .check-list li { grid-template-columns: 1fr auto; }
            .check-list .req { display: none; }
            .steps .text { font-size: 11px; }
        }
    </style>
</head>
<body>
<div class="page">
    <header class="brand">
        <div class="mark">T</div>
        <div>
            <h1>torchCMS 安装向导</h1>
            <p>@yield('subtitle', '四步完成环境检测、数据库配置与站点初始化')</p>
        </div>
    </header>

    @php($steps = [1 => '环境检测', 2 => '数据库', 3 => '站点信息', 4 => '完成'])

    <ol class="steps">
        @foreach ($steps as $index => $label)
            <li class="{{ $step > $index ? 'done' : ($step === $index ? 'active' : '') }}">
                <span class="dot">{{ $step > $index ? '✓' : $index }}</span>
                <span class="text">{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    <main class="card">
        @yield('content')
    </main>

    <p class="foot">torchCMS · 基于 Laravel 与 Filament 的内容管理系统</p>
</div>

<script>
    (function () {
        function fallbackCopy(text, done) {
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.top = '-1000px';
            document.body.appendChild(area);
            area.select();

            try { document.execCommand('copy'); done(); } catch (error) { /* 复制失败时保持原样，用户仍可手动选中 */ }

            document.body.removeChild(area);
        }

        // 非 HTTPS（如通过内网 IP 访问）下 navigator.clipboard 不可用，需要回退
        function copyText(text, done) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(done, function () { fallbackCopy(text, done); });

                return;
            }

            fallbackCopy(text, done);
        }

        // 切换密码可见性
        document.querySelectorAll('[data-reveal]').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.dataset.reveal);

                if (!input) {
                    return;
                }

                var showing = input.type === 'text';
                input.type = showing ? 'password' : 'text';
                button.textContent = showing ? '显示' : '隐藏';
            });
        });

        // 明文块与密码框实时同步，保证复制到的就是即将提交的密码
        var passwordInput = document.getElementById('admin_password');
        var passwordPlain = document.getElementById('admin_password_plain');

        if (passwordInput && passwordPlain) {
            var sync = function () { passwordPlain.textContent = passwordInput.value; };

            passwordInput.addEventListener('input', sync);
            sync();
        }

        document.querySelectorAll('[data-copy-from]').forEach(function (button) {
            button.addEventListener('click', function () {
                var source = document.getElementById(button.dataset.copyFrom);
                var text = source ? source.textContent.trim() : '';

                if (text === '') {
                    return;
                }

                copyText(text, function () {
                    var original = button.textContent;

                    button.textContent = '已复制';
                    button.classList.add('done');

                    setTimeout(function () {
                        button.textContent = original;
                        button.classList.remove('done');
                    }, 1800);
                });
            });
        });
    })();
</script>
</body>
</html>
