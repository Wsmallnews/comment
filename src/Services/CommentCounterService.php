<?php

namespace Wsmallnews\Comment\Services;

use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Models\Comment;
use Wsmallnews\Comment\Support\Utils;

class CommentCounterService
{
    /**
     * 创建评论后更新计数器
     */
    public static function afterCommentCreated(Comment $comment, ?Comment $parentComment, mixed $commentable): void
    {
        $isVisible = $comment->status === CommentStatus::Normal;

        // 父评论计数
        if ($parentComment) {
            // 确定上级，如果有 parent_id，就查上级，否者自己就是上级
            $parent = $parentComment->parent_id ? $parentComment->parent : $parentComment;

            if ($parent) {
                $parent->whereKey($parent->getKey())->incrementJson('counter->total_comment_num');
                if ($isVisible) {
                    $parent->whereKey($parent->getKey())->incrementJson('counter->comment_num');
                }
            }
        }

        // 评论主体计数（Post 等）
        if ($commentable) {
            $commentable->whereKey($commentable->getKey())->incrementJson('counter->total_comment_num');
            if ($isVisible) {
                $commentable->whereKey($commentable->getKey())->incrementJson('counter->comment_num');
            }
        }
    }

    /**
     * 删除评论后更新计数器
     *
     * 注意：调用此方法时 $comment 尚未被删除（需要获取 status 和 commentable）
     */
    public static function afterCommentDeleted(Comment $comment): void
    {
        $isVisible = $comment->status === CommentStatus::Normal;

        // 父评论计数
        if ($comment->parent_id) {
            $parent = Utils::getCommentModel()::find($comment->parent_id);
            if ($parent) {
                $parent->whereKey($parent->getKey())->decrementJson('counter->total_comment_num');
                if ($isVisible) {
                    $parent->whereKey($parent->getKey())->decrementJson('counter->comment_num');
                }
            }
        }

        // 评论主体计数
        if ($comment->commentable) {
            $comment->commentable->whereKey($comment->commentable->getKey())->decrementJson('counter->total_comment_num');
            if ($isVisible) {
                $comment->commentable->whereKey($comment->commentable->getKey())->decrementJson('counter->comment_num');
            }
        }
    }

    /**
     * 评论状态变更后更新计数器
     *
     * 覆盖全部 3×3 状态转换矩阵：
     *
     * | from \ to   | normal     | unaudited  | hidden     |
     * |-------------|------------|------------|------------|
     * | normal      | -          | c: -1      | c: -1      |
     * | unaudited   | c: +1      | -          | -          |
     * | hidden      | c: +1      | -          | -          |
     *
     * c = comment_num（可见计数），正号=increment，负号=decrement
     * total_comment_num 不受状态变更影响
     */
    public static function afterStatusChanged(Comment $comment, CommentStatus $oldStatus, CommentStatus $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $wasVisible = $oldStatus === CommentStatus::Normal;
        $isVisible = $newStatus === CommentStatus::Normal;

        // 可见性没变（unaudited↔hidden 等）→ 无需调整计数器
        if ($wasVisible === $isVisible) {
            return;
        }

        // 父评论
        if ($comment->parent_id) {
            $parent = Utils::getCommentModel()::find($comment->parent_id);
            if ($parent) {
                $isVisible 
                    ? $parent->whereKey($parent->getKey())->incrementJson('counter->comment_num')
                    : $parent->whereKey($parent->getKey())->decrementJson('counter->comment_num');
            }
        }

        // 评论主体
        if ($comment->commentable) {
            $isVisible
                ? $comment->commentable->whereKey($comment->commentable->getKey())->incrementJson('counter->comment_num')
                : $comment->commentable->whereKey($comment->commentable->getKey())->decrementJson('counter->comment_num');
        }
    }
}
