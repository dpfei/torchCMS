<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\News;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * 生成演示栏目与内容，便于预览前台效果。可反复执行。
     */
    public function run(): void
    {
        $company = Category::query()->firstOrCreate(
            ['cat_name' => '公司新闻'],
            ['sort' => 1, 'is_menu' => 1, 'description' => '公司动态、活动与公告发布。']
        );

        $tech = Category::query()->firstOrCreate(
            ['cat_name' => '技术分享'],
            ['sort' => 2, 'is_menu' => 1, 'description' => 'Web 开发、架构设计与工程实践经验分享。']
        );

        $items = [
            [
                'cat_id' => $company->id,
                'title' => 'torchCMS 正式发布：基于 Laravel 的内容管理系统',
                'keywords' => 'torchCMS,Laravel,CMS',
                'description' => 'torchCMS 是一套基于 Laravel 与 Filament 构建的内容管理系统，内置栏目、内容、媒体库、权限与系统设置模块。',
                'content' => '<p>torchCMS 是一套基于 <strong>Laravel</strong> 与 <strong>Filament</strong> 构建的内容管理系统。</p><h2>核心特性</h2><ul><li>栏目与内容管理</li><li>媒体库统一管理</li><li>基于角色的权限控制</li><li>系统设置与 SEO 支持</li></ul><p>欢迎使用！</p>',
                'copyfrom' => 'torchCMS 团队',
                'sort' => 10,
            ],
            [
                'cat_id' => $company->id,
                'title' => '产品更新公告：媒体库与权限系统上线',
                'keywords' => '更新,媒体库,权限',
                'description' => '本次更新新增媒体库与基于角色的权限管理系统，后台管理更加高效安全。',
                'content' => '<p>本次更新新增了媒体库与权限系统，支持为不同管理员分配不同角色与权限。</p><p>登录后台即可体验。</p>',
                'copyfrom' => 'torchCMS 团队',
                'sort' => 9,
            ],
            [
                'cat_id' => $company->id,
                'title' => '如何快速搭建企业官网内容后台',
                'keywords' => '企业官网,内容后台',
                'description' => '使用 torchCMS 可以快速搭建企业官网所需的内容管理后台，几分钟即可完成部署。',
                'content' => '<p>只需完成数据库配置与迁移，即可开始录入内容。</p><p>前台模板可直接复用或自行扩展。</p>',
                'copyfrom' => '运营部',
                'sort' => 8,
            ],
            [
                'cat_id' => $tech->id,
                'title' => 'Laravel 中用 Policy 做细粒度权限控制',
                'keywords' => 'Laravel,Policy,权限',
                'description' => '介绍如何结合 Laravel Policy 与权限包实现资源级授权，并在 Filament 后台生效。',
                'content' => '<p>Laravel 的 <strong>Policy</strong> 提供了资源级授权能力。</p><h2>实现思路</h2><ol><li>为用户模型引入权限 Trait</li><li>为每个模型创建 Policy</li><li>在 Policy 中校验权限</li></ol><p>Filament 会自动使用 Policy 完成按钮与页面的可见性控制。</p>',
                'copyfrom' => '技术部',
                'sort' => 7,
            ],
            [
                'cat_id' => $tech->id,
                'title' => 'Filament 表单与表格开发实践',
                'keywords' => 'Filament,表单,表格',
                'description' => '总结 Filament 中表单与表格的常用组件、筛选与批量操作的写法。',
                'content' => '<p>Filament 让后台开发效率大幅提升。</p><ul><li>表单：TextInput、FileUpload、RichEditor</li><li>表格：筛选、排序、批量操作</li></ul>',
                'copyfrom' => '技术部',
                'sort' => 6,
            ],
            [
                'cat_id' => $tech->id,
                'title' => 'CMS 前台模板的 SEO 优化要点',
                'keywords' => 'SEO,前台,模板',
                'description' => '从标题、关键词、描述、语义化标签等角度介绍内容站点的 SEO 优化要点。',
                'content' => '<p>良好的 SEO 需要关注以下要点：</p><ol><li>每页独立的 title 与 description</li><li>合理的关键词</li><li>语义化的 HTML 结构</li><li>可读的 URL</li></ol>',
                'copyfrom' => '技术部',
                'sort' => 5,
            ],
        ];

        foreach ($items as $item) {
            News::query()->firstOrCreate(
                ['title' => $item['title']],
                $item + ['status' => News::STATUS_ENABLED]
            );
        }
    }
}
