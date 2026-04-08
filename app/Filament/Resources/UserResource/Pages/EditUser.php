<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
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
        $actions = [];

        // Botão Reset .env — só para Super Admin editando o próprio perfil
        if (Auth::user()->isSuperAdmin() && (int) Auth::id() === (int) $this->record->id) {
            $actions[] = Actions\Action::make('resetFromEnv')
                ->label('Resetar do .env')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Resetar dados do .env?')
                ->modalDescription('Isso vai sobrescrever os dados do perfil com as informações do arquivo .env. A senha NÃO será alterada. Continuar?')
                ->modalSubmitActionLabel('Sim, resetar')
                ->action(function () {
                    Artisan::call('db:seed', ['--class' => 'UserProfileSeeder', '--force' => true]);
                    Notification::make()
                        ->title('Dados resetados do .env com sucesso!')
                        ->success()
                        ->send();
                });
        }

        // Botão excluir — não aparece para Super Admin
        if (!$this->record->isSuperAdmin()) {
            $actions[] = Actions\DeleteAction::make();
        }

        return $actions;
    }

    public function savePassword(): void
    {
        $data   = $this->data;
        $isSelf = (int) $this->record->id === (int) Auth::id();

        // Pede senha atual apenas quando edita o próprio perfil
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
