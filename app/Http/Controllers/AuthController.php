<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function showRegister(): View
    {
        $classes = AcademicClass::query()
            ->orderBy('name')
            ->get();

        $schools = School::query()
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('auth.register', compact('classes', 'schools'));
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'school_name' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $schoolName = trim($data['school_name']);
        $school = School::query()
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($schoolName)])
            ->first();

        if (! $school) {
            $school = School::create([
                'title' => $schoolName,
                'address' => 'Added during student registration',
                'status' => 'pending',
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'school_id' => $school->id,
            'academic_class_id' => $data['academic_class_id'],
            'password' => $data['password'],
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Account created.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You are logged out.');
    }
}
