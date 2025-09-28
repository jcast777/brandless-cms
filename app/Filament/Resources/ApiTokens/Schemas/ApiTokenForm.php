<?php

namespace App\Filament\Resources\ApiTokens\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ApiTokenForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Token Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Frontend API Access'),

                        Textarea::make('description')
                            ->maxLength(500)
                            ->placeholder('Optional description of what this token is used for')
                            ->rows(3),
                    ]),

                Section::make('Permissions & Expiration')
                    ->schema([
                        CheckboxList::make('abilities')
                            ->label('Token Abilities')
                            ->options([
                                'read' => 'Read - Can access GET endpoints',
                                'write' => 'Write - Can create and update content',
                                'delete' => 'Delete - Can delete content',
                                'admin' => 'Admin - Full administrative access (superadmin only)',
                            ])
                            ->default(['read'])
                            ->required()
                            ->columns(2)
                            ->helperText('Select the permissions this token should have'),

                        DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->placeholder('Leave empty for no expiration')
                            ->helperText('Token will automatically become invalid after this date')
                            ->minDate(now())
                            ->displayFormat('Y-m-d H:i'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive tokens cannot be used for authentication'),
                    ]),
            ]);
    }
}
