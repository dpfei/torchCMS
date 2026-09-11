## torchCMS

基于 Laravel 13 + Filament 5 的内容管理系统，包含前台展示站与后台管理面板。

- 后台：`Filament 5` 管理面板（`/admin`）
- 前台：`Blade + Tailwind CSS 4 + Vite`，支持 URL 伪静态与列表数据缓存
- 权限：`spatie/laravel-permission`，内置超级管理员 / 内容编辑 / 只读三档角色
- 安装：内置网页安装向导（`/install`），也可用命令行部署

---

### 环境要求

| 依赖 | 版本 |
| --- | --- |
| PHP | >= 8.3 |
| Composer | 2.x |
| Node.js | >= 18 |
| 数据库 | MySQL 5.7+ / MariaDB / PostgreSQL / SQLite |

必需的 PHP 扩展：`ctype`、`curl`、`dom`、`fileinfo`、`json`、`mbstring`、`openssl`、`pdo`、`tokenizer`、`xml`，以及至少一个 `pdo_mysql` / `pdo_sqlite` / `pdo_pgsql` 驱动。

建议启用：`gd`、`intl`、`zip`、`bcmath`。

---

### 快速开始

先拉取依赖并构建前端资源：

```bash
composer install
npm install
npm run build
```

#### 方式一：网页安装向导（推荐）

```bash
php artisan serve
```

浏览器打开 `http://127.0.0.1:8000/install`。安装向导不依赖编译后的前端资源，未执行 `npm run build` 也能正常显示。

向导共四步：

1. **环境检测**：检查 PHP 版本、扩展、目录权限与 `.env` 可写性（必需项未通过时无法继续）
2. **数据库**：填写连接信息，点击「测试连接并继续」，通过后自动写入 `.env`，可勾选「数据库不存在时自动创建」（仅 MySQL）
3. **站点信息**：站点名称 / 简介 / 地址，以及后台超级管理员账号密码，可勾选生产模式与导入演示内容
4. **完成**：展示后台与前台入口

安装向导会自动完成：生成 `APP_KEY` → 写入数据库配置 → 迁移数据表 → 初始化权限、角色与系统设置 → 创建超级管理员 → 创建 `public/storage` 软链接。

安装完成后会把入口自动关闭（访问 `/install` 会跳转到后台）。

**重新安装**：仅删除 `storage/installed.lock` **不会**重新进入安装向导。锁文件缺失时 `Installer::isInstalled()` 会回落到探测数据库，只要 `admins`、`settings` 表仍存在且至少有一个管理员，就判定为已安装并自动重建锁文件，`/install` 依旧被重定向到 `/admin`（该逻辑用于避免老部署升级后被误导向安装向导）。真正重装需要先让探测失败：

```bash
php artisan db:wipe --force   # 删除数据库中的全部表，不可恢复
rm -f storage/installed.lock
php artisan optimize:clear
```

再访问 `/install`。SQLite 部署则删除 `database/database.sqlite` 后重新创建空文件。

#### 方式二：命令行部署

```bash
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

编辑 `.env` 配置数据库（示例）：

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=torchcms
DB_USERNAME=root
DB_PASSWORD=
```

```bash
php artisan migrate --seed                    # 建表 + 初始化权限/角色/设置/默认管理员
php artisan db:seed --class=DemoContentSeeder # 可选：演示栏目与内容
php artisan storage:link                      # 后台图片上传依赖
```

---

### 访问入口

| 入口 | 地址 | 说明 |
| --- | --- | --- |
| 前台首页 | `/` | 站点首页 |
| 后台登录 | `/admin/login` | 未登录访问 `/admin` 会自动跳转 |
| 安装向导 | `/install` | 仅在未安装时可访问 |
| 健康检查 | `/up` | 返回服务状态 |

命令行部署的默认管理员账号：

```
邮箱：admin@torchcms.com
密码：password
```

首次登录后请立即修改密码（后台「管理员」菜单）。

---

### 前台路由

| 路由 | 说明 |
| --- | --- |
| `/` | 首页：导航、推荐内容列表 |
| `/category/{slug}` | 栏目页 |
| `/news/{slug}` | 内容详情页 |

内容与栏目均支持 slug 伪静态：后台填写标题时自动生成别名（中文自动转拼音），也可手动修改。链接同时兼容数字 ID（`/news/12`），旧链接不会失效。

---

### 后台功能

| 模块 | 说明 |
| --- | --- |
| 仪表盘 | 后台首页概览 |
| 栏目 | 多级栏目、排序、是否显示在导航、栏目描述 |
| 内容 | 标题、slug、所属栏目、封面、正文、关键词/描述、浏览量、软删除 |
| 媒体 | 图片等素材库，支持上传与选择 |
| 角色 | 角色与权限分配（`view/create/update/delete` + 资源维度） |
| 管理员 | 后台账号管理、角色分配 |
| 系统设置 | 站点名称、Logo、关键词、描述、联系方式、备案号、版权 |

内置角色：

- `super_admin`：全部权限（`Gate::before` 直接放行）
- `editor`：栏目与内容的新增/编辑、媒体管理，无删除与系统设置权限
- `viewer`：所有 `view_*` 只读权限

---

### 缓存说明

前台列表数据通过 `App\Support\ContentCache` 缓存，默认 TTL 600 秒。缓存键带统一的「内容版本号」，内容增删改时只需递增版本号即可让旧缓存整体失效，无需遍历删除。

手动清理：

```bash
php artisan cache:clear   # 清空缓存（同时重置内容版本号）
php artisan view:clear    # 清理编译后的视图
php artisan config:clear  # 清理配置缓存
```

`.env` 默认 `CACHE_STORE=database`、`SESSION_DRIVER=database`、`QUEUE_CONNECTION=database`，对应数据表由迁移创建。

---

### 目录结构

```
app/
├── Filament/              后台资源、页面与组件
│   ├── Pages/             Dashboard、系统设置
│   ├── Resources/         栏目、内容、媒体、角色、管理员
│   └── Widgets/
├── Http/
│   ├── Controllers/       前台控制器与安装控制器
│   └── Middleware/        安装状态拦截（RedirectIfNotInstalled）
├── Models/                News、Category、Media、Admin、Role、Setting...
├── Support/               EnvWriter、Installer、SlugGenerator、ContentCache
└── Traits/                HasSlugTrait

resources/views/
├── install/               安装向导视图（独立内联样式）
├── layouts/ partials/      前台布局与片段
└── *.blade.php            首页 / 栏目页 / 内容页

routes/web.php             安装路由 + 前台路由
```

---

### 常用命令

| 命令 | 说明 |
| --- | --- |
| `php artisan serve` | 启动本地开发服务 |
| `npm run dev` | 前端资源热更新 |
| `npm run build` | 构建生产前端资源 |
| `php artisan migrate --seed` | 迁移并执行种子数据 |
| `php artisan db:seed --class=RolePermissionSeeder` | 仅重建权限与角色 |
| `php artisan db:seed --class=DemoContentSeeder` | 导入演示内容 |
| `php artisan storage:link` | 创建上传目录软链接 |
| `php artisan cache:clear` | 清理应用缓存 |

---

### 生产部署要点

1. `.env` 中设置 `APP_ENV=production`、`APP_DEBUG=false`，并正确填写 `APP_URL`
2. 确保 `storage`、`bootstrap/cache` 可写（Linux：`chmod -R 775 storage bootstrap/cache`）
3. 执行 `php artisan storage:link`，否则后台上传的图片无法访问
4. 使用 `npm run build` 生成静态资源（`public/build`），线上无需 Node 环境
5. 配置 Web 服务器重写规则（Laravel 标准 `public` 目录指向）
6. 若使用队列（邮件、异步任务），`QUEUE_CONNECTION=database` 时需常驻 `php artisan queue:work`

---

### 常见问题

**访问任何页面都被跳转到 `/install`**
系统未检测到安装信息。若确认数据库已初始化，检查 `.env` 的数据库配置是否正确；锁文件缺失时会自动探测数据库中的管理员表并补写 `storage/installed.lock`。

**后台登录后又被弹回登录页，且看不到任何错误提示**

这是「登录表单没有真正走 Livewire」或「Livewire 请求被拒绝」的典型表现，绝大多数情况不是密码错误（密码错 Filament 会明确提示）。按下面顺序排查：

1. `APP_URL` 必须与浏览器实际访问的协议、域名、端口完全一致——装完后换过域名、加了 HTTPS 最容易踩到。改完执行 `php artisan optimize:clear`
2. 若站点由 Nginx 终止 HTTPS，需要把协议透传给 PHP：`fastcgi_param HTTPS on;` 与 `fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;`。否则页面会按 `http` 去加载 Filament/Livewire 资源，被浏览器当作混合内容拦掉，表单会退化成普通提交，一刷新就回到登录页
3. 打开浏览器开发者工具提交一次登录：**没有** `livewire/update` 请求说明前端资源没加载（看 Console 红色报错）；状态码 `419` 说明会话没保持住；`500` 则查 `storage/logs/laravel.log`
4. 确认会话确实写入了：`php artisan tinker --execute="echo DB::table('sessions')->count();"`，登录一次后该数字应当增加
5. 同一域名下并存多个 Laravel 应用时，为本站设置独立的 `SESSION_COOKIE`，避免共用 `laravel-session` 互相覆盖
6. 临时把 `.env` 的 `APP_DEBUG` 改为 `true` 再试一次，可直接看到被隐藏的真实异常

**上传的图片显示 404**
未创建软链接，执行 `php artisan storage:link`。

**前台样式错乱**
未构建前端资源，执行 `npm run build`（或 `npm run dev`）。

**想重新安装**

只删 `storage/installed.lock` 不起作用：锁文件缺失时 `Installer::isInstalled()` 会回落到探测数据库，只要 `admins` 与 `settings` 表存在、且至少有一条管理员记录，就判定为「已安装」并自动补写锁文件，`/install` 于是又被重定向到 `/admin`。必须先让这个探测失败：

```bash
php artisan db:wipe --force
rm -f storage/installed.lock
php artisan optimize:clear
```

SQLite 部署则删除 `database/database.sqlite` 并重建空文件。**注意这会清空全部数据。**

如果只是想恢复后台访问，不需要重装，直接重置管理员密码即可：

```bash
php artisan tinker --execute="\$a=App\Models\Admin::first();\$a->password='新密码';\$a->save();echo \$a->email;"
```
