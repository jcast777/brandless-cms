<?php

namespace App\Filament\Resources\Settings\Tables;

use App\Models\Setting;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Group')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                TextColumn::make('value')
                    ->label('Value')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->formatStateUsing(function ($state, $record) {
                        return match ($record->type) {
                            'boolean' => $state ? '✓ True' : '✗ False',
                            'json' => 'JSON Data',
                            'file' => $state ? 'File: ' . basename($state) : 'No file',
                            default => $state,
                        };
                    }),

                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'boolean' => 'success',
                        'json' => 'warning',
                        'file' => 'info',
                        default => 'gray',
                    }),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->trueIcon('heroicon-o-globe-alt')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Group')
                    ->options(fn () => Setting::distinct('group')->pluck('group', 'group')->toArray()),

                SelectFilter::make('type')
                    ->label('Type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'boolean' => 'Boolean',
                        'integer' => 'Integer',
                        'float' => 'Float',
                        'select' => 'Select',
                        'json' => 'JSON',
                        'file' => 'File',
                        'url' => 'URL',
                        'email' => 'Email',
                        'color' => 'Color',
                    ]),

                TernaryFilter::make('is_public')
                    ->label('Public Settings')
                    ->placeholder('All settings')
                    ->trueLabel('Public only')
                    ->falseLabel('Private only'),
            ])
            ->actions([
                Action::make('clear_cache')
                    ->label('Clear Cache')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->action(function (Setting $record) {
                        Cache::forget("setting.{$record->group}.{$record->key}");
                        Cache::forget("settings.group.{$record->group}");
                        Cache::forget('settings.public');
                        
                        Notification::make()
                            ->title('Cache Cleared')
                            ->body("Cache cleared for setting: {$record->key}")
                            ->success()
                            ->send();
                    }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    BulkAction::make('clear_all_cache')
                        ->label('Clear All Cache')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function () {
                            Cache::flush();
                            
                            Notification::make()
                                ->title('All Cache Cleared')
                                ->body('All settings cache has been cleared')
                                ->success()
                                ->send();
                        }),

                    BulkAction::make('make_public')
                        ->label('Make Public')
                        ->icon('heroicon-o-globe-alt')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_public' => true])),

                    BulkAction::make('make_private')
                        ->label('Make Private')
                        ->icon('heroicon-o-lock-closed')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_public' => false])),

                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('group')
            ->groups([
                Group::make('group')
                    ->label('Group')
                    ->collapsible(),
            ]);
    }
}
