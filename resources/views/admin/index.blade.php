@extends('layouts.app', ['title' => 'Admin Dashboard'])

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
            <section id="question-list" class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Question</span>
                        <h2>Question List</h2>
                    </div>
                    <span class="count-badge">{{ $questions->total() }}</span>
                </div>

                <form class="question-search-form" method="GET" action="{{ route('admin.index') }}#question-list">
                    <label>
                        Search
                        <input name="question_search" value="{{ $search }}" placeholder="Search question, class, subject, chapter">
                    </label>
                    <div class="question-search-actions">
                        <button type="submit">Search</button>
                        @if ($search !== '')
                            <a class="button secondary" href="{{ route('admin.index') }}#question-list">Clear</a>
                        @endif
                    </div>
                </form>

                @if ($questions->isEmpty())
                    <p class="muted">{{ $search === '' ? 'No questions added yet.' : 'No questions matched your search.' }}</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
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
                    @if ($questions->hasPages())
                        <nav class="pagination-wrap" aria-label="Question list pagination">
                            @if ($questions->onFirstPage())
                                <span class="pagination-button is-disabled">Previous</span>
                            @else
                                <a class="pagination-button" href="{{ $questions->previousPageUrl() }}#question-list">Previous</a>
                            @endif

                            <span class="pagination-status">Page {{ $questions->currentPage() }} of {{ $questions->lastPage() }}</span>

                            @if ($questions->hasMorePages())
                                <a class="pagination-button" href="{{ $questions->nextPageUrl() }}#question-list">Next</a>
                            @else
                                <span class="pagination-button is-disabled">Next</span>
                            @endif
                        </nav>
                    @endif
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

@push('styles')
    <style>
        .pagination-wrap {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .pagination-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 7px 12px;
            border: 1px solid var(--line);
            background: #fff;
            color: var(--text);
            font-weight: 700;
        }

        .pagination-button:not(.is-disabled):hover {
            background: #f8f9fa;
        }

        .pagination-button.is-disabled {
            color: var(--muted);
            opacity: .55;
            cursor: not-allowed;
        }

        .pagination-status {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .question-search-form {
            display: grid;
            grid-template-columns: minmax(220px, 1fr) auto;
            align-items: end;
            gap: 10px;
            margin-bottom: 14px;
        }

        .question-search-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        @media (max-width: 760px) {
            .question-search-form {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

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

                $('.admin-nav-link').on('click', function () {
                    $('.admin-nav-link').removeClass('is-active');
                    $(this).addClass('is-active');
                });

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



