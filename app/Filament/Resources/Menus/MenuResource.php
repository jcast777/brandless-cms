<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Filament\Resources\Menus\Pages\ViewMenu;
use App\Models\Menu;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Bars4;
    protected static ?string $modelLabel = 'Menu';
    protected static ?string $navigationLabel = 'Menus';

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Menu Details')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    Forms\Components\TextInput::make('location')
                        ->maxLength(255),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Menu Items')
                ->schema([
                    Forms\Components\Repeater::make('menuItems')
                        ->relationship()
                        ->schema([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('url')
                                ->required()
                                ->url()
                                ->maxLength(255),
                            Forms\Components\Select::make('target')
                                ->options([
                                    '_self' => 'Same Tab',
                                    '_blank' => 'New Tab',
                                ])
                                ->default('_self'),
                            Forms\Components\TextInput::make('css_class')
                                ->label('CSS Class')
                                ->maxLength(255),
                            Forms\Components\Toggle::make('is_active')
                                ->default(true),
                        ])
                        ->itemLabel(fn(array $state): ?string => $state['title'] ?? null)
                        ->defaultItems(0)
                        ->collapsible()
                        ->cloneable()
                        ->reorderable()
                        ->columns(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'view' => ViewMenu::route('/{record}'),
            'edit' => EditMenu::route('/{record}/edit'),
        ];
    }
}
