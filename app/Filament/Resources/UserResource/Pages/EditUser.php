<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $auth    = Auth::user();
        $editing = $this->record;

        // Admin não pode editar Super Admin ou outro Admin
        if ($auth->isAdmin() && ($editing->isSuperAdmin() || $editing->isAdmin())) {
            abort(403, 'Sem permissão para editar este usuário.');
        }

        // Editor não acessa nada aqui
        if ($auth->isEditor()) {
            abort(403, 'Sem permissão.');
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('edit', ['record' => $this->record]);
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('savePassword')
                ->label('Salvar senha')
                ->action('savePassword')
                ->color('primary'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function savePassword(): void
    {
        $data    = $this->data;
        $isSelf  = $this->record->id === Auth::id();

        if ($isSelf) {
            if (empty($data['current_password'])) {
                Notification::make()->title('Informe a senha atual!')->warning()->send();
                return;
            }
            if (!Hash::check($data['current_password'], $this->record->password)) {
                Notification::make()->title('Senha atual incorreta!')->danger()->send();
                return;
            }
        }

        if (empty($data['password'])) {
            Notification::make()->title('Informe a nova senha!')->warning()->send();
            return;
        }

        if ($data['password'] !== $data['password_confirmation']) {
            Notification::make()->title('As senhas não coincidem!')->danger()->send();
            return;
        }

        $this->record->update(['password' => Hash::make($data['password'])]);
        Notification::make()->title('Senha alterada com sucesso!')->success()->send();
    }
}
