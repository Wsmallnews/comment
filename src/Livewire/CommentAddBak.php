<?php

namespace Wsmallnews\Comment\Livewire;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Livewire\Component;

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
                    ]),
            ])
            ->statePath('data');
    }

    public function render()
    {
        return view('sn-comment::livewire.comment-add');
    }
}
