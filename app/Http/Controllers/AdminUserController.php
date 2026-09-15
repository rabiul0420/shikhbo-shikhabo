<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = Admin::query()
            ->where('is_admin', true)
            ->orderByDesc('is_super_admin')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:admins,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:admins,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'admin_role' => ['sometimes', 'required', \Illuminate\Validation\Rule::in(array_keys(Admin::ADMIN_ROLES))],
        ]);

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => $data['password'],
            'is_admin' => true,
            'is_super_admin' => false,
            'admin_role' => $data['admin_role'] ?? 'admin',
        ]);

        return redirect()->route('admin.users.index')->with('status', 'Admin user added.');
    }

    public function updateRole(Request $request, Admin $user): RedirectResponse
    {
        abort_unless($user->is_admin, 404);
        abort_if($user->is_super_admin || $user->is($request->user()), 403);

        $data = $request->validate([
            'admin_role' => ['required', \Illuminate\Validation\Rule::in(array_keys(Admin::ADMIN_ROLES))],
        ]);
        $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'User role updated.');
    }
}
