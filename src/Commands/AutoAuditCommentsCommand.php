<?php

namespace Wsmallnews\Comment\Commands;

use Illuminate\Console\Command;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Services\CommentCounterService;
use Wsmallnews\Comment\Support\Utils;

class AutoAuditCommentsCommand extends Command
{
    protected $signature = 'sn-comment:auto-audit';

    protected $description = 'Auto audit comments created since last run';

    public function handle(): int
    {
        $lastProcessedId = (int) cache('sn_comment_auto_audit_last_id', 0);

        $comments = Utils::getCommentModel()::query()
            ->when($lastProcessedId > 0, fn ($q) => $q->where('id', '>', $lastProcessedId))
            ->where('status', CommentStatus::Unaudited)
            ->orderBy('id')
            ->get();

        $maxId = $lastProcessedId;

        foreach ($comments as $comment) {
            $oldStatus = $comment->status;
            $comment->update(['status' => CommentStatus::Normal]);
            CommentCounterService::afterStatusChanged($comment, $oldStatus, CommentStatus::Normal);

            $maxId = max($maxId, $comment->getKey());
        }

        if ($maxId > $lastProcessedId) {
            cache()->put('sn_comment_auto_audit_last_id', $maxId);
        }

        $this->info("Auto audited {$comments->count()} comments. Last ID: {$maxId}");

        return self::SUCCESS;
    }
}
