<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Listar usuários — só Admin+
     */
    public function viewAny(User $auth): bool
    {
        return $auth->isAtLeastAdmin();
    }

    /**
     * Ver um usuário — Admin+ ou o próprio
     */
    public function view(User $auth, User $record): bool
    {
        return $auth->isAtLeastAdmin() || $auth->id === $record->id;
    }

    /**
     * Criar usuário — só Admin+
     */
    public function create(User $auth): bool
    {
        return $auth->isAtLeastAdmin();
    }

    /**
     * Editar — Admin+ ou o próprio usuário (Editor editando a si mesmo)
     */
    public function update(User $auth, User $record): bool
    {
        return $auth->isAtLeastAdmin() || $auth->id === $record->id;
    }

    /**
     * Excluir — só Admin+
     */
    public function delete(User $auth, User $record): bool
    {
        return $auth->isAtLeastAdmin();
    }

    /**
     * Bulk delete — só Admin+
     */
    public function deleteAny(User $auth): bool
    {
        return $auth->isAtLeastAdmin();
    }
}
