<?php

namespace App\Http\Controllers;

use App\Models\AcademicClass;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login', ['loginMode' => 'student']);
    }

    public function showAdminLogin(): View
    {
        return view('auth.login', ['loginMode' => 'admin']);
    }

    public function login(Request $request): RedirectResponse
    {
        $isAdminLogin = $request->routeIs('admin.login') || $request->is('admin/login');
        $loginField = $isAdminLogin ? 'email' : 'phone';

        $rules = [
            $loginField => $isAdminLogin ? ['required', 'email'] : ['required', 'string'],
            'password' => ['required', 'string'],
            'redirect_to' => ['nullable', 'string', 'starts_with:/'],
        ];

        $request->validate($rules);

        $credentials = [
            $loginField => $request->input($loginField),
            'password' => $request->input('password'),
            'is_admin' => $isAdminLogin,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors([$loginField => 'The provided credentials do not match our records.'])
                ->onlyInput($loginField);
        }

        $request->session()->regenerate();

        $redirectTo = $request->input('redirect_to');

        if ($redirectTo && ! str_starts_with($redirectTo, '//')) {
            return redirect()->to($redirectTo);
        }

        return redirect()->intended($isAdminLogin ? route('admin.index') : route('home'));
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
            'phone' => ['required', 'string', 'max:30', 'unique:users,phone'],
            'school_name' => ['required', 'string', 'max:255'],
            'academic_class_id' => ['required', 'exists:academic_classes,id'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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

        $profilePhotoPath = $request->hasFile('profile_photo')
            ? $this->storeProfilePhoto($request)
            : null;

        $user = User::create([
            'name' => $data['name'],
            'email' => $this->internalEmailForPhone($data['phone']),
            'phone' => $data['phone'],
            'school_id' => $school->id,
            'academic_class_id' => $data['academic_class_id'],
            'profile_photo_path' => $profilePhotoPath,
            'password' => $data['password'],
            'is_admin' => false,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Account created.');
    }

    public function profile(): View
    {
        return view('profile.show');
    }

    public function editProfile(): View
    {
        $classes = AcademicClass::query()
            ->orderBy('name')
            ->get();

        $schools = School::query()
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('profile.edit', compact('classes', 'schools'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user->id)],
            'school_name' => ['nullable', 'string', 'max:255'],
            'academic_class_id' => ['nullable', 'exists:academic_classes,id'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $schoolId = null;
        $schoolName = trim($data['school_name'] ?? '');

        if ($schoolName !== '') {
            $school = School::query()
                ->whereRaw('LOWER(title) = ?', [mb_strtolower($schoolName)])
                ->first();

            if (! $school) {
                $school = School::create([
                    'title' => $schoolName,
                    'address' => 'Added during profile update',
                    'status' => 'pending',
                ]);
            }

            $schoolId = $school->id;
        }

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                $this->deleteProfilePhoto($user->profile_photo_path);
            }

            $user->profile_photo_path = $this->storeProfilePhoto($request);
        }

        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'school_id' => $schoolId,
            'academic_class_id' => $data['academic_class_id'] ?? null,
        ])->save();

        return back()->with('status', 'Profile updated.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $redirectRoute = $request->user()?->is_admin ? 'admin.login' : 'home';

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($redirectRoute)->with('status', 'You are logged out.');
    }

    private function storeProfilePhoto(Request $request): string
    {
        $file = $request->file('profile_photo');
        $directory = $this->webRootPath('uploads/profile-photos');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/profile-photos/' . $filename;
    }

    private function deleteProfilePhoto(string $path): void
    {
        if (! str_starts_with($path, 'uploads/profile-photos/')) {
            return;
        }

        $fullPath = $this->webRootPath($path);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    private function internalEmailForPhone(string $phone): string
    {
        return Str::slug($phone) . '-' . Str::lower(Str::random(8)) . '@mobile.local';
    }

    private function webRootPath(string $path = ''): string
    {
        $documentRoot = request()->server('DOCUMENT_ROOT');
        $root = is_string($documentRoot) && is_dir($documentRoot)
            ? $documentRoot
            : public_path();

        return rtrim($root, DIRECTORY_SEPARATOR) . ($path ? DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '');
    }
}
