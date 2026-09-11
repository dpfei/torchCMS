@extends('install.layout')

@section('title', '环境检测')

@section('content')
    <h2 class="card-title">运行环境检测</h2>
    <p class="card-desc">标记为「必须」的项目需全部通过才能继续；标记为「建议」的项目缺失时不影响安装，但可能影响部分功能。</p>

    @php($groups = collect($checks)->groupBy('group'))

    @foreach ($groups as $group => $items)
        <section class="group">
            <h3>{{ $group }}</h3>
            <ul class="check-list">
                @foreach ($items as $check)
                    <li class="{{ $check['passed'] ? 'ok' : ($check['level'] === 'optional' ? 'warn' : 'fail') }}">
                        <span class="name">{{ $check['label'] }}</span>
                        <span class="value">{{ $check['current'] }}</span>
                        <span class="req">要求：{{ $check['required'] }}</span>
                        <span class="badge">{{ $check['passed'] ? '通过' : ($check['level'] === 'optional' ? '建议启用' : '未通过') }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach

    @unless ($passed)
        <div class="alert error">存在未通过的必需项，请修复后重新检测。若为目录权限问题，Linux 下可执行 <code>chmod -R 775 storage bootstrap/cache</code>。</div>
    @endunless

    <div class="actions">
        <a class="btn ghost" href="{{ route('install.environment') }}">重新检测</a>
        @if ($passed)
            <a class="btn" href="{{ route('install.database') }}">下一步：配置数据库</a>
        @else
            <span class="btn disabled">请先解决未通过项</span>
        @endif
    </div>
@endsection
