<?php

namespace Wsmallnews\Comment;

use BadMethodCallException;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Filament\Concerns\RegistersConfigurable;

class CommentPlugin implements Plugin
{
    use RegistersConfigurable;

    public function getId(): string
    {
        return 'sn-comment';
    }

    public function register(Panel $panel): void
    {
        // 注册 resources
        $resources = Utils::getPanelRegister('resources', true);
        if ($resources) {
            $panel->resources([...$resources]);
        }
        $configurableResources = $this->getConfigurableResources();
        if ($configurableResources) {
            $panel->resources([...$configurableResources]);
        }

        // 注册 pages
        $pages = Utils::getPanelRegister('pages', true);
        if ($pages) {
            $panel->pages([...$pages]);
        }
        $configurablePages = $this->getConfigurablePages();
        if ($configurablePages) {
            $panel->pages([...$configurablePages]);
        }
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament(app(static::class)->getId());

        return $plugin;
    }

    public function __call(string $method, array $arguments): mixed
    {
        if (method_exists(Utils::class, $method)) {
            return Utils::$method(...$arguments);
        }

        throw new BadMethodCallException("Method {$method} does not exist on CommentPlugin");
    }
}
