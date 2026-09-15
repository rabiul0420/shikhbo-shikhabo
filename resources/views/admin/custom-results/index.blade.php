@extends('layouts.app', ['title' => 'Admin Custom Results'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.academic.partials.sidebar', ['activeAcademic' => ''])

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Custom Result</span>
                        <h2>Student Custom Exam Results</h2>
                        <p class="muted">All customized exams created and submitted by students.</p>
                    </div>
                    <span class="count-badge">{{ $attempts->count() }}</span>
                </div>

                @if ($attempts->isEmpty())
                    <p class="muted">No custom exam results found yet.</p>
                @else
                    <div class="table-wrap">
                        <table id="custom-results-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Exam</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Oddhay / Chapter</th>
                                    <th>Questions</th>
                                    <th>Score</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($attempts as $attempt)
                                    <tr>
                                        <td>{{ $attempt->user?->name ?? $attempt->admin?->name ?? 'Deleted account' }}</td>
                                        <td>{{ $attempt->customExam->title }}</td>
                                        <td>{{ $attempt->customExam->academicClass->name }}</td>
                                        <td>{{ $attempt->customExam->subject->name }}</td>
                                        <td>{{ $attempt->customExam->chapter->display_name }}</td>
                                        <td>{{ $attempt->customExam->question_count }}</td>
                                        <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
                                        <td>{{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <a class="button secondary small" href="{{ route('admin.custom-results.show', $attempt) }}">View result</a>
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

                if ($.fn.DataTable && $('#custom-results-table').length) {
                    $('#custom-results-table').DataTable({
                        pageLength: 10,
                        order: [[7, 'desc']],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }
            });
        }
    </script>
@endpush
