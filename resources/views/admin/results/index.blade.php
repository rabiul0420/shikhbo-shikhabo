@extends('layouts.app', ['title' => 'Admin Results'])

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

                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
                <a class="admin-nav-link admin-menu-direct is-active" href="{{ route('admin.results.index') }}">Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.custom-results.index') }}">Custom Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
            </nav>
        </aside>

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Result</span>
                        <h2>{{ $exam ? $exam->title . ' Results' : 'Exam Results' }}</h2>
                        @if ($exam)
                            <p class="muted">
                            {{ $exam->academicClass->name }} / {{ $exam->subject->name }} / {{ $exam->chapter->display_name }}
                            </p>
                        @endif
                    </div>
                    <span class="count-badge">{{ $exam ? $participantCount : $examAttempts->count() }}</span>
                </div>

                @if ($examAttempts->isEmpty())
                    <p class="muted">{{ $exam ? 'No participants found for this exam yet.' : 'No exam results found yet.' }}</p>
                @else
                    @if ($exam)
                        <div class="stat-grid" style="margin-bottom: 14px;">
                            <div class="stat-card stat-info">
                                <i>#</i>
                                <strong>{{ $participantCount }}</strong>
                                <span>Total Participants</span>
                            </div>
                            <div class="stat-card stat-success">
                                <i>H</i>
                                <strong>{{ $highestAttempt->score }} / {{ $highestAttempt->total_marks }}</strong>
                                <span>Highest Mark</span>
                            </div>
                        </div>
                    @endif

                    <div class="table-wrap">
                        <table id="exam-results-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    @if ($exam)
                                        <th>Position</th>
                                    @endif
                                    <th>Student</th>
                                    <th>Exam</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Oddhay / Chapter</th>
                                    <th>Score</th>
                                    @if ($exam)
                                        <th>Gift</th>
                                    @endif
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($examAttempts as $attempt)
                                    <tr>
                                        @if ($exam)
                                            <td>{{ $attemptPositions[$attempt->id] }}</td>
                                        @endif
                                        <td>{{ $attempt->user->name }}</td>
                                        <td>{{ $attempt->exam->title }}</td>
                                        <td>{{ $attempt->exam->academicClass->name }}</td>
                                        <td>{{ $attempt->exam->subject->name }}</td>
                                            <td>{{ $attempt->exam->chapter->display_name }}</td>
                                        <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
                                        @if ($exam)
                                            <td>{{ $exam->prizeForPosition($attemptPositions[$attempt->id]) ?: '-' }}</td>
                                        @endif
                                        <td>{{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <a class="button secondary small" href="{{ route('exam-attempts.result', $attempt) }}">View result</a>
                                        </td>
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

                if ($.fn.DataTable && $('#exam-results-table').length) {
                    $('#exam-results-table').DataTable({
                        pageLength: 10,
                        order: @json($exam ? [[0, 'asc']] : [[6, 'desc']]),
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }
            });
        }
    </script>
@endpush




