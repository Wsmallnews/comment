## Comment 包（wsmallnews/comment）

`wsmallnews/comment` 是一个多态评论系统，支持文本域、Markdown、富文本三种内容类型，深度集成 Filament 面板和 Livewire 前端。命名空间根为 `Wsmallnews\Comment`，Blade 视图前缀为 `sn-comment`，配置文件为 `config/sn-comment.php`。

### 核心架构

通过 `sn_comments` 表（主表）+ `sn_comment_contents` 表（格式化内容表）实现评论存储：

```
sn_comments                          sn_comment_contents
├── id                               ├── id
├── parent_id (自关联)                ├── contentable_type (多态)
├── commentable_type (多态关联主体)    ├── contentable_id
├── commentable_id                    ├── content (rich text/markdown)
├── commenter_type (多态评论者)        ├── content_type
├── commenter_id                     ├── team_id
├── be_replyer_type (多态被回复者)     ├── timestamps
├── be_replyer_id
├── content_type (text/markdown/richtext)
├── content (textarea 内容，格式化时为 null)
├── counter (JSON: comment_num, like_num)
├── status (normal/unaudited/hidden)
├── images (JSON 数组)
├── options (JSON)
├── team_id
├── timestamps, softDeletes
```

**三组多态关联：**
- **commentable（评论主体）**：被评论的内容实体，使用 `Commentable` trait
- **commenter（评论者）**：发表评论的用户/模型，使用 `Commenter` trait  
- **beReplyer（被回复者）**：被回复的评论者，使用 `BeReplyer` trait

**自关联树形结构**：通过 `parent_id` 实现评论嵌套，所有子评论直接挂在顶层评论下（扁平化，非多层级树）。

### ContentType 支持

评论内容支持三种格式，通过 `ContentType` 枚举控制：

| 类型 | 存储位置 | 组件 | 说明 |
|---|---|---|---|
| `Textarea` | `sn_comments.content` | `Textarea` | 纯文本，默认类型 |
| `Richtext` | `sn_comment_contents.content` | `RichEditor` | 富文本，支持文件附件 |
| `Markdown` | `sn_comment_contents.content` | `MarkdownEditor` | Markdown 格式 |

格式化内容（Richtext/Markdown）通过 `commentContent()` 多态关联到 `sn_comment_contents` 表。评论模型 scope `with('commentContent')` 预加载格式化内容。

`CommentAction` trait 中的 `configureAction()` 根据 `$this->contentType` 自动选择对应的表单组件：

@verbatim
```php
$schemas = match ($this->contentType) {
    ContentType::Richtext => $this->getRichtextComponents($parentComment),
    ContentType::Markdown => $this->getMarkdownComponents($parentComment),
    default => $this->getTextareaComponents($parentComment),
};
```
@endverbatim

### CommentStatus 枚举

`Wsmallnews\Comment\Enums\CommentStatus`（BackedEnum: string）：

| 状态 | 值 | 颜色 | 图标 |
|---|---|---|---|
| `Normal` | `normal` | success | Heroicon::Eye |
| `Unaudited` | `unaudited` | warning | Heroicon::DocumentCheck |
| `Hidden` | `hidden` | gray | Heroicon::EyeSlash |

评论模型的三个 scope：`normal()`、`unaudited()`、`hidden()`。

默认状态由 `Utils::getDefaultCommentStatus()` 获取，对应配置 `sn-comment.default_status`。

### Model traits（关联侧）

评论系统通过三个 trait 建立模型的多态关联：

#### Commentable（被评论的内容模型）

`Wsmallnews\Comment\Models\Concerns\Commentable`：

@verbatim
```php
use Wsmallnews\Comment\Models\Concerns\Commentable;

$post->comments;                    // MorphMany 关联，获取所有评论
$post->comments()->normal();        // 带 scope 查询正常评论
```
@endverbatim

#### Commenter（评论者模型）

`Wsmallnews\Comment\Models\Concerns\Commenter`：

@verbatim
```php
use Wsmallnews\Comment\Models\Concerns\Commenter;

$user->comments;                    // MorphMany 关联，获取用户所有评论
```
@endverbatim

#### BeReplyer（被回复者模型）

`Wsmallnews\Comment\Models\Concerns\BeReplyer`：

@verbatim
```php
use Wsmallnews\Comment\Models\Concerns\BeReplyer;

$user->beReplyerComments;           // MorphMany 关联，被回复的评论列表
```
@endverbatim

### Comment 模型

`Wsmallnews\Comment\Models\Comment`（继承 `SupportModel`，可通过 `config('sn-comment.models.comment')` 替换）。

**核心属性和关系：**

@verbatim
```php
class Comment extends SupportModel
{
    use Likeable;                   // 评论可被点赞（依赖 preference 包）
    use Preferenceable;
    use SoftDeletes;

    protected $table = 'sn_comments';

    protected $casts = [
        'counter' => CounterCast::class,
        'images' => 'array',
        'options' => 'array',
        'status' => CommentStatus::class,
        'content_type' => ContentType::class,
    ];

    // MorphTo 关联
    commentable()       // 评论主体
    commenter()         // 评论者
    beReplyer()         // 被回复者

    // 自关联树
    children()          //  HasMany 子评论（按 created_at asc 排序）
    parent()            //  BelongsTo 父评论

    // 格式化内容
    commentContent()    //  MorphOne 关联 CommentContent

    // Query Scopes
    scopeNormal()
    scopeUnaudited()
    scopeHidden()
}
```
@endverbatim

**计数器字段**（`counter` JSON 列，需配合 `CounterCast`）：
- `comment_num`：子评论数量
- `like_num`：点赞数量

当回复某条评论时，系统自动给上级评论的 `counter->comment_num` 加 1（通过 `incrementJson`）。

**点赞功能**：Comment 模型 use 了 `Likeable` trait（来自 preference 包），允许用户点赞评论。`toggleLike()` 操作在 Livewire 和 Filament 组件中均可使用。

### CommentContent 模型

`Wsmallnews\Comment\Models\CommentContent`（继承 `SupportModel`，可通过 `config('sn-comment.models.comment_content')` 替换）。

@verbatim
```php
class CommentContent extends SupportModel
{
    protected $table = 'sn_comment_contents';

    protected $casts = [
        'content_type' => ContentType::class,
    ];

    contentable()       // MorphTo 多态关联
    team()              // BelongsTo 租户
}
```
@endverbatim

`contentable` 的多态映射为 `'sn-comment' => Comment::class`，在 `CommentServiceProvider::packageBooted()` 中通过 `Relation::enforceMorphMap` 注册。

### Livewire 前端组件

三个前端组件均继承 `Wsmallnews\Comment\Livewire\Components\Base`（→ `Wsmallnews\Support\Livewire\Base`，使用 `Scopeable` trait）。

#### Comments（评论列表 + 添加评论）

`Wsmallnews\Comment\Livewire\Components\Comments`，注册名 `sn-comment-components-comments`：

@verbatim
```php
use CanAddComment;          // $canAddComment = true
use CanBeContained;         // 支持容器模式
use CanPagination;          // 分页（已包含 WithPagination，不要重复 use）
use CommentAction;          // 评论/回复/删除/审核操作
use HasAuth;                // 认证用户
use HasCommentStatus;       // $commentStatus
use HasContentType;         // $contentType
use HasProperties;          // 自定义属性传递
use WithoutUrlPagination;   // 非 URL 分页
```

属性：
- `$commentable`（Model）：评论主体模型
- `$parentId`（int，默认 0）：父级评论 ID
- `$loadChildren`（bool，默认 false）：是否自动加载子评论
- `$comments`（Collection）：评论集合

@endverbatim

**使用示例：**

@verbatim
```blade
{{-- 在某文章页面显示评论 --}}
<livewire:sn-comment-components-comments
    :commentable="$post"
    content-type="textarea"
    :can-add-comment="true"
    :empty-label="'暂无评论'"
/>

{{-- 富文本模式评论 --}}
<livewire:sn-comment-components-comments
    :commentable="$article"
    content-type="richtext"
    comment-status="normal"
/>
```
@endverbatim

#### Comment（单条评论展示 + 展开子评论 + 点赞）

`Wsmallnews\Comment\Livewire\Components\Comment`，注册名 `sn-comment-components-comment`：

属性：
- `$commentable`（Model）：评论主体
- `$comment`（CommentModel）：当前评论实例
- `$loadChildren`（bool，默认 false）：是否展开子评论

方法：
- `startLoadChildren()`：展开子评论
- `hiddenChildren()`：收起子评论
- `toggleLike()`：点赞/取消点赞，未登录时发送失败通知

@verbatim
```blade
{{-- 往往在 comments 组件内部递归使用 --}}
<livewire:sn-comment-components-comment
    :commentable="$commentable"
    :comment="$comment"
    :load-children="false"
/>
```
@endverbatim

#### Base（公共基类）

`Wsmallnews\Comment\Livewire\Components\Base`：

@verbatim
```php
class Base extends BaseComponent    // Wsmallnews\Support\Livewire\Base
{
    use Scopeable;
}
```
@endverbatim

所有前端评论组件通过 Base 获得 scope 能力。

### Filament 面板组件

Filament 面板中的评论组件直接继承 `Filament\Pages\BasePage`，更适合面板环境使用。

#### Comments（面板评论列表）

`Wsmallnews\Comment\Filament\Pages\Comment\Components\Comments`，注册名 `sn-comment-fi-comments`：

使用与 Livewire 版本相同的 traits，但直接继承 `BasePage`。视图为 `sn-comment::filament.pages.comment.components.comments`。

额外属性：
- `$commenter`（?Model）：按评论者筛选
- `$beReplyer`（?Model）：按被回复者筛选

`getViewData()` 按优先级构建查询：
1. `$commentable` → `commentable->comments()`
2. `$commenter` → `commenter->comments()`
3. `$beReplyer` → `beReplyer->beReplyComments()`
4. 默认 → `CommentModel::query()`（所有评论）

#### Comment（面板单条评论）

`Wsmallnews\Comment\Filament\Pages\Comment\Components\Comment`，注册名 `sn-comment-fi-comment`：

与 Livewire 版本功能相同，但继承 `BasePage`。视图为 `sn-comment::filament.pages.comment.components.comment`。

在 mount 时自动设置认证用户：`$this->authUser(Filament::auth()->user())`。

#### 面板 Widget 包装器

`Wsmallnews\Comment\Filament\Pages\Comment\Widgets\Comment`，视图为 `sn-comment::filament.pages.comment.widgets.comment`。在 Filament 页面中作为 Widget 嵌入渲染。

@verbatim
```blade
{{-- 在 Filament 页面中使用 --}}
<x-filament-widgets::widgets>
    <livewire:sn-comment-fi-comments
        :commentable="$record"
        content-type="richtext"
    />
</x-filament-widgets::widgets>
```
@endverbatim

### Filament 页面

#### Base（页面基类）

`Wsmallnews\Comment\Filament\Pages\Comment\Base`：

@verbatim
```php
abstract class Base extends Page
{
    use Scopeable;

    protected static ?string $slug = 'comments';
    protected static ?int $navigationSort = 1;
    protected string $view = 'sn-comment::filament.pages.comment.comment-page';

    // 可被子类覆盖的静态属性
    protected static ?string $emptyLabel = null;
    protected static ?string $emptyTipLabel = null;
    protected static ContentType $contentType = ContentType::Textarea;
    protected static ?CommentStatus $commentStatus = null;

    // 静态方法（翻译默认值）
    getModelLabel()           -> __('sn-comment::comment.comment_page.model_label')
    getPluralModelLabel()    -> __('sn-comment::comment.comment_page.plural_model_label')
    getTitle()               -> __('sn-comment::comment.comment_page.title')
    getNavigationLabel()     -> __('sn-comment::comment.comment_page.navigation_label')
    getNavigationGroup()     -> __('...global_default.navigation_group')
    getContentType()         -> Textarea
    getCommentStatus()       -> null
    getEmptyLabel()          -> __('...comment_page.no_comments')
    getEmptyTipLabel()       -> __('...comment_page.no_comments_description')
    getProperties()          -> ['emptyLabel' => ..., 'emptyTipLabel' => ...]
}
```
@endverbatim

#### CommentPage（面板页面）

`Wsmallnews\Comment\Filament\Pages\Comment\CommentPage`（final class）：

继承 `Base`，注册到 Filament 面板。使用 `BelongsToParent`、`BelongsToTenant`、`HasGlobalSearch`、`HasLabels`、`HasNavigation`、`HasCustomProperties` traits。

核心方法覆盖：
- `getScopeType()` → 优先从 `CommentPlugin` 自定义属性读取
- `getScopeId()` → 同上
- `getContentType()` → 优先从自定义属性读取
- `getCommentStatus()` → 仅从自定义属性读取
- `getEmptyLabel()` / `getEmptyTipLabel()` → 优先从自定义属性，fallback 到 parent

`getEssentialsPlugin()` 返回 `CommentPlugin::get()`。

### Livewire Concerns（Traits）

#### CommentAction（核心操作逻辑）

`Wsmallnews\Comment\Livewire\Concerns\CommentAction`：

提供 8 个 Action 方法和 3 个私有表单组件方法：

**Filament Actions（面板用）：**
- `filamentCommentAction()`：CreateAction，添加评论
- `filamentReplyAction()`：CreateAction（link 样式），回复评论
- `filamentDeleteAction()`：删除评论（使用 `ActionComponents::deleteAction`）
- `filamentStatusAction()`：审核评论状态（Radio 切换）

**通用 Actions（前端用）：**
- `commentAction()`：CreateAction，添加评论
- `replyAction()`：CreateAction（link 样式），回复评论

**私有方法：**
- `configureAction(CreateAction, $type)`：核心配置方法，处理创建/回复的完整流程
- `getTextareaComponents($parentComment)`：文本域 + 图片上传
- `getRichtextComponents($parentComment)`：富文本编辑器（通过 commentContent 关联）
- `getMarkdownComponents($parentComment)`：Markdown 编辑器（通过 commentContent 关联）

`configureAction()` 的关键逻辑：

1. 根据 `$this->contentType` 选择表单组件
2. 处理回复场景：自动填充 `parent_id`、`be_replyer_*` 字段
3. 创建模型时关联 `commentable`、`commenter`
4. 提供额外字段：`commenter_name`、`commenter_avatar_url`、`status`、`content_type`
5. 回复时自动递增上级评论的 `counter->comment_num`
6. sticky modal、根据内容类型设置宽度

@verbatim
```php
// configureAction 中的 using 回调关键逻辑
$parentCommentId = $arguments['id'] ?? null;
$parentComment = $parentCommentId ? Utils::getCommentModel()::find($parentCommentId) : null;

if ($parentComment) {
    $data['parent_id'] = $parentComment->parent_id ?: $parentComment->id;
    $data['be_replyer_type'] = $parentComment->commenter_type;
    $data['be_replyer_id'] = $parentComment->commenter_id;
    $data['be_replyer_name'] = $parentComment->commenter_name;
    $data['be_replyer_avatar_url'] = $parentComment->commenter_avatar_url;
}
```
@endverbatim

#### 其他 Concerns

| Trait | 文件 | 说明 |
|---|---|---|
| `CanAddComment` | `Livewire\Concerns\CanAddComment` | `$canAddComment = true`，控制组件是否允许用户添加评论 |
| `CanComment` | `Livewire\Concerns\CanComment` | `$canComment = true`，控制是否可评论（与 `CanAddComment` 独立） |
| `HasCommentStatus` | `Livewire\Concerns\HasCommentStatus` | `$commentStatus = null`，用于设置评论发布后的默认状态 |

### CommentPlugin

`Wsmallnews\Comment\CommentPlugin`（implements `Filament\Contracts\Plugin`）。

使用 traits（来自 `BezhanSalleh\PluginEssentials` 和 support 包）：
- `BelongsToParent`、`BelongsToTenant`
- `HasGlobalSearch`、`HasLabels`、`HasNavigation`
- `HasPluginDefaults`、`WithMultipleResourceSupport`
- `HasCustomProperties`

**插件 ID**：`sn-comment`

**`register()` 方法**：从 `Utils::getPanelRegister('pages')` 注册页面。

**`getPluginDefaults()`**（所有翻译使用闭包延迟求值）：

@verbatim
```php
[
    'navigationGroup' => fn () => __('...global_default.navigation_group'),
    'globallySearchable' => false,
    'resources' => [
        CommentPage::class => [
            'modelLabel' => fn () => __('...model_label'),
            'pluralModelLabel' => fn () => __('...plural_model_label'),
            'navigationLabel' => fn () => __('...navigation_label'),
            'navigationIcon' => Heroicon::OutlinedChatBubbleLeft,
            'activeNavigationIcon' => Heroicon::ChatBubbleLeft,
            'navigationSort' => 1,
        ],
    ],
]
```
@endverbatim

### Service Provider

`Wsmallnews\Comment\CommentServiceProvider`（继承 `PackageServiceProvider`）：

**配置：**
- 名称：`sn-comment`
- 视图前缀：`sn-comment`
- 命令：`CommentInstallCommand`
- 迁移：`create_sn_comments_table`、`create_sn_comment_contents_table`
- 翻译：自动加载 `resources/lang/` 目录
- 视图：`hasViews('sn-comment')`

**`packageBooted()` 中的注册：**

@verbatim
```php
// 模型别名
Relation::enforceMorphMap(['sn-comment' => Utils::getCommentModel()]);

// Filament 面板组件（BasePage 继承）
Livewire::component('sn-comment-fi-comments', Comments::class);
Livewire::component('sn-comment-fi-comment', Comment::class);

// Livewire 前端组件
Livewire::component('sn-comment-components-comments', ComponentsComments::class);
Livewire::component('sn-comment-components-comment', ComponentsComment::class);

// Stubs 发布
// Filament 资源注册
// 图标注册
```
@endverbatim

### 配置

`config/sn-comment.php`：

@verbatim
```php
return [
    'scopeable' => [
        'scope_type' => 'sn-comment',       // 默认作用域类型
        'scope_id' => 0,                     // 0 = 全局
    ],
    'default_content_type' => ContentType::Textarea,
    'default_status' => CommentStatus::Normal,
    'models' => [
        'comment' => Models\Comment::class,         // 可替换
        'comment_content' => Models\CommentContent::class,  // 可替换
    ],
    'panel_register' => [
        'pages' => [CommentPage::class],    // 面板注册的页面
    ],
    'file_directory' => 'sn/comment/',       // 文件上传基础目录
];
```
@endverbatim

### Utils 工具类

`Wsmallnews\Comment\Support\Utils` — 全部为静态方法：

| 方法 | 说明 |
|---|---|
| `getConfig(?string $name, $default)` | 读取 `sn-comment` 配置（dot notation） |
| `getScopeableContext()` | 从配置创建 ScopeableContext 值对象 |
| `getScopeable()` | 返回 `['scope_type' => '...', 'scope_id' => 0]` |
| `getScopeType()` | 获取默认 scope_type |
| `getScopeId()` | 获取默认 scope_id |
| `getDefaultContentType()` | 获取默认内容类型 |
| `getDefaultCommentStatus()` | 获取默认评论状态 |
| `getPanelRegister($type)` | 获取面板注册配置（pages/resources） |
| `getModel(string $name, bool $shouldException = true)` | 获取配置的模型类名，`false` 时不抛异常 |
| `getCommentModel()` | `getModel('comment')` 快捷方式 |
| `getCommentContentModel()` | `getModel('comment_content')` 快捷方式 |
| `getFileDirectory(?string $type)` | 获取文件目录（自动追加日期），如 `sn/comment/comments/20260530` |

### Testing

`Wsmallnews\Comment\Testing\TestsComment` trait，用于测试中辅助评论相关操作。

### Facade

`Wsmallnews\Comment\Facades\Comment`，accessor 为 `\Wsmallnews\Comment\Comment::class`（空壳类，仅用于 Facade 注册）。

### 正确命名空间速查

| 类别 | 命名空间 |
|---|---|
| Comment 模型 | `Wsmallnews\Comment\Models\Comment` |
| CommentContent 模型 | `Wsmallnews\Comment\Models\CommentContent` |
| Commenter trait | `Wsmallnews\Comment\Models\Concerns\Commenter` |
| Commentable trait | `Wsmallnews\Comment\Models\Concerns\Commentable` |
| BeReplyer trait | `Wsmallnews\Comment\Models\Concerns\BeReplyer` |
| Livewire 前端组件 | `Wsmallnews\Comment\Livewire\Components\` |
| Livewire Base | `Wsmallnews\Comment\Livewire\Components\Base` |
| Livewire Concerns | `Wsmallnews\Comment\Livewire\Concerns\` |
| Filament 页面组件 | `Wsmallnews\Comment\Filament\Pages\Comment\Components\` |
| Filament Widgets | `Wsmallnews\Comment\Filament\Pages\Comment\Widgets\` |
| Filament 页面 | `Wsmallnews\Comment\Filament\Pages\Comment\Base` / `CommentPage` |
| CommentPlugin | `Wsmallnews\Comment\CommentPlugin` |
| CommentStatus | `Wsmallnews\Comment\Enums\CommentStatus` |
| Utils | `Wsmallnews\Comment\Support\Utils` |
| Facade | `Wsmallnews\Comment\Facades\Comment` |
| 异常 | `Wsmallnews\Comment\Exceptions\CommentException` |

### 常见错误

- **评论主体模型必须 use `Commentable` trait**，否则 `$post->comments()` 关联查询不存在。
- **评论者模型需要 `getFilamentName()` 方法**，`CommentAction::configureAction()` 在创建评论时会调用 `$user->getFilamentName()` 填充 `commenter_name` 字段。
- **`CanPagination` 已包含 `WithPagination`**，不要在 Livewire/Filament 组件中重复 `use WithPagination`。
- **counter 字段使用 JSON 格式**，模型中需配合 support 包的 `CounterCast` 使用：`'counter' => CounterCast::class`。使用 `incrementJson('counter->comment_num')` 而非直接赋值。
- **格式化内容需预加载关联**：RichText/Markdown 模式下，评论列表查询需 `.when($this->isFormattedContent(), fn($q) => $q->with('commentContent'))`，否则 commentContent 为 null。
- **`content` 字段在格式化模式下为 null**：Richtext/Markdown 内容存储在 `sn_comment_contents` 表，`sn_comments.content` 仅在 Textarea 模式下使用。
- **面板组件 mount 时需设置 authUser**：`$this->hasAuthUser() || $this->authUser(Filament::auth()->user())`，否则 `CommentAction` 中的认证检查会失败。
- **`sn-comment` morph map**：在 `CommentServiceProvider::packageBooted()` 中通过 `Relation::enforceMorphMap` 注册，确保所有多态查询使用别名而非全类名。
- **`Utils::getModel()` 默认会抛异常**，传递 `false` 作为第二个参数以允许返回 `null`。
- **`Utils` 所有方法都是静态的**，使用 `Utils::getConfig()` 而非 `(new Utils)->getConfig()`。