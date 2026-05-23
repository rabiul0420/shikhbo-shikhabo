@extends('layouts.app', ['title' => 'Admin Dashboard'])

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
                <a class="admin-nav-link is-active" href="#dashboard">Dashboard</a>

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
                        <a class="admin-nav-link" href="#question-list">Question List</a>
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

                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>

            </nav>
        </aside>

        <div class="admin-content">
            <section id="question-list" class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Question</span>
                        <h2>Question List</h2>
                    </div>
                    <span class="count-badge">{{ $questions->count() }}</span>
                </div>

                @if ($questions->isEmpty())
                    <p class="muted">No questions added yet.</p>
                @else
                    <div class="table-wrap">
                        <table id="question-list-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Oddhay / Chapter</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($questions as $question)
                                    <tr>
                                        <td>{{ $question->question_text }}</td>
                                        <td>{{ $question->academicClass->name ?? '-' }}</td>
                                        <td>{{ $question->subject->name ?? '-' }}</td>
                                        <td>{{ $question->chapter->display_name ?? '-' }}</td>
                                        <td>
                                            <div class="table-actions">
                                                <button
                                                    class="secondary-action small js-edit-question"
                                                    type="button"
                                                    data-modal-target="edit-question-{{ $question->id }}"
                                                >Edit</button>
                                                <form method="POST" action="{{ route('questions.destroy', $question) }}" onsubmit="return confirm('Delete this question?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="danger small" type="submit">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            @foreach ($questions as $question)
                <div class="modal-backdrop" id="edit-question-{{ $question->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-question-{{ $question->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit Question</span>
                                <h3 id="edit-question-{{ $question->id }}-title">Update Question</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-question-{{ $question->id }}">Close</button>
                        </div>

                        <form class="stack" method="POST" action="{{ route('questions.update', $question) }}">
                            @csrf
                            @method('PATCH')
                            @php
                                $correctOption = $question->options->firstWhere('is_correct', true);
                                $correctOptionIndex = $correctOption ? $question->options->values()->search(fn ($option) => $option->id === $correctOption->id) : null;
                            @endphp
                            <label>
                                Question
                                <textarea name="question_text" placeholder="Write your question here">{{ old('question_text', $question->question_text) }}</textarea>
                            </label>

                            <div class="option-list">
                                @for ($i = 0; $i < 4; $i++)
                                    @php
                                        $option = $question->options->values()->get($i);
                                    @endphp
                                    <label class="option">
                                        <input type="radio" name="correct_option" value="{{ $i }}" @checked((string) old('correct_option', $correctOptionIndex) === (string) $i)>
                                        <input name="options[]" value="{{ old('options.' . $i, optional($option)->option_text) }}" placeholder="Option {{ $i + 1 }}">
                                    </label>
                                @endfor
                            </div>

                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-question-{{ $question->id }}">Cancel</button>
                                <button type="submit">Update question</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
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

                $('.admin-nav-link').on('click', function () {
                    $('.admin-nav-link').removeClass('is-active');
                    $(this).addClass('is-active');
                });

                if ($.fn.DataTable && $('#question-list-table').length) {
                    $('#question-list-table').DataTable({
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50],
                        order: [[0, 'asc']],
                        responsive: true,
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                        language: {
                            search: 'Search:',
                            lengthMenu: 'Show _MENU_ entries',
                            info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                            emptyTable: 'No question data available',
                        },
                    });
                }

                $(document).on('click', '.js-edit-question', function () {
                    const modalId = $(this).data('modal-target');
                    const $modal = $('#' + modalId);

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

                $(document).on('keyup', function (event) {
                    if (event.key === 'Escape') {
                        $('.modal-backdrop.is-open').removeClass('is-open').attr('aria-hidden', 'true');
                        $('body').removeClass('modal-open');
                    }
                });
            });
        }
    </script>
@endpush




