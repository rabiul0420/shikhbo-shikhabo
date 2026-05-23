@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Create Account</h1>
            <p class="muted">Register as a student user. Admin access is assigned from the database.</p>
        </div>
    </div>

    <section class="panel" style="max-width:520px;">
        <form class="stack" method="POST" action="{{ route('register') }}">
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
                Password
                <input type="password" name="password">
            </label>
            <label>
                Confirm Password
                <input type="password" name="password_confirmation">
            </label>
            <button type="submit">Create account</button>
        </form>
        <p class="muted">Already registered? <a href="{{ route('login') }}"><strong>Login</strong></a>.</p>
    </section>
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
