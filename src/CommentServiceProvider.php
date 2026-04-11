<?php

namespace Wsmallnews\Comment;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Wsmallnews\Comment\Commands\CommentCommand;
use Wsmallnews\Comment\Livewire\Components\Comments as ComponentsComments;
use Wsmallnews\Comment\Livewire\Components\Comment as ComponentsComment;
use Wsmallnews\Comment\Support\Utils;

class CommentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-comment';

    public static string $viewNamespace = 'sn-comment';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews(static::$viewNamespace)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('wsmallnews/comment');
            });

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
            $package->runsMigrations();
        }
    }

    public function packageRegistered(): void
    {

    }

    public function packageBooted(): void
    {
        // 注册模型别名
        Relation::enforceMorphMap([
            'sn-comment' => Utils::getCommentModel(),
        ]);

        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/comment/{$file->getFilename()}"),
                ], 'comment-stubs');
            }
        }

        // 注册 livewire 组件
        Livewire::component('sn-comment-components-comments', ComponentsComments::class);
        Livewire::component('sn-comment-components-comment', ComponentsComment::class);

    }

    protected function getAssetPackageName(): ?string
    {
        return 'wsmallnews/comment';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('comment', __DIR__ . '/../resources/dist/components/comment.js'),
            // Css::make('comment-styles', __DIR__ . '/../resources/dist/comment.css'),
            // Js::make('comment-scripts', __DIR__ . '/../resources/dist/comment.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            CommentCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            '2026_04_07_150952_create_sn_comments_table',
        ];
    }
}
