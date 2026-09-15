@extends('layouts.app', ['title' => 'Add Admin User'])

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar')

        <div class="admin-content">
            <section class="panel" style="max-width: 680px;">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">User</span>
                        <h2>Add Admin User</h2>
                    </div>
                    <a class="button secondary" href="{{ route('admin.users.index') }}">User List</a>
                </div>

                <form class="stack" method="POST" action="{{ route('admin.users.store') }}">
                    @csrf
                    <label>
                        Role
                        <select name="admin_role" required>
                            @foreach (\App\Models\Admin::ADMIN_ROLES as $value => $label)
                                <option value="{{ $value }}" @selected(old('admin_role', 'admin') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <p class="muted">Admin: all modules except user management. Exam Manager: academic setup, questions, exams and results. Content Editor: blogs only. Only Super Admin can manage users.</p>
                    <label>
                        Name
                        <input name="name" value="{{ old('name') }}" autofocus>
                    </label>
                    <label>
                        Email
                        <input type="email" name="email" value="{{ old('email') }}">
                    </label>
                    <label>
                        Mobile Number
                        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="01XXXXXXXXX" required>
                    </label>
                    <label>
                        Password
                        <input type="password" name="password">
                    </label>
                    <label>
                        Confirm Password
                        <input type="password" name="password_confirmation">
                    </label>
                    <button type="submit">Add user</button>
                </form>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        if (window.jQuery) {
            $(function () {
                $('.admin-menu-toggle').on('click', function () {
                    const $group = $(this).closest('.admin-menu-group');
                    const $submenu = $group.children('.admin-submenu');

                    $group.toggleClass('is-open');
                    $submenu.stop(true, true).slideToggle(180);
                });
            });
        }
    </script>
@endpush
