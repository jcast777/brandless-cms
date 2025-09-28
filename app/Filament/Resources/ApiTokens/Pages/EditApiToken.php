<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use App\Filament\Resources\ApiTokens\ApiTokenResource;
use App\Models\ApiToken;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditApiToken extends EditRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('regenerate')
                ->label('Regenerate Token')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Regenerate API Token')
                ->modalDescription('This will generate a new token and invalidate the current one. Make sure to update any applications using this token.')
                ->action(function () {
                    $record = $this->getRecord();
                    
                    // Generate new token
                    $plainTextToken = Str::random(64);
                    $hashedToken = hash('sha256', $plainTextToken);
                    
                    // Update the record
                    $record->update([
                        'token' => $hashedToken,
                        'usage_count' => 0,
                        'last_used_at' => null,
                    ]);
                    
                    // Show the new token to the user
                    Notification::make()
                        ->title('Token Regenerated Successfully!')
                        ->body("**New Token:** {$plainTextToken}\n\n**⚠️ Important:** Save this token securely. It will not be shown again!")
                        ->success()
                        ->persistent()
                        ->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Don't show the actual token value for security
        unset($data['token']);
        return $data;
    }
}
