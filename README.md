# A versatile commenting system built on Laravel + Filament

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsmallnews/comment.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/comment)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/comment/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/wsmallnews/comment/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/wsmallnews/comment/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/wsmallnews/comment/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/wsmallnews/comment.svg?style=flat-square)](https://packagist.org/packages/wsmallnews/comment)

A versatile commenting system built on Laravel + Filament. It supports multi-tenancy, multi-scope isolation, nested replies, likes, rich text content, out-of-the-box front-end Livewire components, and Filament admin panel.

## Overview

- **Polymorphic Comments**：Any Eloquent model can be a comment subject (Commentable)
- **Polymorphic Commenters**：Support any model as a commenter (Commenter)
- **Nested Replies**：Support two-level replies, automatically associate with the replied-to (BeReplyer)
- **Scope Isolation**：Through scope_type + scope_id, multi-scope data isolation is achieved
- **Multi-Tenantancy Support**：Automatically associate with team team_id
- **Content Types**：Support plain text, rich text (Richtext), and Markdown content types
- **Comment Status**：Support normal, pending, and hidden comment statuses
- **Like Function**：Based on [Wsmallnews/preference](https://github.com/wsmallnews/preference) extension
- **Filament Admin Panel**：Full comment management page
- **Front-end Livewire Components**：开箱即用的 Livewire comment list and comment input components
- **Highly Configurable**：Support custom models, default status, content types, etc
- **Register the CommentPlugin**：Based on [bezhansalleh/filament-plugin-essentials](https://github.com/bezhansalleh/filament-plugin-essentials) extension

## Installation

You can install the package via composer:

```bash
composer require wsmallnews/comment:^1.0
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="sn-comment-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="sn-comment-config"
```

Optionally, you can publish the views using:

```bash
php artisan vendor:publish --tag="sn-comment-views"
```

Multi language support, you can publish the language files using

```bash
php artisan vendor:publish --tag="sn-comment-translations"
```

This is the contents of the published config file:

```php

use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Models;
use Wsmallnews\Support\Enums\ContentType;

return [
    /**
     * Default scopeable
     */
    'scopeable' => [
        'scope_type' => 'sn-comment',
        'scope_id' => 0,
    ],

    /**
     * Default comment contentType
     */
    'default_content_type' => ContentType::Textarea,

    /**
     * Default comment status
     */
    'default_status' => CommentStatus::Normal,

    /**
     * Custom models
     */
    'models' => [
        'comment' => Models\Comment::class,
        'comment_content' => Models\CommentContent::class,
    ],

    /**
     * File base directory (only used by filament default upload component (Forms\Components\FileUpload))
     */
    'file_directory' => 'sn/comment/',
];
```

## Quick Start

### 1. Add comment capability to your models

Give your models comment capability by adding the corresponding Traits:

```php
use Illuminate\Database\Eloquent\Model;
use Wsmallnews\Comment\Models\Concerns\Commentable;
use Wsmallnews\Comment\Models\Concerns\Commenter;
use Wsmallnews\Comment\Models\Concerns\BeReplyer;

class Post extends Model
{
    // As commentable (the object being commented on)
    use Commentable;
}

class User extends Model
{
    // As commenter (the user commenting on the object)
    use Commenter;

    // As be replyer
    use BeReplyer;
}
```

### 2. Use the Livewire component

Use the Livewire component in your Blade view:

```php
<livewire:sn-comment-components-comments
    scopeType="default"
    :scopeId="0"
    :commentable="$post"
    :properties="[
        'emptyLabel' => 'No comments',
        'emptyTipLabel' => 'Add your first comment'
    ]"
    :contentType="\Wsmallnews\Support\Enums\ContentType::Textarea"
    page-name="cp"
/>
```

### 3. Custom theme

You should use a [filament custom theme](https://filamentphp.com/docs/5.x/styling/overview#creating-a-custom-theme)

You should add the following code to your custom theme file. If you custom theme file is `/resources/css/filament/admin/theme.css`

```css
@import '../../../../vendor/wsmallnews/support/resources/css/index.css';
@import '../../../../vendor/wsmallnews/comment/resources/css/index.css';
```

## Filament Integration

### Register the CommentPlugin

Register the CommentPlugin in your Panel configuration:

```php
use Wsmallnews\Comment\CommentPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            CommentPlugin::make()
                ->forResource(CommentPage::class)
                ->navigationLabel('Comment manage')
                ->navigationGroup('Website manage')
                ->navigationIcon(Heroicon::OutlinedChatBubbleLeft)
                ->activeNavigationIcon(Heroicon::ChatBubbleLeft)
                ->navigationSort(10)
                ->modelLabel('Comment')
                ->pluralModelLabel('Comments')
                ->customProperties([
                    'contentType' => ContentType::Textarea,
                    'commentStatus' => CommentStatus::Normal,
                    'emptyLabel' => 'No comments',
                    'emptyTipLabel' => 'Add your first comment',
                ]),
        ]);
}
```

#### Available Properties

You can use the `customProperties()` method to customize the following properties:

| Property        | Description             | Default                  |
| --------------- | ----------------------- | ------------------------ |
| `contentType`   | Content type enum       | `ContentType::Textarea`  |
| `commentStatus` | Comment status enum     | `CommentStatus::Normal`  |
| `emptyLabel`    | Empty comment label     | Language package default |
| `emptyTipLabel` | Empty comment tip label | Language package default |

### Backend Page

After registration, you can view all comments in the Filament backend navigation:

#### Custom comment page

To customize the comment page, you can extend `Wsmallnews\Comment\Filament\Pages\Comment\Base` class in your own namespace:

```php
<?php

namespace App\Filament\Pages\Comment;

use Wsmallnews\Comment\Filament\Pages\Comment\Base;

class CommentPage extends Base
{

}

```

### Widget

To use the comment widget in your Filament page comment page:

```php
use Wsmallnews\Comment\Filament\Pages\Comment\Widgets\Comment as CommentWidget;

class ViewPost extends ViewRecord
{
    protected function getFooterWidgets(): array
    {
        return [
            CommentWidget::make([
                'scopeType' => 'default',
                'scopeId' => 0,
                'widgetType' => 'commentable',  // commentable = Commentable | commenter = Commenter
                'canAddComment' => true,
            ]),
        ];
    }
}
```

To use the comment widget in your Filament page comment page for a commenter:

```php
use Wsmallnews\Comment\Filament\Pages\Comment\Widgets\Comment as CommentWidget;

class ViewUser extends ViewRecord
{
    protected function getFooterWidgets(): array
    {
        return [
            CommentWidget::make([
                'scopeType' => 'default',
                'scopeId' => 0,
                'widgetType' => 'commenter',  // commentable = Commentable | commenter = Commenter
                'canAddComment' => true,
            ]),
        ];
    }
}
```

#### Widget Properties

| Property        | Description                           | Default                 |
| --------------- | ------------------------------------- | ----------------------- | ------------- |
| `scopeType`     | Scope type                            | `default`               |
| `scopeId`       | Scope ID                              | `0`                     |
| `widgetType`    | Widget type commentable = Commentable | commenter = Commenter   | `commentable` |
| `canAddComment` | Whether to allow adding comments      | `false`                 |
| `contained`     | Whether to contain the widget         | `true`                  |
| `commentStatus` | Comment status enum                   | `CommentStatus::Normal` |
| `contentType`   | Content type enum                     | `ContentType::Textarea` |
| `properties`    | Properties array                      | []                      |

## Livewire Component

### Comment List Component

```php
<livewire:sn-comment-components-comments
    scopeType="default"
    :scopeId="0"
    :commentable="$post"
    :properties="[
        'emptyLabel' => 'No comments',
        'emptyTipLabel' => 'Add your first comment'
    ]"
    :contentType="\Wsmallnews\Support\Enums\ContentType::Textarea"
    page-name="cp"
/>
```

| Property        | Description                                               | Default                 |
| --------------- | --------------------------------------------------------- | ----------------------- |
| `scopeType`     | Scope type                                                | `default`               |
| `scopeId`       | Scope ID                                                  | `0`                     |
| `canAddComment` | Whether to allow adding comments                          | `false`                 |
| `contained`     | Whether to contain the widget                             | `true`                  |
| `pageType`      | Page type scroll:Scroll,paginator:Paginator,manual:Manual | `scroll`                |
| `pageName`      | Page name                                                 | `page`                  |
| `perPage`       | Per page comments count                                   | `10`                    |
| `user`          | user instance                                             | `null`                  |
| `commentStatus` | Comment status enum                                       | `CommentStatus::Normal` |
| `contentType`   | Content type enum                                         | `ContentType::Textarea` |
| `properties`    | Properties array                                          | []                      |

The component will automatically handle:

- Comment pagination
- Nested reply expand/collapse
- Like count display
- Commenter avatar and nickname display

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [smallnews](https://github.com/Wsmallnews)
- [Wsmallnews/preference](https://github.com/wsmallnews/preference)
- [Wsmallnews/support](https://github.com/wsmallnews/support)
- [bezhansalleh/filament-plugin-essentials](https://github.com/bezhansalleh/filament-plugin-essentials)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
