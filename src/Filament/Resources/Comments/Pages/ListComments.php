<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments\Pages;

use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Filament\Resources\Comments\CommentResource;
use Wsmallnews\Comment\Support\Utils;
use Wsmallnews\Support\Filament\Resources\Concerns\Pages\Scopeable;

class ListComments extends ListRecords
{
    use Scopeable;

    protected static string $resource = CommentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->label(__('sn-comment::comment.comment_resource.tabs.all'))
                ->badge(fn () => $this->getCount()),
            'normal' => Tab::make()
                ->label(CommentStatus::Normal->getLabel())
                ->badge(fn () => $this->getCount(CommentStatus::Normal))
                ->modifyQueryUsing(fn ($query) => $query->where('status', CommentStatus::Normal)),
            'unaudited' => Tab::make()
                ->label(CommentStatus::Unaudited->getLabel())
                ->badge(fn () => $this->getCount(CommentStatus::Unaudited))
                ->modifyQueryUsing(fn ($query) => $query->where('status', CommentStatus::Unaudited)),
            'hidden' => Tab::make()
                ->label(CommentStatus::Hidden->getLabel())
                ->badge(fn () => $this->getCount(CommentStatus::Hidden))
                ->modifyQueryUsing(fn ($query) => $query->where('status', CommentStatus::Hidden)),
            'rejected' => Tab::make()
                ->label(CommentStatus::Rejected->getLabel())
                ->badge(fn () => $this->getCount(CommentStatus::Rejected))
                ->modifyQueryUsing(fn ($query) => $query->where('status', CommentStatus::Rejected)),
        ];
    }

    protected function getCount(?CommentStatus $status = null): int
    {
        $query = Utils::getCommentModel()::query()->snScope(
            static::getScopeType(),
            static::getScopeId(),
        );

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
