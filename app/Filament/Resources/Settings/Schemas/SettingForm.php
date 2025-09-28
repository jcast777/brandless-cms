<?php

namespace App\Filament\Resources\Settings\Schemas;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Setting Information')
                    ->schema([
                        Select::make('group')
                            ->label('Group')
                            ->options([
                                'general' => 'General',
                                'site' => 'Site',
                                'theme' => 'Theme',
                                'seo' => 'SEO',
                                'social' => 'Social Media',
                                'email' => 'Email',
                                'api' => 'API',
                                'security' => 'Security',
                                'performance' => 'Performance',
                                'analytics' => 'Analytics',
                            ])
                            ->default('general')
                            ->required()
                            ->searchable(),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->maxLength(255)
                            ->unique(Setting::class, 'key', ignoreRecord: true)
                            ->helperText('Unique identifier for this setting (e.g., site_name, logo_url)')
                            ->rules(['regex:/^[a-z0-9_]+$/']),

                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->helperText('Optional description of what this setting controls')
                            ->rows(2),
                    ])
                    ->columns(2),

                Section::make('Value Configuration')
                    ->schema([
                        Select::make('type')
                            ->label('Type')
                            ->options([
                                'text' => 'Text',
                                'textarea' => 'Textarea',
                                'boolean' => 'Boolean (True/False)',
                                'integer' => 'Integer',
                                'float' => 'Float',
                                'select' => 'Select (Dropdown)',
                                'json' => 'JSON',
                                'file' => 'File',
                                'url' => 'URL',
                                'email' => 'Email',
                                'color' => 'Color',
                            ])
                            ->default('text')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('value', null)),

                        TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => in_array($get('type'), ['text', 'url', 'email']))
                            ->maxLength(1000),

                        Textarea::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'textarea')
                            ->rows(4)
                            ->maxLength(5000),

                        Toggle::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'boolean')
                            ->inline(false),

                        TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'integer')
                            ->numeric()
                            ->integer(),

                        TextInput::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'float')
                            ->numeric(),

                        Select::make('value')
                            ->label('Value')
                            ->visible(fn ($get) => $get('type') === 'select')
                            ->options(fn ($get) => $get('options') ? array_combine($get('options'), $get('options')) : [])
                            ->searchable(),

                        Textarea::make('value')
                            ->label('JSON Value')
                            ->visible(fn ($get) => $get('type') === 'json')
                            ->rows(6)
                            ->helperText('Enter valid JSON format'),

                        FileUpload::make('value')
                            ->label('File')
                            ->visible(fn ($get) => $get('type') === 'file')
                            ->disk('public')
                            ->directory('settings'),

                        ColorPicker::make('value')
                            ->label('Color')
                            ->visible(fn ($get) => $get('type') === 'color'),

                        KeyValue::make('options')
                            ->label('Options')
                            ->visible(fn ($get) => $get('type') === 'select')
                            ->helperText('Define the available options for select type')
                            ->keyLabel('Option Value')
                            ->valueLabel('Option Label'),
                    ]),

                Section::make('Display Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first'),

                        Toggle::make('is_public')
                            ->label('Public Setting')
                            ->helperText('Public settings are accessible via API without authentication')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }
}
