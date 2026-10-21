<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class RoleManagementService
{
    public function changeRole(User $actor, User $target, string $newRole): bool
    {
        Gate::forUser($actor)->authorize('manage-roles');
        if ($actor->is($target) || $target->role === 'admin') {
            throw ValidationException::withMessages(['role' => 'Akun sendiri atau akun admin tidak dapat diubah di fitur ini.']);
        }
        if (!in_array($newRole, ['customer', 'staff'], true)) {
            throw ValidationException::withMessages(['role' => 'Role hanya boleh customer atau staff.']);
        }
        if ($target->role === $newRole) return false;

        $target->role = $newRole;
        $target->save();
        return true;
    }
}
