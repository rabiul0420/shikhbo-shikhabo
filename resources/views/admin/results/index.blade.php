@extends('layouts.app', ['title' => 'Admin Results'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <span class="admin-mark">M</span>
                <div>
                    <h2>MCQ Admin</h2>
                    <p>{{ auth()->user()->name }}</p>
                </div>
            </div>

            <nav class="admin-menu" aria-label="Admin navigation">
                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Main Menu
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.index') }}#dashboard">Dashboard</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Academic Setup
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.academic.classes') }}">Class</a>
                        <a class="admin-nav-link" href="{{ route('admin.academic.lessons') }}">Lesson</a>
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

                <div class="admin-menu-group is-open">
                    <button class="admin-menu-toggle" type="button">
                        Result
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link is-active" href="{{ route('admin.results.index') }}">All Results</a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Result</span>
                        <h2>Exam Results</h2>
                    </div>
                    <span class="count-badge">{{ $examAttempts->count() }}</span>
                </div>

                @if ($examAttempts->isEmpty())
                    <p class="muted">No exam results found yet.</p>
                @else
                    <div class="table-wrap">
                        <table id="exam-results-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Exam</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Lesson</th>
                                    <th>Score</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($examAttempts as $attempt)
                                    <tr>
                                        <td>{{ $attempt->user->name }}</td>
                                        <td>{{ $attempt->exam->title }}</td>
                                        <td>{{ $attempt->exam->academicClass->name }}</td>
                                        <td>{{ $attempt->exam->subject->name }}</td>
                                        <td>{{ $attempt->exam->chapter->name }}</td>
                                        <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
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
                        order: [[6, 'desc']],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }
            });
        }
    </script>
@endpush

