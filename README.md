## torchCMS

基于 Laravel 13 + Filament 5 的内容管理系统，包含前台展示站与后台管理面板。

- 后台：`Filament 5` 管理面板（`/admin`）
- 前台：`Blade + Tailwind CSS 4 + Vite`，支持 URL 伪静态与列表数据缓存
- 权限：`spatie/laravel-permission`，内置超级管理员 / 内容编辑 / 只读三档角色
- 安装：内置网页安装向导（`/install`），也可用命令行部署
- 安全：管理员密码带应用级盐值，后台登录自带验证码
- 语言：默认简体中文，后台界面（Filament）与前台文案共用 `.env` 的 `APP_LOCALE` 配置

---

### 环境要求

| 依赖 | 版本 |
| --- | --- |
| PHP | >= 8.3 |
| Composer | 2.x |
| Node.js | >= 18 |
| 数据库 | MySQL 5.7+ / MariaDB / PostgreSQL / SQLite |

必需的 PHP 扩展：`ctype`、`curl`、`dom`、`fileinfo`、`json`、`mbstring`、`openssl`、`pdo`、`tokenizer`、`xml`、`gd`，以及至少一个 `pdo_mysql` / `pdo_sqlite` / `pdo_pgsql` 驱动。

> `gd` 用于绘制后台登录验证码，未启用时会自动降级为文字算术题，功能不受影响。

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
3. **站点信息**：站点名称 / 简介 / 地址，以及后台超级管理员账号密码（密码栏已预填一个自动生成的强密码，可直接使用或改成自己的），可勾选生产模式与导入演示内容
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

命令行部署（`php artisan migrate --seed`）会创建默认管理员 `admin@torchcms.com`，密码为**随机生成的强密码**，会在执行种子命令时打印在终端输出中。网页安装向导则以第 3 步填写的邮箱与密码为准：若邮箱同为 `admin@torchcms.com`，会直接覆盖该账号。

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
5. 配置 Web 服务器重写规则（Laravel 标准 `public` 目录指向）。**不要**为 `.js` / `.css` 等扩展名单独设置 `try_files $uri =404;`，那会把 Livewire 的脚本挡掉，登录页会直接不可用（宝塔默认站点模板正是这种写法），详见下方「宝塔面板部署」与常见问题
6. 若使用队列（邮件、异步任务），`QUEUE_CONNECTION=database` 时需常驻 `php artisan queue:work`

---

### 宝塔面板部署

宝塔的默认站点模板为 `.js` / `.css` 单独建了一个 `location`，但**只设了 `expires`、没有 `try_files` 兜底**，效果等同于「只服务物理文件」。Filament 的静态资源 `php artisan filament:assets` 发布成了物理文件，所以能正常加载，**只有 Livewire 的动态脚本会被 Nginx 返回 404**——表现为「提交登录表单后没有任何提示、直接跳回登录页」。下面这套配置可一次性避开它。

#### 1. 安装运行环境

在宝塔「软件商店」安装：

| 软件 | 版本 | 说明 |
| --- | --- | --- |
| Nginx | 1.20+ | Web 服务器 |
| PHP | 8.3 / 8.4 | `composer.json` 要求 `^8.3` |
| MySQL | 5.7+ / MariaDB 10.3+ | 也可改用 SQLite |
| Node.js | >= 18 | 仅在服务器上构建前端资源时需要 |

PHP 需勾选 `fileinfo`、`gd`、`pdo_mysql` 扩展，建议一并勾选 `intl`、`zip`、`bcmath`。再到「PHP 设置 → 禁用函数」确认 `putenv`、`proc_open` 未被禁用，否则 Composer 与部分 Laravel 功能会报错。

> 服务器内存小于 2GB 时 `composer install` 容易因内存不足被系统杀掉，可先创建 swap，或改用「本地构建后整体上传」的方式。

#### 2. 创建站点

「网站 → 添加站点」：

- 域名：填写实际域名（如 `www.qinlangtech.com`）
- 根目录：`/www/wwwroot/torchcms`，填**项目根目录**即可，不要直接填 `public`
- PHP 版本：8.3+
- 数据库：同页勾选 MySQL，记下库名 / 用户名 / 密码

创建后进入「站点 → 设置」：

1. **网站目录 → 运行目录** 选 `public`，并取消勾选 **防跨站攻击（open_basedir）**
2. **伪静态**，填入：

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# 覆盖宝塔默认的 js/css 规则：默认模板只有 expires 没有 try_files，
# 会把 Livewire 的动态脚本挡成 404，导致后台登录「无提示弹回登录页」
location ~ .*\.(js|css)?$ {
    expires    12h;
    access_log /dev/null;
    error_log  /dev/null;
    try_files  $uri /index.php?$query_string;
}
```

> **为什么写在「伪静态」而不是「配置文件」**：宝塔在 UI 上改动站点设置（切换运行目录、更新证书等）时会按模板重新生成配置文件，手改的内容会被覆盖；伪静态则是独立文件，由 `include` 插入 server 块中较靠前的位置。Nginx 对**同类型**的 location（以上两条都是正则 `~`）按**出现顺序取第一个匹配**，所以伪静态里这条会抢先命中，模板中的旧规则自然失效。

#### 3. 上传代码并安装依赖

任选一种：

- **Git 拉取**（推荐，便于后续更新）：在站点根目录执行 `git clone`，或在宝塔「终端」里操作
- **压缩包上传**：本地执行 `npm run build` 后，把整个项目（含 `public/build`、`vendor`）打包上传解压，服务器上就无需 Node 与 Composer

只上传源码时，在站点根目录执行：

```bash
composer install --no-dev --optimize-autoloader
npm install && npm run build      # 或改为本地构建后单独上传 public/build
```

#### 4. 初始化

```bash
cp .env.example .env
php artisan key:generate
```

在 `.env` 中配置数据库与访问地址：

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.qinlangtech.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=你的库名
DB_USERNAME=你的库用户名
DB_PASSWORD=你的库密码
```

`APP_URL` 必须与浏览器实际访问的协议、域名、端口**完全一致**，否则登录会不断弹回登录页（见常见问题「后台登录后又被弹回登录页」的排查第 1 条）。

```bash
php artisan migrate --seed
php artisan storage:link
php artisan optimize          # 缓存配置/路由/视图，加速生产环境
```

也可以跳过上面全部步骤，直接浏览器访问 `https://你的域名/install` 走网页安装向导，由向导代写 `.env`、建表并创建管理员。

> 执行过 `php artisan optimize` 后，再修改 `.env` 必须重新执行 `php artisan optimize:clear` 才会生效。

#### 5. 目录权限

宝塔默认以 `www` 用户运行 PHP，把项目属主改为 `www` 即可：

```bash
chown -R www:www /www/wwwroot/torchcms
chmod -R 775 storage bootstrap/cache
```

若开启「防跨站攻击（open_basedir）」，Laravel 读取项目上级目录会失败并报 500，请在第 2 步中关闭。

#### 6. 启用 HTTPS

「站点 → 设置 → SSL」申请证书后开启「强制 HTTPS」。宝塔会在配置文件中自动写入 `fastcgi_param HTTPS $https if_not_empty;`，PHP 侧能正确识别协议；若你手工改过配置文件，请确认这一行仍然存在，否则页面会按 `http` 去加载资源、被浏览器当作混合内容拦掉，表单会退化为普通提交。

#### 7. 验证

从登录页源码里找到 `livewire.min.js` 的完整地址，然后：

```bash
curl -sI "https://你的域名/livewire-xxxxxxxx/livewire.min.js?id=yyyyyyyy"
```

返回 `200` 且 `Content-Type: application/javascript` 即为正常，此时可正常登录后台。

---

### 常见问题

**后台 / 前台界面是英文，想改成中文**

界面语言由 `.env` 的 `APP_LOCALE` 决定，Filament 后台直接跟随它（没有单独的面板语言开关）。本项目默认值为 `zh_CN`，已存在的部署升级后请确认自己的 `.env` 也是中文：

```bash
APP_LOCALE=zh_CN
APP_FALLBACK_LOCALE=en   # 保留英文兜底，个别未翻译的文案会回落到英文
```

改完执行 `php artisan config:clear` 再刷新页面。若要改回英文，把 `APP_LOCALE` 设为 `en` 即可，后台与前台会一并切换。

**访问任何页面都被跳转到 `/install`**
系统未检测到安装信息。若确认数据库已初始化，检查 `.env` 的数据库配置是否正确；锁文件缺失时会自动探测数据库中的管理员表并补写 `storage/installed.lock`。

**后台登录后又被弹回登录页，且看不到任何错误提示**

这是「登录表单没有真正走 Livewire」或「Livewire 请求被拒绝」的典型表现，绝大多数情况不是密码错误（密码错 Filament 会明确提示）。按下面顺序排查：

1. `APP_URL` 必须与浏览器实际访问的协议、域名、端口完全一致——装完后换过域名、加了 HTTPS 最容易踩到。改完执行 `php artisan optimize:clear`
2. **确认 Nginx 没有把 Livewire 的脚本当成静态文件挡掉**。从登录页源码里找到 `livewire.min.js` 的完整地址（形如 `/livewire-xxxxxxxx/livewire.min.js?id=yyyyyyyy`），然后：

   ```bash
   curl -sI "https://你的域名/livewire-xxxxxxxx/livewire.min.js?id=yyyyyyyy"
   ```

   若返回的 404 页面里带着 `<hr><center>nginx</center>`，说明这个 404 **不是 Laravel 给的**，而是 Nginx 对 `.js` 这类扩展名单独配了 `try_files $uri =404;`（或等价的"只服务物理文件"规则，**宝塔默认站点模板即属此类**），请求根本没到达 Laravel。Filament 的静态资源是 `php artisan filament:assets` 发布出来的物理文件，因此能正常加载，**只有 Livewire 的脚本会挂**——这也是这个问题最容易被误判成"密码错了"的原因。修法是在该 `location` 里把 `=404` 换成 `/index.php?$query_string`，或直接删掉这个 `location` 块，让请求统一走：

   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

   改完 `nginx -t && systemctl reload nginx`，再执行一次上面的 `curl`，应当返回 `200` + `Content-Type: application/javascript`
3. 若站点由 Nginx 终止 HTTPS，需要把协议透传给 PHP：`fastcgi_param HTTPS on;` 与 `fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;`。否则页面会按 `http` 去加载 Filament/Livewire 资源，被浏览器当作混合内容拦掉，表单会退化成普通提交，一刷新就回到登录页
4. 打开浏览器开发者工具提交一次登录：**没有** `livewire/update` 请求说明前端资源没加载（看 Console 红色报错）；状态码 `419` 说明会话没保持住；`500` 则查 `storage/logs/laravel.log`
5. 确认会话确实写入了：`php artisan tinker --execute="echo DB::table('sessions')->count();"`，登录一次后该数字应当增加
6. 同一域名下并存多个 Laravel 应用时，为本站设置独立的 `SESSION_COOKIE`，避免共用 `laravel-session` 互相覆盖
7. 临时把 `.env` 的 `APP_DEBUG` 改为 `true` 再试一次，可直接看到被隐藏的真实异常

> 安全提示：表单退化成原生提交时，Filament 的登录 `<form>` 没有 `method` 属性，浏览器会按 **GET** 提交，邮箱与密码会明文出现在 URL 中，并被 Nginx 的 access log 记录下来。排查出该问题后，除了修 Nginx，还应清理日志并立即更换管理员密码。

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

**登录页验证码显示不出来 / 想关掉验证码**

验证码图片由 `/captcha/admin-login` 动态生成，需要 PHP 的 GD 扩展。未启用 GD 时登录页会自动改用文字算术题（如 `3 + 7 = ?`），校验逻辑完全一致。若图片位置显示为裂图，先确认该路由能直接访问（它本身不需要登录，不能被服务器规则拦截）。临时关闭：把 `.env` 的 `ADMIN_LOGIN_CAPTCHA` 设为 `false`，然后 `php artisan config:clear`。

验证码是一次性的——每次提交都会作废并换新，登录失败后请按新图重新输入。

**改了 `ADMIN_PASSWORD_SALT` 之后所有管理员都登录不上**

密码盐参与哈希运算，改值等于把所有已有密码作废。请改回原值，或按上面的方式用 `php artisan tinker` 重设密码。安装向导在首次安装时自动生成该值并固化，重复安装会沿用原值，不要手工改动。

从没有该配置的旧版本升级时，若 `.env` 中没有 `ADMIN_PASSWORD_SALT`，会退化为基于 `APP_KEY` 派生盐值：旧密码可直接登录，并在首次登录时自动升级为加盐哈希，无需重置任何账号。此时若想改用独立盐值，请在**任何人登录之前**先写入 `.env` 再执行 `php artisan config:clear`；等账号升级过密码之后再加，那些账号会登不上。
