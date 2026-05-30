<?php

namespace Wsmallnews\Comment;

use BezhanSalleh\PluginEssentials\Concerns\Plugin as Essentials;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Comment\Filament\Pages\Comment\CommentPage;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Concerns\Plugin\HasCustomProperties;

class CommentPlugin implements Plugin
{
    use Essentials\BelongsToParent;
    use Essentials\BelongsToTenant;
    use Essentials\HasGlobalSearch;
    use Essentials\HasLabels;
    use Essentials\HasNavigation;
    use Essentials\HasPluginDefaults;
    use Essentials\WithMultipleResourceSupport;
    use EvaluatesClosures;
    use HasCustomProperties;

    public function getId(): string
    {
        return 'sn-comment';
    }

    public function register(Panel $panel): void
    {
        if (Utils::getPanelRegister('pages')) {
            $panel->pages([
                ...Utils::getPanelRegister('pages'),
            ]);
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

    /**
     * 资源默认值
     */
    protected function getPluginDefaults(): array
    {
        return [
            'navigationGroup' => fn () => __('sn-comment::comment.global_default.navigation_group'),
            'globallySearchable' => false,
            'globalSearchResultsLimit' => 25,

            'resources' => [
                CommentPage::class => [
                    'modelLabel' => fn () => __('sn-comment::comment.comment_page.model_label'),
                    'pluralModelLabel' => fn () => __('sn-comment::comment.comment_page.plural_model_label'),

                    'navigationLabel' => fn () => __('sn-comment::comment.comment_page.navigation_label'),
                    'navigationIcon' => Heroicon::OutlinedChatBubbleLeft,
                    'activeNavigationIcon' => Heroicon::ChatBubbleLeft,
                    'navigationSort' => 1,
                ],
            ],
        ];
    }
}
