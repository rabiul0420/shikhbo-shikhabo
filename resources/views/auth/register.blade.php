@extends('layouts.app', ['title' => 'Register'])

@push('styles')
    <style>
        .auth-page {
            min-height: calc(100vh - 270px);
            display: grid;
            place-items: center;
            padding: 28px 0;
        }

        .auth-card {
            width: min(100%, 560px);
            padding: 28px;
            border-radius: 8px;
            border-color: #d8dee7;
            box-shadow: 0 14px 38px rgba(31, 45, 61, .10);
        }

        .auth-card .page-head {
            display: block;
            margin-bottom: 22px;
            text-align: center;
        }

        .auth-card h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .auth-card .muted {
            margin-bottom: 0;
        }

        .auth-form {
            gap: 16px;
        }

        .auth-form label {
            color: #2f3d4c;
        }

        .auth-form input,
        .auth-form select {
            min-height: 44px;
            border-radius: 6px;
            background: #fbfcfd;
        }

        .auth-form span.muted {
            font-size: 13px;
            line-height: 1.45;
        }

        .auth-form button {
            width: 100%;
            min-height: 44px;
            border-radius: 6px;
            margin-top: 2px;
        }

        .auth-login-link {
            margin: 18px 0 0;
            text-align: center;
            font-size: 14px;
        }

        .auth-login-link a {
            color: var(--primary);
        }

        @media (max-width: 620px) {
            .auth-page {
                min-height: auto;
                padding: 14px 0;
            }

            .auth-card {
                padding: 22px 18px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <section class="panel auth-card">
            <div class="page-head">
                <div>
                    <h1>Create Account</h1>
                    <p class="muted">Register as a student user.</p>
                </div>
            </div>

            <form class="stack auth-form" method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <label>
                    Name
                    <input name="name" value="{{ old('name') }}" autofocus>
                </label>
                <label>
                    Email
                    <input type="email" name="email" value="{{ old('email') }}">
                </label>
                <label>
                    Phone Number
                    <input name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
                </label>
                <label>
                    Class
                    <select name="academic_class_id">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) old('academic_class_id') === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    School
                    <input
                        id="school-search"
                        name="school_name"
                        value="{{ old('school_name') }}"
                        list="school-options"
                        placeholder="Search or type your school name"
                        autocomplete="off"
                    >
                    <datalist id="school-options">
                        @foreach ($schools as $school)
                            <option value="{{ $school->title }}"></option>
                        @endforeach
                    </datalist>
                    <span id="school-help" class="muted">Search your school. If it is not listed, type the name and it will be added as pending.</span>
                </label>
                <label>
                    Profile Picture
                    <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp">
                    <span class="muted">Optional. JPG, PNG, or WebP image up to 2 MB.</span>
                </label>
                <label>
                    Password
                    <input type="password" name="password">
                </label>
                <label>
                    Confirm Password
                    <input type="password" name="password_confirmation">
                </label>
                <button type="submit">Create account</button>
            </form>
            <p class="muted auth-login-link">Already registered? <a href="{{ route('login') }}"><strong>Login</strong></a>.</p>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const schoolInput = document.getElementById('school-search');
        const schoolHelp = document.getElementById('school-help');
        const schoolNames = @json($schools->pluck('title')->values());

        if (schoolInput && schoolHelp) {
            const syncSchoolHelp = () => {
                const value = schoolInput.value.trim().toLowerCase();
                const exists = schoolNames.some((name) => name.toLowerCase() === value);

                if (! value) {
                    schoolHelp.textContent = 'Search your school. If it is not listed, type the name and it will be added as pending.';
                    return;
                }

                schoolHelp.textContent = exists
                    ? 'Existing school selected.'
                    : 'New school name: it will be added as pending after registration.';
            };

            schoolInput.addEventListener('input', syncSchoolHelp);
            syncSchoolHelp();
        }
    </script>
@endpush
