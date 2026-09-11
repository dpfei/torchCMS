@extends('install.layout')

@section('title', '数据库配置')

@section('content')
    <h2 class="card-title">数据库连接</h2>
    <p class="card-desc">填写数据库信息，系统会先测试连通性，通过后写入 <code>.env</code> 配置文件。</p>

    @error('database')
        <div class="alert error">{{ $message }}</div>
    @enderror

    <form method="POST" action="{{ route('install.database.store') }}">
        @csrf

        <div class="field">
            <label for="driver">数据库类型</label>
            <select name="driver" id="driver">
                @foreach (['mysql' => 'MySQL / MariaDB', 'pgsql' => 'PostgreSQL', 'sqlite' => 'SQLite'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('driver', $values['driver']) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="grid" data-connection-field>
            <div class="field">
                <label for="host">数据库地址</label>
                <input type="text" name="host" id="host" value="{{ old('host', $values['host']) }}" placeholder="127.0.0.1" autocomplete="off">
            </div>
            <div class="field">
                <label for="port">端口</label>
                <input type="text" name="port" id="port" value="{{ old('port', $values['port']) }}" placeholder="3306" autocomplete="off">
            </div>
            <div class="field">
                <label for="username">账号</label>
                <input type="text" name="username" id="username" value="{{ old('username', $values['username']) }}" autocomplete="off">
            </div>
            <div class="field">
                <label for="password">密码</label>
                <input type="password" name="password" id="password" value="{{ old('password', $values['password']) }}" autocomplete="new-password">
            </div>
        </div>

        <div class="field">
            <label for="database" data-database-label>数据库名</label>
            <input type="text" name="database" id="database" value="{{ old('database', $values['database']) }}" required>
            <p class="hint" data-database-hint>数据库需已存在，或在下方勾选自动创建（仅 MySQL）。</p>
        </div>

        <label class="switch" data-connection-field>
            <input type="checkbox" name="create_database" value="1" @checked(old('create_database'))>
            <span>数据库不存在时自动创建（仅 MySQL）</span>
        </label>

        <div class="actions">
            <a class="btn ghost" href="{{ route('install.environment') }}">上一步</a>
            <button type="submit" class="btn">测试连接并继续</button>
        </div>
    </form>

    <script>
        (function () {
            var driver = document.getElementById('driver');
            var connectionFields = document.querySelectorAll('[data-connection-field]');
            var databaseLabel = document.querySelector('[data-database-label]');
            var hint = document.querySelector('[data-database-hint]');
            var port = document.getElementById('port');
            var defaults = { mysql: '3306', pgsql: '5432' };

            function sync() {
                var isSqlite = driver.value === 'sqlite';

                connectionFields.forEach(function (element) {
                    element.style.display = isSqlite ? 'none' : '';
                });

                databaseLabel.textContent = isSqlite ? 'SQLite 文件路径' : '数据库名';
                hint.textContent = isSqlite
                    ? '留空或填写相对路径，将保存到 database/database.sqlite。'
                    : '数据库需已存在，或在下方勾选自动创建（仅 MySQL）。';

                if (! isSqlite && ['', '3306', '5432'].indexOf(port.value) !== -1) {
                    port.value = defaults[driver.value] || '3306';
                }
            }

            driver.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
