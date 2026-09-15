@extends('layouts.app', ['title' => 'Admin Dashboard'])

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar')

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
                        Class
                        <select name="class_id" id="question-filter-class">
                            <option value="">All classes</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected(($filters['class_id'] ?? '') == $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Subject
                        <select name="subject_id" id="question-filter-subject">
                            <option value="">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}" @selected(($filters['subject_id'] ?? '') == $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Chapter
                        <select name="chapter_id" id="question-filter-chapter">
                            <option value="">All chapters</option>
                            @foreach ($chapters as $chapter)
                                <option value="{{ $chapter->id }}" data-class-id="{{ $chapter->academic_class_id }}" data-subject-id="{{ $chapter->subject_id }}" @selected(($filters['chapter_id'] ?? '') == $chapter->id)>{{ $chapter->display_name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Created by
                        <select name="creator_id" id="question-filter-creator">
                            <option value="">All creators</option>
                            @foreach ($creators as $creator)
                                <option value="{{ $creator->id }}" @selected(($filters['creator_id'] ?? '') == $creator->id)>{{ $creator->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Search
                        <input name="question_search" value="{{ $search }}" placeholder="Search question, class, subject, chapter, creator">
                    </label>
                    <div class="question-search-actions">
                        <button type="submit">Search</button>
                        @if ($hasFilters)
                            <a class="button secondary" href="{{ route('admin.index') }}#question-list">Clear</a>
                        @endif
                    </div>
                </form>

                @if ($questions->isEmpty())
                    <p class="muted">{{ $hasFilters ? 'No questions matched your search or filters.' : 'No questions added yet.' }}</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Question</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Oddhay / Chapter</th>
                                    <th>Created by</th>
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
                                        <td>{{ $question->creator?->name ?? 'Unknown' }}</td>
                                        <td>
                                            @if ($question->canBeManagedBy(auth()->user()))
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
                                            @else
                                                <span class="muted">Read only</span>
                                            @endif
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
                @continue(! $question->canBeManagedBy(auth()->user()))
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
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
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
        const questionFilterClass = document.getElementById('question-filter-class');
        const questionFilterSubject = document.getElementById('question-filter-subject');
        const questionFilterChapter = document.getElementById('question-filter-chapter');
        const questionFilterCreator = document.getElementById('question-filter-creator');

        function updateQuestionFilterOptions() {
            for (const option of questionFilterSubject.options) {
                option.hidden = Boolean(option.value && questionFilterClass.value
                    && !(option.dataset.classIds || '').split(',').includes(questionFilterClass.value));
            }
            for (const option of questionFilterChapter.options) {
                option.hidden = Boolean(option.value && (
                    (questionFilterClass.value && option.dataset.classId !== questionFilterClass.value)
                    || (questionFilterSubject.value && option.dataset.subjectId !== questionFilterSubject.value)
                ));
            }
        }

        questionFilterClass.addEventListener('change', () => {
            questionFilterSubject.value = '';
            questionFilterChapter.value = '';
            questionFilterClass.form.requestSubmit();
        });
        questionFilterSubject.addEventListener('change', () => {
            questionFilterChapter.value = '';
            questionFilterSubject.form.requestSubmit();
        });
        for (const select of [questionFilterChapter, questionFilterCreator]) {
            select.addEventListener('change', () => select.form.requestSubmit());
        }
        updateQuestionFilterOptions();

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
