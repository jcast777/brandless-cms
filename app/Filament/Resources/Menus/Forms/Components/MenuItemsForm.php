<?php

namespace App\Filament\Resources\Menus\Forms\Components;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class MenuItemsForm extends Repeater
{
  protected string $view = 'filament.forms.components.menu-items-form';

  protected function setUp(): void
  {
    parent::setUp();

    $this->schema([
      TextInput::make('title')
        ->required(),
      TextInput::make('url')
        ->required()
        ->url(),
      Select::make('target')
        ->options([
          '_self' => 'Same Tab',
          '_blank' => 'New Tab',
        ])
        ->default('_self'),
      TextInput::make('css_class')
        ->label('CSS Class'),
      Toggle::make('is_active')
        ->default(true),
    ]);
  }
}
