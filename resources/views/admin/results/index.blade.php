@extends('layouts.app', ['title' => 'Admin Results'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar')

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
                                        <td>{{ $attempt->user?->name ?? $attempt->admin?->name ?? 'Deleted account' }}</td>
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
                                            <a class="button secondary small" href="{{ route('admin.results.show', $attempt) }}">View result</a>
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




