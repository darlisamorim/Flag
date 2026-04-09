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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Se o avatar mudou, deleta o antigo do disco
        $oldAvatar = $this->record->avatar;
        $newAvatar = $data['avatar'] ?? null;

        if ($oldAvatar && $oldAvatar !== $newAvatar) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldAvatar);
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perfil salvo com sucesso!';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()->isAtLeastAdmin()),
        ];
    }
}
