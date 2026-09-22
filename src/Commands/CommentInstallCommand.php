<?php

namespace Wsmallnews\Comment\Commands;

use Wsmallnews\Support\Commands\PackageInstallCommand;

class CommentInstallCommand extends PackageInstallCommand
{
    protected string $packageName = 'sn-comment';
}
