<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Usuários';
    protected static ?string $modelLabel = 'Usuário';
    protected static ?string $pluralModelLabel = 'Usuários';
    protected static ?int $navigationSort = 98;

    public static function canAccess(): bool
    {
        return Auth::user()?->isAtLeastAdmin() ?? false;
    }

    private static function autoSave(string $field, mixed $state, ?User $record): void
    {
        if (!$record) return;
        $record->update([$field => $state]);
        Notification::make()->title('Salvo!')->success()->duration(1500)->send();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                // ─── PERFIL E ACESSO ──────────────────────────────────────
                Forms\Components\Section::make('Perfil e acesso')
                    ->schema([
                        Forms\Components\Placeholder::make('avatar_preview')
                            ->label('Foto atual')
                            ->content(fn (?User $record) => $record?->avatar
                                ? new \Illuminate\Support\HtmlString('<img src="' . asset('storage/' . $record->avatar) . '" style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid #444;">')
                                : new \Illuminate\Support\HtmlString('<span style="color:#666">Nenhuma foto</span>')
                            ),

                        // Etiqueta para Super Admin
                        Forms\Components\Placeholder::make('access_level_label')
                            ->label('Nível de acesso')
                            ->content(fn (?User $record) => match($record?->access_level) {
                                'super_admin' => new \Illuminate\Support\HtmlString('<span style="background:#7f1d1d;color:#fca5a5;padding:4px 12px;border-radius:9999px;font-size:13px;">Super Admin</span>'),
                                'admin'       => new \Illuminate\Support\HtmlString('<span style="background:#1e3a5f;color:#93c5fd;padding:4px 12px;border-radius:9999px;font-size:13px;">Admin</span>'),
                                default       => new \Illuminate\Support\HtmlString('<span style="background:#3b2f00;color:#fcd34d;padding:4px 12px;border-radius:9999px;font-size:13px;">Editor</span>'),
                            })
                            ->visible(fn (?User $record) => $record?->isSuperAdmin() ?? false),

                        // Dropdown para não Super Admin
                        Forms\Components\Select::make('access_level')
                            ->label('Nível de acesso')
                            ->options(function (?User $record) {
                                $auth = Auth::user();
                                if ($auth->isSuperAdmin()) {
                                    return ['admin' => 'Admin', 'editor' => 'Editor'];
                                }
                                return ['editor' => 'Editor'];
                            })
                            ->default('editor')
                            ->required()
                            ->visible(fn (?User $record) => !($record?->isSuperAdmin() ?? false))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('access_level', $state, $record)),

                        Forms\Components\FileUpload::make('avatar')
                            ->label('Alterar foto de perfil')
                            ->image()->avatar()
                            ->disk('public')->directory('avatars')
                            ->visibility('public')->maxSize(2048)
                            ->live()
                            ->afterStateUpdated(function ($state, ?User $record) {
                                if (empty($state) || !$record) return;
                                $uploadedFile = basename($state);
                                $currentFile  = $record->avatar ? basename($record->avatar) : null;
                                if ($uploadedFile === $currentFile) return;
                                $slug    = Str::slug($record->name);
                                $ext     = pathinfo($uploadedFile, PATHINFO_EXTENSION) ?: 'png';
                                $newName = $slug . '.' . $ext;
                                Storage::disk('public')->move('avatars/' . $uploadedFile, 'avatars/' . $newName);
                                if ($currentFile && $currentFile !== $newName) Storage::disk('public')->delete('avatars/' . $currentFile);
                                $record->update(['avatar' => 'avatars/' . $newName]);
                                Notification::make()->title('Foto atualizada!')->success()->duration(2000)->send();
                            })
                            ->columnSpan(2),
                    ])->columns(2),

                // ─── INFORMAÇÕES PESSOAIS ─────────────────────────────────
                Forms\Components\Section::make('Informações pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('name')->label('Nome completo')->required()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('name', $state, $record)),
                        Forms\Components\DatePicker::make('birthdate')->label('Data de nascimento')->displayFormat('d/m/Y')->maxDate(now()->subYears(1))
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('birthdate', $state, $record)),
                        Forms\Components\TextInput::make('email')->label('E-mail')->email()->required()->unique(ignoreRecord: true)
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('email', $state, $record)),
                        Forms\Components\TextInput::make('phone')->label('Telefone / WhatsApp')->mask('+55 (99) 999-999-999')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('phone', $state, $record)),
                        Forms\Components\TextInput::make('title')->label('Cargo / título')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('title', $state, $record)),
                        Forms\Components\TextInput::make('role')->label('Função técnica')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('role', $state, $record)),
                        Forms\Components\TextInput::make('subname')->label('Tagline / subtítulo')->columnSpanFull()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('subname', $state, $record)),
                        Forms\Components\Textarea::make('bio')->label('Bio / descrição')->rows(4)->columnSpanFull()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('bio', $state, $record)),
                    ])->columns(2),

                // ─── ENDEREÇO ─────────────────────────────────────────────
                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('addr')->label('Endereço')->columnSpan(2)
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('addr', $state, $record)),
                        Forms\Components\TextInput::make('district')->label('Bairro / Região')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('district', $state, $record)),
                        Forms\Components\TextInput::make('location')->label('Cidade, UF')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('location', $state, $record)),
                        Forms\Components\TextInput::make('zip')->label('CEP')->mask('99999-999')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('zip', $state, $record)),
                        Forms\Components\TextInput::make('country')->label('País')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('country', $state, $record)),
                    ])->columns(2),

                // ─── DADOS JURÍDICOS ──────────────────────────────────────
                Forms\Components\Section::make('Dados jurídicos')
                    ->schema([
                        Forms\Components\TextInput::make('razao_social')->label('Razão social')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('razao_social', $state, $record)),
                        Forms\Components\TextInput::make('nome_fantasia')->label('Nome fantasia')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('nome_fantasia', $state, $record)),
                        Forms\Components\TextInput::make('cnpj')->label('CNPJ')->mask('99.999.999/9999-99')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('cnpj', $state, $record)),
                        Forms\Components\TextInput::make('ie')->label('Inscrição Estadual')->mask('999.999.999.999')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('ie', $state, $record)),
                    ])->columns(2),

                // ─── SITE E REDES PRINCIPAIS ──────────────────────────────
                Forms\Components\Section::make('Site e redes principais')
                    ->schema([
                        Forms\Components\TextInput::make('website')->label('Site pessoal')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('website', $state, $record)),
                        Forms\Components\TextInput::make('links')->label('Linktree / Links')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('links', $state, $record)),
                        Forms\Components\TextInput::make('github')->label('GitHub')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('github', $state, $record)),
                        Forms\Components\TextInput::make('linkedin')->label('LinkedIn')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('linkedin', $state, $record)),
                        Forms\Components\TextInput::make('twitter')->label('Twitter / X')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('twitter', $state, $record)),
                        Forms\Components\TextInput::make('instagram')->label('Instagram')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('instagram', $state, $record)),
                        Forms\Components\TextInput::make('tiktok')->label('TikTok')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('tiktok', $state, $record)),
                        Forms\Components\TextInput::make('youtube')->label('YouTube')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('youtube', $state, $record)),
                        Forms\Components\TextInput::make('facebook')->label('Facebook (perfil)')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('facebook', $state, $record)),
                        Forms\Components\TextInput::make('fb_page')->label('Facebook (página)')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('fb_page', $state, $record)),
                    ])->columns(2),

                // ─── OUTRAS REDES ─────────────────────────────────────────
                Forms\Components\Section::make('Outras redes')
                    ->schema([
                        Forms\Components\TextInput::make('medium')->label('Medium')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('medium', $state, $record)),
                        Forms\Components\TextInput::make('devto')->label('Dev.to')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('devto', $state, $record)),
                        Forms\Components\TextInput::make('codepen')->label('CodePen')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('codepen', $state, $record)),
                        Forms\Components\TextInput::make('behance')->label('Behance')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('behance', $state, $record)),
                        Forms\Components\TextInput::make('dribbble')->label('Dribbble')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('dribbble', $state, $record)),
                        Forms\Components\TextInput::make('deviantart')->label('DeviantArt')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('deviantart', $state, $record)),
                        Forms\Components\TextInput::make('pinterest')->label('Pinterest')->url()
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('pinterest', $state, $record)),
                    ])->columns(2),

                // ─── ALTERAR SENHA ────────────────────────────────────────
                Forms\Components\Section::make('Alterar senha')
                    ->schema([
                        // Senha atual — aparece só quando edita o próprio perfil
                        Forms\Components\TextInput::make('current_password')
                            ->label('Senha atual')->password()->revealable()->dehydrated(false)
                            ->visible(fn (?User $record) => (int) $record?->id === (int) Auth::id()),

                        Forms\Components\TextInput::make('password')
                            ->label('Nova senha')->password()->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->placeholder(fn (string $operation) => $operation === 'edit' ? 'Deixe em branco para manter a atual' : ''),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar nova senha')->password()->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(false)->same('password'),
                    ])->columns(3),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')->label('Avatar')->disk('public')->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->name) . '&color=7F9CF5&background=EBF4FF'),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('title')->label('Cargo')->searchable()->toggleable(),
                Tables\Columns\BadgeColumn::make('access_level')->label('Nível')
                    ->colors(['danger' => 'super_admin', 'primary' => 'admin', 'warning' => 'editor'])
                    ->formatStateUsing(fn ($state) => match($state) {
                        'super_admin' => 'Super Admin',
                        'admin'       => 'Admin',
                        'editor'      => 'Editor',
                        default       => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Tables\Actions\DeleteAction $action, User $record) {
                        $auth = Auth::user();
                        if ($record->isSuperAdmin()) {
                            Notification::make()->title('Não é possível excluir o Super Admin!')->danger()->send();
                            $action->cancel();
                            return;
                        }
                        if ($auth->isAdmin() && $record->isAdmin()) {
                            Notification::make()->title('Admins não podem excluir outros Admins!')->danger()->send();
                            $action->cancel();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
