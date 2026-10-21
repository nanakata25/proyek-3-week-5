<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChangeRoleRequest;
use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('roles', ['users' => User::select('id_user', 'username', 'nama_lengkap', 'role')->orderBy('id_user')->get()]);
    }

    public function update(ChangeRoleRequest $request, User $user, RoleManagementService $service): RedirectResponse
    {
        $changed = $service->changeRole($request->user(), $user, $request->validated('role'));
        return redirect()->route('admin.users.index')->with('success', $changed
            ? 'Role diperbarui.' : 'Role sudah sama; tidak ada perubahan.');
    }
}
