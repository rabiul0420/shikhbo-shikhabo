@extends('layouts.app', ['title' => 'Student List'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

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

                @if (auth()->user()->is_super_admin)
                    <div class="admin-menu-group">
                        <button class="admin-menu-toggle" type="button">
                            User
                            <span></span>
                        </button>
                        <div class="admin-submenu">
                            <a class="admin-nav-link" href="{{ route('admin.users.index') }}">User List</a>
                            <a class="admin-nav-link" href="{{ route('admin.users.create') }}">Add User</a>
                        </div>
                    </div>
                @endif

                <a class="admin-nav-link admin-menu-direct is-active" href="{{ route('admin.students.index') }}">Student List</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
            </nav>
        </aside>

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Students</span>
                        <h2>Student List</h2>
                    </div>
                    <span class="count-badge">{{ $studentCount }}</span>
                </div>

                @if (session('status'))
                    <p class="muted">{{ session('status') }}</p>
                @endif

                @if ($studentCount === 0)
                    <p class="muted">No students found yet.</p>
                @else
                    <div class="table-wrap">
                        <table id="students-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Class</th>
                                    <th>School</th>
                                    <th>Attempts</th>
                                    <th>Last Submitted</th>
                                    <th>Joined</th>
                                    <th>Password</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="modal-backdrop {{ $errors->studentPassword->any() ? 'is-open' : '' }}" id="change-password-modal" aria-hidden="{{ $errors->studentPassword->any() ? 'false' : 'true' }}">
                        <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="change-password-title">
                            <div class="modal-head">
                                <div>
                                    <span class="eyebrow">Student Password</span>
                                    <h3 id="change-password-title">Change Password</h3>
                                    <p class="muted" id="change-password-student">
                                        @if (old('password_student_name'))
                                            {{ old('password_student_name') }} - {{ old('password_student_contact') }}
                                        @endif
                                    </p>
                                </div>
                                <button class="button secondary small js-close-modal" type="button" data-modal-close="change-password-modal">Close</button>
                            </div>

                            <form class="stack" id="change-password-form" method="POST" action="{{ old('password_action', route('admin.students.index')) }}">
                                @csrf
                                @method('PATCH')
                                <label>
                                    New Password
                                    <input type="password" name="password" minlength="8" required autofocus>
                                </label>
                                <label>
                                    Confirm Password
                                    <input type="password" name="password_confirmation" minlength="8" required>
                                </label>
                                @if ($errors->studentPassword->any())
                                    <p class="muted">{{ $errors->studentPassword->first('password') }}</p>
                                @endif
                                <div class="modal-actions">
                                    <button class="button secondary js-close-modal" type="button" data-modal-close="change-password-modal">Cancel</button>
                                    <button type="submit">Change Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>
    <script>
        if (window.jQuery) {
            $(function () {
                $('.admin-menu-toggle').on('click', function () {
                    const $group = $(this).closest('.admin-menu-group');
                    const $submenu = $group.children('.admin-submenu');

                    $group.toggleClass('is-open');
                    $submenu.stop(true, true).slideToggle(180);
                });

                if ($.fn.DataTable && $('#students-table').length) {
                    $('#students-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: @json(route('admin.students.data')),
                        pageLength: 10,
                        order: [[0, 'asc']],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }

                $(document).on('click', '.js-change-password', function () {
                    const $button = $(this);
                    const modalId = 'change-password-modal';
                    const $modal = $('#' + modalId);

                    $('#change-password-form').attr('action', $button.data('action'));
                    $('#change-password-student').text($button.data('student-name') + ' - ' + $button.data('student-contact'));
                    $modal.addClass('is-open').attr('aria-hidden', 'false');
                    $('body').addClass('modal-open');
                    $modal.find('input, textarea, select, button').filter(':visible').first().trigger('focus');
                });

                $(document).on('click', '.js-close-modal', function () {
                    const modalId = $(this).data('modal-close');

                    $('#' + modalId).removeClass('is-open').attr('aria-hidden', 'true');
                    $('body').removeClass('modal-open');
                });

                $(document).on('click', '.modal-backdrop', function (event) {
                    if (event.target === this) {
                        $(this).removeClass('is-open').attr('aria-hidden', 'true');
                        $('body').removeClass('modal-open');
                    }
                });

                $(document).on('keydown', function (event) {
                    if (event.key === 'Escape') {
                        $('.modal-backdrop.is-open').removeClass('is-open').attr('aria-hidden', 'true');
                        $('body').removeClass('modal-open');
                    }
                });

                if ($('.modal-backdrop.is-open').length) {
                    $('body').addClass('modal-open');
                }
            });
        }
    </script>
@endpush




