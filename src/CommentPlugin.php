<?php

namespace Wsmallnews\Comment;

use BezhanSalleh\PluginEssentials\Concerns\Plugin as Essentials;
use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Icons\Heroicon;
use Wsmallnews\Comment\Filament\Pages\Comment\CommentPage;
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
        $panel->pages([
            CommentPage::class,
        ]);
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
            'resources' => [
                CommentPage::class => [
                    // hasLabels
                    'modelLabel' => __('sn-comment::comment.filament.comment.model_label'),
                    'pluralModelLabel' => __('sn-comment::comment.filament.comment.plural_model_label'),

                    // hasNavigation
                    'navigationLabel' => __('sn-comment::comment.filament.comment.navigation_label'),
                    'navigationIcon' => Heroicon::OutlinedChatBubbleLeft,
                    'activeNavigationIcon' => Heroicon::ChatBubbleLeft,
                    'navigationGroup' => __('sn-comment::comment.filament.comment.navigation_group'),
                    'navigationSort' => 1,
                    'navigationBadge' => null,
                    'navigationBadgeColor' => null,
                    'navigationParentItem' => null,
                    'registerNavigation' => true,

                    // hasGlobalSearch
                    'globallySearchable' => false,
                    'globalSearchResultsLimit' => 50,
                    'forceGlobalSearchCaseInsensitive' => null,
                    'splitGlobalSearchTerms' => false,

                    // belongsToParent
                    'parentResource' => null,

                    // HasCustomProperties
                    'customProperties' => [],
                ],
            ],
        ];
    }
}
