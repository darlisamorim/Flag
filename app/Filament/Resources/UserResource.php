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

    /**
     * Qualquer usuário autenticado pode acessar as ROTAS do resource.
     * A Policy controla quem pode ver/editar cada registro.
     * A sidebar é controlada por shouldRegisterNavigation().
     */
    public static function canAccess(): bool
    {
        return Auth::check();
    }

    /**
     * Só Admin+ vê "Usuários" na sidebar.
     * Editor não vê o menu, mas acessa /users/{id}/edit via link "Meu Perfil".
     */
    public static function shouldRegisterNavigation(): bool
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

                        // Foto de perfil — componente único com preview integrado
                        Forms\Components\FileUpload::make('avatar')
                            ->label('Foto de perfil')
                            ->image()
                            ->avatar()
                            ->imageEditor()
                            ->circleCropper()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->maxSize(2048)
                            ->live()
                            ->afterStateUpdated(function ($state, ?User $record, Forms\Set $set) {
                                if (!$record) return;

                                // Se removeu a foto
                                if (empty($state)) {
                                    if ($record->avatar && Storage::disk('public')->exists($record->avatar)) {
                                        Storage::disk('public')->delete($record->avatar);
                                    }
                                    $record->update(['avatar' => null]);
                                    Notification::make()->title('Foto removida!')->success()->duration(1500)->send();
                                    return;
                                }

                                // O state pode ser um TemporaryUploadedFile ou uma string
                                // Se for TemporaryUploadedFile, precisamos salvar manualmente
                                if ($state instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                    $fileName = Str::slug($record->name) . '-' . time() . '.' . ($state->getClientOriginalExtension() ?: 'png');
                                    $path = $state->storeAs('avatars', $fileName, 'public');

                                    // Deleta avatar antigo
                                    if ($record->avatar && Storage::disk('public')->exists($record->avatar)) {
                                        Storage::disk('public')->delete($record->avatar);
                                    }

                                    $record->update(['avatar' => $path]);
                                    $set('avatar', $path);
                                    Notification::make()->title('Foto atualizada!')->success()->duration(2000)->send();
                                }
                            }),

                        // Badge de nível — visível quando:
                        // - O record é Super Admin (nunca editável)
                        // - OU o usuário está editando a si mesmo (não pode se rebaixar/promover)
                        // - OU o usuário logado é Editor (não pode mudar nível de ninguém)
                        Forms\Components\Placeholder::make('access_level_label')
                            ->label('Nível de acesso')
                            ->content(fn (?User $record) => match($record?->access_level) {
                                'super_admin' => new \Illuminate\Support\HtmlString('<span style="background:#7f1d1d;color:#fca5a5;padding:4px 12px;border-radius:9999px;font-size:13px;">Super Admin</span>'),
                                'admin'       => new \Illuminate\Support\HtmlString('<span style="background:#1e3a5f;color:#93c5fd;padding:4px 12px;border-radius:9999px;font-size:13px;">Admin</span>'),
                                default       => new \Illuminate\Support\HtmlString('<span style="background:#3b2f00;color:#fcd34d;padding:4px 12px;border-radius:9999px;font-size:13px;">Editor</span>'),
                            })
                            ->visible(function (?User $record) {
                                $auth = Auth::user();
                                // Super Admin no record → sempre badge
                                if ($record?->isSuperAdmin()) return true;
                                // Editando a si mesmo → badge (não pode se rebaixar)
                                if ($record && (int) $record->id === (int) $auth->id) return true;
                                // Editor logado → badge (não pode mudar nível)
                                if ($auth->isEditor()) return true;
                                // Admin+ editando outro não-SuperAdmin → mostra dropdown
                                return false;
                            }),

                        // Dropdown de nível — só aparece para Admin+ editando OUTRO usuário (não SuperAdmin)
                        Forms\Components\Select::make('access_level')
                            ->label('Nível de acesso')
                            ->options(function () {
                                $auth = Auth::user();
                                if ($auth->isSuperAdmin()) {
                                    return ['admin' => 'Admin', 'editor' => 'Editor'];
                                }
                                return ['editor' => 'Editor'];
                            })
                            ->default('editor')
                            ->required()
                            ->visible(function (?User $record) {
                                $auth = Auth::user();
                                // Não pode editar nível do SuperAdmin
                                if ($record?->isSuperAdmin()) return false;
                                // Não pode editar o próprio nível (se rebaixar)
                                if ($record && (int) $record->id === (int) $auth->id) return false;
                                // Só Admin+ pode ver o dropdown
                                return $auth->isAtLeastAdmin();
                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('access_level', $state, $record)),

                    ])->columns(2),

                // ─── INFORMAÇÕES PESSOAIS ─────────────────────────────────
                Forms\Components\Section::make('Informações pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome completo')
                            ->placeholder('Ex: Darlis Alves Amorim')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('name', $state, $record)),

                        Forms\Components\DatePicker::make('birthdate')
                            ->label('Data de nascimento')
                            ->placeholder('DD/MM/AAAA')
                            ->displayFormat('d/m/Y')
                            ->maxDate(now()->subYears(1))
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('birthdate', $state, $record)),

                        Forms\Components\TextInput::make('email')
                            ->label('E-mail')
                            ->placeholder('Ex: eu@seusite.com')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('email', $state, $record)),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefone / WhatsApp')
                            ->placeholder('Ex: +55 (11) 999-999-999')
                            ->mask('+55 (99) 999-999-999')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('phone', $state, $record)),

                        Forms\Components\TextInput::make('title')
                            ->label('Cargo / título')
                            ->placeholder('Ex: Developer & Design')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('title', $state, $record)),

                        Forms\Components\TextInput::make('role')
                            ->label('Função técnica')
                            ->placeholder('Ex: Software Engineer')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('role', $state, $record)),

                        Forms\Components\TextInput::make('subname')
                            ->label('Tagline / subtítulo')
                            ->placeholder('Ex: Full Stack Developer de São Paulo/SP')
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('subname', $state, $record)),

                        Forms\Components\Textarea::make('bio')
                            ->label('Bio / descrição')
                            ->placeholder('Ex: Desenvolvedor Full Stack apaixonado por tecnologia...')
                            ->rows(4)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('bio', $state, $record)),
                    ])->columns(2),

                // ─── ENDEREÇO ─────────────────────────────────────────────
                Forms\Components\Section::make('Endereço')
                    ->schema([
                        Forms\Components\TextInput::make('addr')
                            ->label('Endereço')
                            ->placeholder('Ex: Av. Interlagos, 4944')
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('addr', $state, $record)),

                        Forms\Components\TextInput::make('district')
                            ->label('Bairro / Região')
                            ->placeholder('Ex: Zona Sul')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('district', $state, $record)),

                        Forms\Components\TextInput::make('location')
                            ->label('Cidade, UF')
                            ->placeholder('Ex: São Paulo, SP')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('location', $state, $record)),

                        Forms\Components\TextInput::make('zip')
                            ->label('CEP')
                            ->placeholder('Ex: 04777-000')
                            ->mask('99999-999')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('zip', $state, $record)),

                        Forms\Components\TextInput::make('country')
                            ->label('País')
                            ->placeholder('Ex: Brasil')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('country', $state, $record)),
                    ])->columns(2),

                // ─── DADOS JURÍDICOS ──────────────────────────────────────
                Forms\Components\Section::make('Dados jurídicos')
                    ->schema([
                        Forms\Components\TextInput::make('razao_social')
                            ->label('Razão social')
                            ->placeholder('Ex: Darlis Alves Amorim LTDA')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('razao_social', $state, $record)),

                        Forms\Components\TextInput::make('nome_fantasia')
                            ->label('Nome fantasia')
                            ->placeholder('Ex: DAA Studio')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('nome_fantasia', $state, $record)),

                        Forms\Components\TextInput::make('cnpj')
                            ->label('CNPJ')
                            ->placeholder('Ex: 00.000.000/0001-00')
                            ->mask('99.999.999/9999-99')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('cnpj', $state, $record)),

                        Forms\Components\TextInput::make('ie')
                            ->label('Inscrição Estadual')
                            ->placeholder('Ex: 000.000.000.000')
                            ->mask('999.999.999.999')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('ie', $state, $record)),
                    ])->columns(2),

                // ─── SITE E REDES PRINCIPAIS ──────────────────────────────
                Forms\Components\Section::make('Site e redes principais')
                    ->schema([
                        Forms\Components\TextInput::make('website')->label('Site pessoal')->url()->placeholder('https://seusite.com')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('website', $state, $record)),
                        Forms\Components\TextInput::make('links')->label('Linktree / Links')->url()->placeholder('https://linktr.ee/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('links', $state, $record)),
                        Forms\Components\TextInput::make('github')->label('GitHub')->url()->placeholder('https://github.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('github', $state, $record)),
                        Forms\Components\TextInput::make('linkedin')->label('LinkedIn')->url()->placeholder('https://linkedin.com/in/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('linkedin', $state, $record)),
                        Forms\Components\TextInput::make('twitter')->label('Twitter / X')->url()->placeholder('https://x.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('twitter', $state, $record)),
                        Forms\Components\TextInput::make('instagram')->label('Instagram')->url()->placeholder('https://instagram.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('instagram', $state, $record)),
                        Forms\Components\TextInput::make('tiktok')->label('TikTok')->url()->placeholder('https://tiktok.com/@seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('tiktok', $state, $record)),
                        Forms\Components\TextInput::make('youtube')->label('YouTube')->url()->placeholder('https://youtube.com/@seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('youtube', $state, $record)),
                        Forms\Components\TextInput::make('facebook')->label('Facebook (perfil)')->url()->placeholder('https://facebook.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('facebook', $state, $record)),
                        Forms\Components\TextInput::make('fb_page')->label('Facebook (página)')->url()->placeholder('https://facebook.com/suapagina')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('fb_page', $state, $record)),
                    ])->columns(2),

                // ─── OUTRAS REDES ─────────────────────────────────────────
                Forms\Components\Section::make('Outras redes')
                    ->schema([
                        Forms\Components\TextInput::make('medium')->label('Medium')->url()->placeholder('https://medium.com/@seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('medium', $state, $record)),
                        Forms\Components\TextInput::make('devto')->label('Dev.to')->url()->placeholder('https://dev.to/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('devto', $state, $record)),
                        Forms\Components\TextInput::make('codepen')->label('CodePen')->url()->placeholder('https://codepen.io/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('codepen', $state, $record)),
                        Forms\Components\TextInput::make('behance')->label('Behance')->url()->placeholder('https://behance.net/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('behance', $state, $record)),
                        Forms\Components\TextInput::make('dribbble')->label('Dribbble')->url()->placeholder('https://dribbble.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('dribbble', $state, $record)),
                        Forms\Components\TextInput::make('deviantart')->label('DeviantArt')->url()->placeholder('https://deviantart.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('deviantart', $state, $record)),
                        Forms\Components\TextInput::make('pinterest')->label('Pinterest')->url()->placeholder('https://pinterest.com/seuperfil')
                            ->live(onBlur: true)->afterStateUpdated(fn ($state, ?User $record) => self::autoSave('pinterest', $state, $record)),
                    ])->columns(2),

                // ─── ALTERAR SENHA ────────────────────────────────────────
                Forms\Components\Section::make('Alterar senha')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Senha atual')
                            ->password()->revealable()->dehydrated(false)
                            ->visible(fn (?User $record) => (int) $record?->id === (int) Auth::id()),

                        Forms\Components\TextInput::make('password')
                            ->label('Nova senha')
                            ->password()->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                            ->placeholder(fn (string $operation) => $operation === 'edit' ? 'Deixe em branco para manter a atual' : ''),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar nova senha')
                            ->password()->revealable()
                            ->required(fn (string $operation) => $operation === 'create')
                            ->dehydrated(false)
                            ->same('password'),
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
