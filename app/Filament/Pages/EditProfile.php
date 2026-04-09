<?php

namespace App\Filament\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EditProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Meu Perfil';
    protected static ?string $title = 'Meu Perfil';
    protected static ?int $navigationSort = 99;
    protected static bool $shouldRegisterNavigation = false;

    // View não será usada pois redirecionamos antes de renderizar
    protected static string $view = 'filament.pages.edit-profile';

    public function mount(): void
    {
        $this->redirect(
            UserResource::getUrl('edit', ['record' => Auth::id()])
        );
    }
}
