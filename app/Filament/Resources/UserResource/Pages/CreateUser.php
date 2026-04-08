<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove campos de senha se vazios
        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Remove confirmação de senha — não é coluna no banco
        unset($data['password_confirmation']);
        unset($data['current_password']);

        return $data;
    }
}
