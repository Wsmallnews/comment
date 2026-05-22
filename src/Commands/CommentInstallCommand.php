<?php

namespace Wsmallnews\Comment\Commands;

use Illuminate\Support\Facades\Artisan;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Wsmallnews\Support\Concerns\Install\ThirdPartyPublishes;

class CommentInstallCommand extends InstallCommand
{
    use ThirdPartyPublishes;

    public function __construct(Package $package)
    {
        $package->name('sn-comment');

        parent::__construct($package);

        $this->signature = 'sn-comment:install
                            {--no-deps : Install without dependencies and skip interactive prompts}';
        $this->description = 'Install sn-comment';
        $this->hidden = false;

        $this->configureUsingFluentDefinition();
        $this->specifyParameters();

        $this->publishConfigFile();
        $this->publishMigrations();
        $this->askToRunMigrations();
        $this->askToStarRepoOnGitHub('wsmallnews/comment');
    }

    public function handle()
    {
        $noDeps = $this->option('no-deps');
        $isDependency = ! $this->input->isInteractive();

        if ($noDeps || $isDependency) {
            $this->askToRunMigrations = false;
            $this->starRepo = null;
        }

        if (! $noDeps) {
            // 安装 wsmallnews/support
            $this->comment('Installing dependency: wsmallnews/support');
            $this->comment(str_repeat('─', 46));

            Artisan::call('sn-support:install', [
                '--no-interaction' => true,
            ], $this->getOutput());

            $this->newLine();

            // 安装 wsmallnews/preference
            $this->comment('Installing dependency: wsmallnews/preference');
            $this->comment(str_repeat('─', 46));

            Artisan::call('sn-preference:install', [
                '--no-deps' => true,
                '--no-interaction' => true,
            ], $this->getOutput());

            $this->newLine();
        }

        parent::handle();

        return self::SUCCESS;
    }
}
