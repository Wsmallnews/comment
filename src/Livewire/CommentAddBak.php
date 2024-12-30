<?php

namespace Wsmallnews\Comment\Livewire;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Attributes\Renderless;
use Livewire\Component;
use Wsmallnews\Comment\Models\Comment;
use Filament\Forms\Form;

class CommentAdd extends Component implements HasForms
{

    use InteractsWithForms;


    public function form(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('content')
                ->hiddenLabel()
                ->placeholder('留下你的精彩评论吧')
                // ->markAsRequired(false)
                ->required(),

            ])
            ->statePath('data');

        return $form
            ->schema([
                Section::make('Rate limiting')
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->schema([
                        TextInput::make('content')->required(),
                    ])
            ])
            ->statePath('data');
    }


    public function render()
    {
        return view('sn-comment::livewire.comment-add');
    }
}
