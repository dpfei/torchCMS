@extends('install.layout')

@section('title', '站点信息')

@section('content')
    <h2 class="card-title">站点与管理员</h2>
    <p class="card-desc">站点信息会写入系统设置，管理员账号用于登录后台，请妥善保管。</p>

    @if ($databaseMessage)
        <div class="alert success">数据库连接成功：{{ $databaseMessage }}</div>
    @endif

    @error('install')
        <div class="alert error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('install.site.store') }}">
        @csrf

        <div class="field">
            <label for="site_name">站点名称</label>
            <input type="text" name="site_name" id="site_name" value="{{ old('site_name', $values['site_name']) }}" required>
            @error('site_name') <p class="hint" style="color:#b91c1c">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="site_description">站点简介</label>
            <input type="text" name="site_description" id="site_description" value="{{ old('site_description', $values['site_description']) }}">
        </div>

        <div class="field">
            <label for="site_url">站点地址</label>
            <input type="text" name="site_url" id="site_url" value="{{ old('site_url', $values['site_url']) }}" placeholder="https://example.com">
            <p class="hint">将写入 .env 的 APP_URL，用于生成图片等资源的绝对地址。</p>
            @error('site_url') <p class="hint" style="color:#b91c1c">{{ $message }}</p> @enderror
        </div>

        <div class="field">
            <label for="admin_name">管理员姓名</label>
            <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name', $values['admin_name']) }}" required>
        </div>

        <div class="field">
            <label for="admin_email">管理员邮箱</label>
            <input type="text" name="admin_email" id="admin_email" value="{{ old('admin_email', $values['admin_email']) }}" required>
            @error('admin_email') <p class="hint" style="color:#b91c1c">{{ $message }}</p> @enderror
        </div>

        <div class="grid">
            <div class="field">
                <label for="admin_password">管理员密码</label>
                <div class="input-affix">
                    <input type="password" name="admin_password" id="admin_password" value="{{ old('admin_password', $values['admin_password']) }}" required autocomplete="new-password">
                    <button type="button" class="reveal" data-reveal="admin_password">显示</button>
                </div>
                <p class="hint">已自动生成强密码，可直接使用，也可以改成自己的。</p>
                @error('admin_password') <p class="hint" style="color:#b91c1c">{{ $message }}</p> @enderror
            </div>
            <div class="field">
                <label for="admin_password_confirmation">确认密码</label>
                <div class="input-affix">
                    <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" value="{{ old('admin_password_confirmation', $values['admin_password']) }}" required autocomplete="new-password">
                    <button type="button" class="reveal" data-reveal="admin_password_confirmation">显示</button>
                </div>
            </div>
        </div>

        <div class="secret">
            <div>
                <span class="label">本次安装使用的管理员密码 · 提交后无法再次查看，请先保存或复制</span>
                <code id="admin_password_plain">{{ old('admin_password', $values['admin_password']) }}</code>
            </div>
            <button type="button" class="copy" data-copy-from="admin_password_plain">复制</button>
        </div>

        <label class="switch">
            <input type="checkbox" name="production" value="1" @checked(old('production', true))>
            <span>生产模式（APP_ENV=production 且关闭调试输出）</span>
        </label>

        <label class="switch">
            <input type="checkbox" name="demo_content" value="1" @checked(old('demo_content'))>
            <span>导入演示栏目与内容，方便预览前台效果</span>
        </label>

        <div class="actions">
            <a class="btn ghost" href="{{ route('install.database') }}">上一步</a>
            <button type="submit" class="btn">开始安装</button>
        </div>
    </form>
@endsection
