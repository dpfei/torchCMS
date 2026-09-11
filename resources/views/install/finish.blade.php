@extends('install.layout')

@section('title', '安装完成')

@section('content')
    @if ($instant)
        <div class="hero">
            <div class="hero-icon">✓</div>
            <h2>安装完成</h2>
            <p>站点已初始化，现在可以进入后台发布内容了。</p>
        </div>

        @if (! empty($logs))
            <ul class="logs">
                @foreach ($logs as $log)
                    <li>{{ $log }}</li>
                @endforeach
            </ul>
        @endif

        <div class="result">
            <div><span>后台地址</span><a href="{{ $adminUrl }}">{{ $adminUrl }}</a></div>
            <div><span>管理员账号</span><strong>{{ $meta['admin_email'] ?? '—' }}</strong></div>
            <div><span>前台地址</span><a href="{{ $homeUrl }}">{{ $homeUrl }}</a></div>
        </div>

        <div class="alert warn">
            安装入口已自动关闭。如需重新安装，删除 <code>storage/installed.lock</code> 后访问 <code>/install</code>。
            正式部署请确认 <code>APP_DEBUG=false</code>，并为 <code>storage</code>、<code>bootstrap/cache</code> 设置正确权限。
        </div>

        <div class="actions">
            <a class="btn" href="{{ $adminUrl }}">进入后台</a>
            <a class="btn ghost" href="{{ $homeUrl }}">查看前台</a>
        </div>
    @else
        <h2 class="card-title">系统已安装</h2>
        <p class="card-desc">安装向导已关闭。如需重新安装，请先删除 <code>storage/installed.lock</code> 文件。</p>

        <div class="actions">
            <a class="btn" href="{{ $adminUrl }}">进入后台</a>
            <a class="btn ghost" href="{{ $homeUrl }}">返回前台</a>
        </div>
    @endif
@endsection
