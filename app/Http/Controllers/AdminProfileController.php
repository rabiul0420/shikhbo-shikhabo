<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminProfileController extends Controller
{
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password:admin'],
            'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:current_password'],
        ]);

        $user = $request->user();
        $user->password = $data['password'];
        $user->setRememberToken(\Illuminate\Support\Str::random(60));
        $user->save();
        $request->session()->regenerate();

        return redirect()->route('admin.profile.show')->with('status', 'Password changed successfully.');
    }

    public function show(Request $request): View
    {
        return view('admin.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('admins', 'phone')->ignore($user->id)],
        ]);

        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return redirect()->route('admin.profile.show')->with('status', 'Profile updated.');
    }
}
