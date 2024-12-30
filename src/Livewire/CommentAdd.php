<?php

namespace Wsmallnews\Comment\Livewire;

use Filament\Forms\Components\RichEditor;
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

    public ?array $data = [];


    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {

        return $form
            ->schema([
                RichEditor::make('content')
                    ->hiddenLabel()
                    ->placeholder('留下你的精彩评论吧')
                    ->extraAttributes(['style' => 'box-shadow: none;'])
                    ->extraInputAttributes(['style' => 'min-height: 80px !important;'])
                    ->toolbarButtons([])
                    ->required()
                    ->columnSpan('full'),
            ])
            ->statePath('data');
    }


    public function create()
    {
        dd($this->form->getState());
    }


    public function render()
    {
        return view('sn-comment::livewire.comment-add');
    }
}
