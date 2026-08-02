@extends('layouts.app', ['title' => 'Admin Users'])

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
                        <a class="admin-nav-link is-active" href="{{ route('admin.users.index') }}">User List</a>
                        <a class="admin-nav-link" href="{{ route('admin.users.create') }}">Add User</a>
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
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">User</span>
                        <h2>Admin User List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $users->count() }}</span>
                        <a class="button" href="{{ route('admin.users.create') }}">Add User</a>
                    </div>
                </div>

                @if ($users->isEmpty())
                    <p class="muted">No admin users found.</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?: '-' }}</td>
                                        <td>{{ $user->is_super_admin ? 'Super Admin' : 'Admin' }}</td>
                                        <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
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
