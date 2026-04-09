<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
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
            Action::make('resetFromEnv')
                ->label('Resetar do .env')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Resetar perfil do .env')
                ->modalDescription('Os dados do perfil serão substituídos pelos valores do .env. A senha NÃO será alterada.')
                ->modalSubmitActionLabel('Sim, resetar')
                ->visible(fn () => Auth::user()->isSuperAdmin() && (int) $this->record->id === (int) Auth::id())
                ->action(function () {
                    $social = fn (string $urlKey, string $handleKey): ?string =>
                        env($urlKey) && env($handleKey) ? env($urlKey) . env($handleKey) : null;

                    $this->record->update([
                        'name'          => env('DAA_NAME'),
                        'email'         => env('DAA_EMAIL'),
                        'phone'         => env('DAA_PHONE'),
                        'title'         => env('DAA_OFFICE'),
                        'role'          => env('DAA_ROLE'),
                        'subname'       => env('DAA_SUBNAME'),
                        'bio'           => env('DAA_DESCRIPTION'),
                        'addr'          => env('DAA_ADDR'),
                        'district'      => env('DAA_DISTRICT'),
                        'location'      => env('DAA_CITY') . ', ' . env('DAA_UF'),
                        'zip'           => env('DAA_ZIP'),
                        'country'       => env('DAA_COUNTRY'),
                        'cnpj'          => env('DAA_CNPJ'),
                        'ie'            => env('DAA_IE'),
                        'razao_social'  => env('DAA_RAZAO_SOCIAL'),
                        'nome_fantasia' => env('DAA_NOME_FANTASMA'),
                        'links'         => $social('DAA_LINKS_URL', 'DAA_LINKS'),
                        'github'        => $social('DAA_GITHUB_URL', 'DAA_GITHUB'),
                        'linkedin'      => $social('DAA_LINKEDIN_URL', 'DAA_LINKEDIN'),
                        'twitter'       => $social('DAA_TWITTER_URL', 'DAA_TWITTER'),
                        'instagram'     => $social('DAA_INSTAGRAM_URL', 'DAA_INSTAGRAM'),
                        'tiktok'        => $social('DAA_TIKTOK_URL', 'DAA_TIKTOK'),
                        'youtube'       => $social('DAA_YOUTUBE_URL', 'DAA_YOUTUBE'),
                        'facebook'      => $social('DAA_FB_URL', 'DAA_FB'),
                        'fb_page'       => $social('DAA_FB_PAGE_URL', 'DAA_FB_PAGE'),
                        'medium'        => $social('DAA_MEDIUM_URL', 'DAA_MEDIUM'),
                        'devto'         => $social('DAA_DEVTO_URL', 'DAA_DEVTO'),
                        'codepen'       => $social('DAA_CODEPEN_URL', 'DAA_CODEPEN'),
                        'behance'       => $social('DAA_BEHANCE_URL', 'DAA_BEHANCE'),
                        'dribbble'      => $social('DAA_DRIBBBLE_URL', 'DAA_DRIBBBLE'),
                        'deviantart'    => $social('DAA_DEVIANTART_URL', 'DAA_DEVIANTART'),
                        'pinterest'     => $social('DAA_PINTEREST_URL', 'DAA_PINTEREST'),
                    ]);

                    $this->fillForm();

                    Notification::make()
                        ->title('Perfil resetado com sucesso!')
                        ->success()
                        ->send();
                }),

            Actions\DeleteAction::make()
                ->visible(fn () => Auth::user()->isAtLeastAdmin()),
        ];
    }
}
