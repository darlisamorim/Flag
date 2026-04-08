<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /**
     * Permite Admin+ acessar qualquer user,
     * e Editor acessar apenas o próprio perfil.
     */
    public static function canAccess(array $parameters = []): bool
    {
        $auth = Auth::user();

        if ($auth->isAtLeastAdmin()) {
            return true;
        }

        // Editor: só pode editar a si mesmo
        $record = $parameters['record'] ?? null;

        if (!$record) {
            return false;
        }

        // Filament pode passar o objeto User já resolvido ou apenas o ID
        $recordId = $record instanceof User ? $record->id : $record;

        return (int) $recordId === (int) $auth->id;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()->isAtLeastAdmin()),
        ];
    }
}
