<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class EditProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Meu Perfil';
    protected static ?string $title = 'Meu Perfil';
    protected static ?int $navigationSort = 99;
    protected static string $view = 'filament.pages.edit-profile';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(Auth::user()->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informações pessoais')
                    ->schema([
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Foto de perfil')
                            ->image()
                            ->avatar()
                            ->disk('public')
                            ->directory('avatars')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome completo')
                            ->required(),
                        Forms\Components\TextInput::make('title')
                            ->label('Cargo / título')
                            ->placeholder('Ex: Desenvolvedor Full Stack'),
                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone / WhatsApp'),
                        Forms\Components\TextInput::make('location')
                            ->label('Cidade, UF')
                            ->placeholder('Ex: São Paulo, SP'),
                        Forms\Components\Textarea::make('bio')
                            ->label('Bio / descrição')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('addr')
                            ->label('Endereço'),
                        Forms\Components\TextInput::make('district')
                            ->label('Bairro / Região'),
                        Forms\Components\TextInput::make('zip')
                            ->label('CEP'),
                        Forms\Components\TextInput::make('country')
                            ->label('País'),
                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ'),
                        Forms\Components\TextInput::make('ie')
                            ->label('Inscrição Estadual'),
                    ])->columns(2),

                Forms\Components\Section::make('Site e redes principais')
                    ->schema([
                        Forms\Components\TextInput::make('website')
                            ->label('Site pessoal')
                            ->url()
                            ->placeholder('https://'),
                        Forms\Components\TextInput::make('links')
                            ->label('Linktree / Links')
                            ->url()
                            ->placeholder('https://linktr.ee/'),
                        Forms\Components\TextInput::make('github')
                            ->label('GitHub')
                            ->url()
                            ->placeholder('https://github.com/'),
                        Forms\Components\TextInput::make('linkedin')
                            ->label('LinkedIn')
                            ->url()
                            ->placeholder('https://linkedin.com/in/'),
                        Forms\Components\TextInput::make('twitter')
                            ->label('Twitter / X')
                            ->url()
                            ->placeholder('https://x.com/'),
                        Forms\Components\TextInput::make('instagram')
                            ->label('Instagram')
                            ->url()
                            ->placeholder('https://instagram.com/'),
                        Forms\Components\TextInput::make('tiktok')
                            ->label('TikTok')
                            ->url()
                            ->placeholder('https://tiktok.com/@'),
                        Forms\Components\TextInput::make('youtube')
                            ->label('YouTube')
                            ->url()
                            ->placeholder('https://youtube.com/@'),
                        Forms\Components\TextInput::make('facebook')
                            ->label('Facebook')
                            ->url()
                            ->placeholder('https://facebook.com/'),
                    ])->columns(2),

                Forms\Components\Section::make('Outras redes')
                    ->schema([
                        Forms\Components\TextInput::make('medium')
                            ->label('Medium')
                            ->url()
                            ->placeholder('https://medium.com/'),
                        Forms\Components\TextInput::make('devto')
                            ->label('Dev.to')
                            ->url()
                            ->placeholder('https://dev.to/'),
                        Forms\Components\TextInput::make('codepen')
                            ->label('CodePen')
                            ->url()
                            ->placeholder('https://codepen.io/'),
                        Forms\Components\TextInput::make('behance')
                            ->label('Behance')
                            ->url()
                            ->placeholder('https://behance.net/'),
                        Forms\Components\TextInput::make('dribbble')
                            ->label('Dribbble')
                            ->url()
                            ->placeholder('https://dribbble.com/'),
                        Forms\Components\TextInput::make('deviantart')
                            ->label('DeviantArt')
                            ->url()
                            ->placeholder('https://deviantart.com/'),
                        Forms\Components\TextInput::make('pinterest')
                            ->label('Pinterest')
                            ->url()
                            ->placeholder('https://pinterest.com/'),
                    ])->columns(2),

                Forms\Components\Section::make('Alterar senha')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Senha atual')
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('new_password')
                            ->label('Nova senha')
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('new_password_confirmation')
                            ->label('Confirmar nova senha')
                            ->password()
                            ->revealable()
                            ->dehydrated(false),
                    ])->columns(3),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar alterações')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        if (!empty($data['current_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                Notification::make()
                    ->title('Senha atual incorreta!')
                    ->danger()
                    ->send();
                return;
            }
            if (!empty($data['new_password'])) {
                if ($data['new_password'] !== $data['new_password_confirmation']) {
                    Notification::make()
                        ->title('As senhas não coincidem!')
                        ->danger()
                        ->send();
                    return;
                }
                $data['password'] = Hash::make($data['new_password']);
            }
        }

        unset($data['current_password'], $data['new_password'], $data['new_password_confirmation']);

        $user->update($data);

        Notification::make()
            ->title('Perfil atualizado com sucesso!')
            ->success()
            ->send();
    }
}
