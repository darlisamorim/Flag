<?php

namespace App\Filament\Pages;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?string $navigationLabel = 'Painel de Controle';
    protected static ?string $navigationGroup = 'Geral';
    protected static ?string $navigationIcon  = 'heroicon-o-home';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $title           = 'Painel de Controle';
}