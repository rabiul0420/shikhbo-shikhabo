@extends('layouts.app', ['title' => 'Update Profile'])

@section('content')
    <div class="profile-page">
        <div class="page-head">
            <div>
                <h1>Update Profile</h1>
                <p class="muted">Edit your account information for Shikhbo Shikhabo.</p>
            </div>
        </div>

        <section class="panel content-panel profile-card">
            <form class="stack profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <label>
                    Name
                    <input name="name" value="{{ old('name', auth()->user()->name) }}" required>
                </label>

                <label>
                    Mobile Number
                    <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" placeholder="01XXXXXXXXX" required>
                </label>

                <label>
                    Class
                    <select name="academic_class_id">
                        <option value="">Select class</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" @selected((string) old('academic_class_id', auth()->user()->academic_class_id) === (string) $class->id)>{{ $class->name }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    School
                    <input
                        id="profile-school-search"
                        name="school_name"
                        value="{{ old('school_name', auth()->user()->school->title ?? '') }}"
                        list="profile-school-options"
                        placeholder="Search or type your school name"
                        autocomplete="off"
                    >
                    <datalist id="profile-school-options">
                        @foreach ($schools as $school)
                            <option value="{{ $school->title }}"></option>
                        @endforeach
                    </datalist>
                    <span id="profile-school-help" class="muted">Search your school. If it is not listed, type the name and it will be added as pending.</span>
                </label>

                <label>
                    Profile Picture
                    <span class="profile-photo-field">
                        <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp">
                        @if (auth()->user()->profile_photo_path)
                            <img
                                class="profile-photo-preview"
                                src="{{ asset(auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }} profile picture"
                            >
                        @endif
                    </span>
                    <span class="muted">Optional. JPG, PNG, or WebP image up to 2 MB.</span>
                </label>

                <div class="row profile-form-actions">
                    <button type="submit">Update Profile</button>
                    <a class="button secondary" href="{{ route('profile.show') }}">Cancel</a>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const profileSchoolInput = document.getElementById('profile-school-search');
        const profileSchoolHelp = document.getElementById('profile-school-help');
        const profileSchoolNames = @json($schools->pluck('title')->values());

        if (profileSchoolInput && profileSchoolHelp) {
            const syncProfileSchoolHelp = () => {
                const value = profileSchoolInput.value.trim().toLowerCase();
                const exists = profileSchoolNames.some((name) => name.toLowerCase() === value);

                if (! value) {
                    profileSchoolHelp.textContent = 'Search your school. If it is not listed, type the name and it will be added as pending.';
                    return;
                }

                profileSchoolHelp.textContent = exists
                    ? 'Existing school selected.'
                    : 'New school name: it will be added as pending after update.';
            };

            profileSchoolInput.addEventListener('input', syncProfileSchoolHelp);
            syncProfileSchoolHelp();
        }
    </script>
@endpush
