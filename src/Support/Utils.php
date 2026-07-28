<?php

declare(strict_types=1);

namespace Wsmallnews\Comment\Support;

use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Exceptions\CommentException;
use Wsmallnews\Support\Data\ScopeableContext;
use Wsmallnews\Support\Enums\ContentType;
use Wsmallnews\Support\Exceptions\InvalidScopeException;
use Wsmallnews\Support\Support\Utils as SupportUtils;

/**
 * Utility class for Comment package configuration and helpers.
 */
class Utils
{
    /**
     * Get configuration value.
     *
     * @param  string|null  $name  Configuration key (dot notation)
     * @param  mixed  $default  Default value if not found
     */
    public static function getConfig(?string $name = null, mixed $default = null): mixed
    {
        $config = config('sn-comment');

        return $name ? (data_get($config, $name) ?? $default) : $config;
    }

    /**
     * Get scopeable configuration as ScopeableContext object.
     *
     * @throws CommentException
     */
    public static function getScopeableContext(): ScopeableContext
    {
        try {
            return SupportUtils::getScopeFromConfig('sn-comment.scopeable');
        } catch (InvalidScopeException $e) {
            throw new CommentException('Scopeable configuration error. ' . $e->getMessage());
        }
    }

    /**
     * Get scopeable array (legacy method for backward compatibility).
     *
     * @return array{scope_type: string, scope_id: int}
     *
     * @throws CommentException
     */
    public static function getScopeable(): array
    {
        return self::getScopeableContext()->toArray();
    }

    /**
     * Get scope type.
     *
     * @throws CommentException
     */
    public static function getScopeType(): string
    {
        return self::getScopeableContext()->scopeType;
    }

    /**
     * Get scope ID.
     *
     * @throws CommentException
     */
    public static function getScopeId(): int
    {
        return self::getScopeableContext()->scopeId;
    }

    /**
     * Get default contentType.
     */
    public static function getDefaultContentType(): ContentType
    {
        return self::getConfig('default_content_type') ?? ContentType::Textarea;
    }

    /**
     * Get default comment status.
     */
    public static function getDefaultCommentStatus(): CommentStatus
    {
        return self::getConfig('default_status') ?? CommentStatus::Normal;
    }

    /**
     * Get panel register raw config.
     *
     * @param  string  $type  Register type (pages or resources)
     */
    public static function getPanelRegister(?string $type = 'pages'): mixed
    {
        if (blank($type)) {
            return self::getConfig('panel_register', null);
        }

        return self::getConfig("panel_register.$type", null);
    }

    /**
     * Get model class by name.
     *
     * @param  string  $name  Model name (e.g., 'post', 'navigation')
     * @param  bool  $shouldException  Whether to throw exception if not found
     *
     * @throws CommentException
     */
    public static function getModel(string $name, bool $shouldException = true): ?string
    {
        $model = self::getConfig('models')[$name] ?? null;

        if (blank($model) && $shouldException) {
            throw new CommentException("Model {$name} not found.");
        }

        return $model;
    }

    /**
     * Get Comment model class.
     *
     * @return string Models\Comment
     */
    public static function getCommentModel(): string
    {
        return self::getModel('comment');
    }

    /**
     * Get Comment Content model class.
     *
     * @return string Models\CommentContent
     */
    public static function getCommentContentModel(): string
    {
        return self::getModel('comment_content');
    }

    /**
     * Get file directory path with optional type and date.
     *
     * @param  string|null  $type  Directory type
     */
    public static function getFileDirectory(?string $type = null): string
    {
        return self::getConfig('file_directory', 'sn/comment/') . ($type ? $type . '/' : '') . date('Ymd');
    }
}
