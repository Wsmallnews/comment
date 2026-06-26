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
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Wsmallnews\Comment\Commands\AutoAuditCommentsCommand;
use Wsmallnews\Comment\Commands\CommentInstallCommand;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Helpers\ScheduleHelper;

class CommentServiceProvider extends PackageServiceProvider
{
    public static string $name = 'sn-comment';

    public static string $viewNamespace = 'sn-comment';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasConfigFile()
            ->hasMigrations($this->getMigrations())
            ->hasTranslations()
            ->hasViews(static::$viewNamespace);
    }

    public function packageRegistered(): void {}

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

        // 注册 livewire 命名空间（自动发现 src/Livewire/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-comment',
            classNamespace: 'Wsmallnews\\Comment\\Livewire'
        );
        // 注册 Filament 命名空间下 comment 组件（自动发现 src/Filament/Pages/comment/Components/ 下的组件）
        Livewire::addNamespace(
            namespace: 'sn-comment-fi-comment-components',
            classNamespace: 'Wsmallnews\\Comment\\Filament\\Pages\\Comment\\Components'
        );

        // 自动审核定时任务（需在配置中开启）
        $auditConfig = Utils::getConfig('schedule_auto_audit');
        if (is_array($auditConfig) && ($auditConfig['enabled'] ?? false)) {
            $this->callAfterResolving(Schedule::class, function (Schedule $schedule) use ($auditConfig) {
                $task = $schedule->command('sn-comment:auto-audit');
                ScheduleHelper::configure($task, $auditConfig);
            });
        }
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
            AutoAuditCommentsCommand::class,
            CommentInstallCommand::class,
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
            'create_sn_comments_table',
            'create_sn_comment_contents_table',
        ];
    }
}
