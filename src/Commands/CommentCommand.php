<?php

namespace Wsmallnews\Comment\Commands;

use Illuminate\Console\Command;

class CommentCommand extends Command
{
    public $signature = 'comment';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
