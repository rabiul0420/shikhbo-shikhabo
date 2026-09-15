@extends('layouts.app', ['title' => 'My Profile'])

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar', ['activeNav' => 'profile'])

        <div class="admin-content">
            <section class="panel" style="max-width: 680px;">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Account</span>
                        <h2>My Profile</h2>
                    </div>
                    <span class="count-badge">{{ $user->adminRole() === 'super_admin' ? 'Super Admin' : (\App\Models\Admin::ADMIN_ROLES[$user->adminRole()] ?? $user->adminRole()) }}</span>
                </div>

                @if ($user->profile_photo_path)
                    <img src="{{ asset($user->profile_photo_path) }}" alt="Profile photo" width="96" height="96" style="object-fit: cover; border-radius: 50%; margin-bottom: 16px;">
                @endif

                <form class="stack" method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    @method('PATCH')
                    <label>
                        Name
                        <input name="name" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                    </label>
                    <p class="muted">Use this email to sign in to the admin panel.</p>
                    <label>
                        Mobile Number
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="30" autocomplete="tel">
                    </label>
                    <div class="row">
                        <button type="submit">Save Changes</button>
                        <a class="button secondary" href="{{ route('admin.profile.show') }}">Cancel</a>
                    </div>
                </form>
            </section>
            <section class="panel" style="max-width: 680px; margin-top: 20px;">
                <div class="section-head"><h2>Change Password</h2></div>
                <form class="stack" method="POST" action="{{ route('admin.profile.password.update') }}">
                    @csrf
                    @method('PATCH')
                    <label>
                        Current Password
                        <input type="password" name="current_password" autocomplete="current-password" required>
                    </label>
                    <label>
                        New Password
                        <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="72" required>
                    </label>
                    <p class="muted">Use at least 8 characters and choose a different password from your current one.</p>
                    <label>
                        Confirm New Password
                        <input type="password" name="password_confirmation" autocomplete="new-password" minlength="8" maxlength="72" required>
                    </label>
                    <div class="row"><button type="submit">Change Password</button></div>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.admin-menu-toggle').forEach(button => {
            button.addEventListener('click', () => button.closest('.admin-menu-group').classList.toggle('is-open'));
        });
    </script>
@endpush
