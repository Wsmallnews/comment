# Comment

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsmallnews/comment.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/comment)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/comment/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/wsmallnews/comment/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/comment/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/wsmallnews/comment/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wsmallnews/comment.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/comment)

基于 Laravel + Filament 的通用评论系统。支持多租户、多范围隔离、嵌套回复、点赞、富文本内容，开箱即用的前端 Livewire 组件和 Filament 管理后台。

## 功能特性

- **多态评论**：任意 Eloquent 模型均可作为评论主体（Commentable）
- **多态评论者**：支持任意模型作为评论者（Commenter）
- **嵌套回复**：支持二层回复，自动关联被回复者（BeReplyer）
- **范围隔离**：通过 scope_type + scope_id 实现多范围数据隔离
- **多租户支持**：自动关联团队 team_id
- **内容类型**：支持纯文本、富文本（Richtext）、Markdown 三种内容类型
- **评论状态**：正常、待审核、已隐藏三种状态管理
- **点赞功能**：基于 preference 扩展包的点赞系统
- **Filament 后台管理**：完整的评论管理页面
- **前端组件**：开箱即用的 Livewire 评论列表和评论输入组件
- **高度可配置**：支持自定义模型、默认状态、内容类型等

## 安装

通过 Composer 安装：

```bash
composer require wsmallnews/comment
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="comment-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="comment-config"
```

发布语言文件（可选）：

```bash
php artisan vendor:publish --tag="comment-translations"
```

Optionally, you can publish the views using:

```bash
php artisan vendor:publish --tag="comment-views"
```

## 配置

This is the contents of the published config file:

```php
return [
    // 默认范围配置
    'scopeable' => [
        'scope_type' => 'sn-comment',
        'scope_id' => 0,
    ],

    // 默认评论内容类型：textarea / richtext / markdown
    'default_content_type' => ContentType::Textarea,

    // 默认评论状态：normal / unaudited / hidden
    'default_status' => CommentStatus::Normal,

    // 自定义模型
    'models' => [
        'comment' => Models\Comment::class,
        'comment_content' => Models\CommentContent::class,
    ],

    // 文件上传目录
    'file_directory' => 'sn/comment/',
];
```

## 快速开始

### 1. 为模型添加评论能力

让你的模型拥有评论功能，只需引入对应的 Trait：

```php
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Models\Concerns\Commentable;
use Wsmallnews\Comment\Models\Concerns\Commenter;
use Wsmallnews\Comment\Models\Concerns\BeReplyer;

class Post extends Model
{
    // 作为评论主体（被评论的对象）
    use Commentable;
}

class User extends Model
{
    // 作为评论者
    use Commenter;

    // 作为被回复者
    use BeReplyer;
}
```

## Filament 后台集成

### 注册插件

在 Panel 配置中注册 CommentPlugin：

```php
use Wsmallnews\Comment\CommentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            CommentPlugin::make()
                ->scopeable([
                    'scope_type' => 'blog',
                    'scope_id' => 1,
                ])
                ->customProperties([
                    'contentType' => ContentType::Textarea,
                    'commentStatus' => CommentStatus::Normal,
                    'emptyLabel' => '暂无评论',
                    'emptyTipLabel' => '快来发表第一条评论吧',
                ]),
        ]);
}
```

### 可用属性

通过 `customProperties()` 方法可以自定义以下属性：

| 属性            | 说明             | 默认值                  |
| --------------- | ---------------- | ----------------------- |
| `contentType`   | 内容类型枚举     | `ContentType::Textarea` |
| `commentStatus` | 评论状态枚举     | `null`                  |
| `emptyLabel`    | 空评论提示文本   | 语言包默认值            |
| `emptyTipLabel` | 空评论提示副文本 | 语言包默认值            |

### 后台页面

注册后可在 Filament 后台左侧导航「评论管理」中查看所有评论，支持：

- 按评论内容、评论者、被回复者筛选
- 状态筛选（正常 / 待审核 / 已隐藏）
- 批量删除、批量强制删除、批量恢复
- 软删除支持

## 前端 Livewire 组件

### 评论列表组件

```blade
<livewire:sn-comment-components-comments
    :commentable="$post"
    :scopeType="'blog'"
    :scopeId="1"
    :properties="[
        'emptyLabel' => '暂无评论',
        'emptyTipLabel' => '快来发表第一条评论吧'
    ]"
    :contentType="'textarea'"
    wire:key="comments-{{ $post->id }}"
/>
```

组件会自动处理：

- 评论分页加载
- 嵌套回复展开/收起
- 点赞数显示
- 评论者头像和昵称展示

### Widget 方式（Filament 页面内嵌）

在 Filament 的 Page 或 EditRecord 中使用评论 Widget：

```php
use Wsmallnews\Comment\Filament\Pages\Comment\Widgets\Comment as CommentWidget;

class PostEdit extends EditRecord
{
    protected function getFooterWidgets(): array
    {
        return [
            CommentWidget::make([
                'scopeable' => ['scope_type' => 'blog', 'scope_id' => 1],
                'widget_type' => 'commentable',  // commentable = 评论主体 | commenter = 评论者
            ]),
        ];
    }
}
```

## 依赖

- PHP ^8.2
- Laravel（通过 Filament 依赖）
- Filament ^4.0 || ^5.0
- [wsmallnews/support](https://github.com/wsmallnews/support) - 基础支持包
- [wsmallnews/preference](https://github.com/wsmallnews/preference) - 偏好/点赞系统

## 更新日志

请查看 [CHANGELOG](CHANGELOG.md) 了解版本变更详情。

## 贡献

请查看 [CONTRIBUTING](.github/CONTRIBUTING.md) 了解贡献方式。

## 安全漏洞

请查阅[安全策略](../../security/policy)了解如何报告安全漏洞。

## 作者

- [smallnews](https://github.com/Wsmallnews)
- [所有贡献者](../../contributors)

## License

The MIT License (MIT). 请查看 [License File](LICENSE.md) 获取更多信息。
