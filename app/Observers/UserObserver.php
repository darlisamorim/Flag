<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserObserver
{
    public function deleted(User $user): void
    {
        // Se não houver nenhum Super Admin, cria um automaticamente
        $superAdminExists = User::where('access_level', 'super_admin')->exists();

        if (!$superAdminExists) {
            User::create([
                'name'         => env('DAA_NAME', 'Darlis Alves Amorim'),
                'email'        => env('DAA_EMAIL', 'eu@darlisalvesamorim.dev'),
                'password'     => Hash::make(env('DAA_PASSWORD', 'password')),
                'access_level' => 'super_admin',
                'title'        => env('DAA_OFFICE'),
                'role'         => env('DAA_ROLE'),
                'phone'        => env('DAA_PHONE'),
                'location'     => env('DAA_CITY') . ', ' . env('DAA_UF'),
            ]);
        }
    }
}
