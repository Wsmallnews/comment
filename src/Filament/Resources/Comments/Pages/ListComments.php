<?php

namespace Wsmallnews\Comment\Filament\Resources\Comments\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;
use Wsmallnews\Comment\Enums\CommentStatus;
use Wsmallnews\Comment\Filament\Resources\Comments\CommentResource;
use Wsmallnews\Support\Filament\Resources\Concerns\Pages\Scopeable;

class ListComments extends ListRecords
{
    use Scopeable;

    protected static string $resource = CommentResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make()
                ->label(__('sn-comment::comment.comment_resource.tabs.all'))
                ->badge(fn (): int => $this->getCount()),
        ];

        foreach (CommentStatus::cases() as $status) {
            $tabs[$status->value] = Tab::make()
                ->label($status->getLabel())
                ->badge(fn (): int => $this->getCount($status))
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status));
        }

        return $tabs;
    }

    protected function getCount(?CommentStatus $status = null): int
    {
        $query = static::getResource()::getEloquentQuery();

        if ($status) {
            $query->where('status', $status);
        }

        return $query->count();
    }
}
