<?php

namespace App\Filament\Resources\ApiTokens\Pages;

use App\Filament\Resources\ApiTokens\ApiTokenResource;
use App\Models\ApiToken;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateApiToken extends CreateRecord
{
    protected static string $resource = ApiTokenResource::class;

    protected function handleRecordCreation(array $data): ApiToken
    {
        // Generate the API token using the model's method
        $result = ApiToken::generateToken(
            user: null, // Public tokens not tied to users
            name: $data['name'],
            abilities: $data['abilities'] ?? ['read'],
            expiresAt: $data['expires_at'] ? \Carbon\Carbon::parse($data['expires_at']) : null,
            description: $data['description'] ?? null
        );

        // Show the plain text token to the user (only time it will be visible)
        Notification::make()
            ->title('API Token Created Successfully!')
            ->body("**Token:** {$result['plain_text_token']}\n\n**⚠️ Important:** Save this token securely. It will not be shown again!")
            ->success()
            ->persistent()
            ->send();

        return $result['token'];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
