@extends('layouts.app', ['title' => 'Add Admin User'])

@section('content')
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <img class="admin-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
                <div>
                    <h2>Shikhbo Shikhabo Admin</h2>
                    <p>{{ auth()->user()->name }}</p>
                </div>
            </div>

            <nav class="admin-menu" aria-label="Admin navigation">
                <a class="admin-nav-link" href="{{ route('admin.index') }}#dashboard">Dashboard</a>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Academic Setup
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.academic.classes') }}">Class</a>
                        <a class="admin-nav-link" href="{{ route('admin.academic.subjects') }}">Subject</a>
                        <a class="admin-nav-link" href="{{ route('admin.academic.chapters') }}">Oddhay / Chapter</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Question
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.index') }}#question-list">Question List</a>
                        <a class="admin-nav-link" href="{{ route('admin.questions.create') }}">Question Add</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Exam
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.exams.index') }}#exam-list">Exam List</a>
                        <a class="admin-nav-link" href="{{ route('admin.exams.index') }}#add-exam">Add Exam</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Schools
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.schools.index') }}">School List</a>
                        <a class="admin-nav-link" href="{{ route('admin.schools.index') }}#add-school">Add School</a>
                    </div>
                </div>

                <div class="admin-menu-group is-open">
                    <button class="admin-menu-toggle" type="button">
                        User
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.users.index') }}">User List</a>
                        <a class="admin-nav-link is-active" href="{{ route('admin.users.create') }}">Add User</a>
                    </div>
                </div>
                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Blog
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.blogs.index') }}">Blog List</a>
                        <a class="admin-nav-link" href="{{ route('admin.blogs.create') }}">Add Blog</a>
                    </div>
                </div>

                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.custom-results.index') }}">Custom Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
            </nav>
        </aside>

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
