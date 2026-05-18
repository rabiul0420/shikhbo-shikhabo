@extends('layouts.app', ['title' => 'Exam'])

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

                <div class="admin-menu-group is-open">
                    <button class="admin-menu-toggle" type="button">
                        Exam
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link is-active" href="#exam-list">Exam List</a>
                        <a class="admin-nav-link js-edit-exam" href="#add-exam" data-modal-target="add-exam">Add Exam</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Result
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.results.index') }}">All Results</a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="admin-content">
            <section id="exam-list" class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Exam</span>
                        <h2>Exam List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $exams->count() }}</span>
                        <button class="js-edit-exam" type="button" data-modal-target="add-exam">Add Exam</button>
                    </div>
                </div>

                @if ($exams->isEmpty())
                    <p class="muted">No exams added yet.</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Lesson</th>
                                    <th>Questions</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($exams as $exam)
                                    <tr>
                                        <td>{{ $exam->title }}</td>
                                        <td>{{ $exam->academicClass->name }}</td>
                                        <td>{{ $exam->subject->name }}</td>
                                        <td>{{ $exam->chapter->name }}</td>
                                        <td>{{ $exam->questions_count }}</td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="secondary-action small js-edit-exam" type="button" data-modal-target="edit-exam-{{ $exam->id }}">Edit</button>
                                                <form method="POST" action="{{ route('exams.destroy', $exam) }}" onsubmit="return confirm('Delete this exam?')">
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

            <div class="modal-backdrop" id="add-exam" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="add-exam-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Add Exam</span>
                            <h3 id="add-exam-title">New Exam</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="add-exam">Close</button>
                    </div>

                    <form class="stack js-exam-form" method="POST" action="{{ route('exams.store') }}">
                        @csrf
                        <label>
                            Title
                            <input name="title" value="{{ old('title') }}" placeholder="Write exam title">
                        </label>
                        <label>
                            Class
                            <select id="exam-class" class="js-exam-class" name="academic_class_id">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subject
                            <select id="exam-subject" class="js-exam-subject" name="subject_id">
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Lesson
                            <select id="exam-lesson" class="js-exam-lesson" name="chapter_id">
                                <option value="">Select lesson</option>
                                @foreach ($lessons as $lesson)
                                    <option
                                        value="{{ $lesson->id }}"
                                        data-class-id="{{ $lesson->academic_class_id }}"
                                        data-subject-id="{{ $lesson->subject_id }}"
                                        data-lesson-name="{{ $lesson->name }}"
                                    >
                                        {{ $lesson->academicClass->name }} / {{ $lesson->subject->name }} / {{ $lesson->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Questions
                            <select id="exam-questions" class="js-exam-questions" name="question_ids[]" multiple size="8">
                                @foreach ($questions as $question)
                                    <option
                                        value="{{ $question->id }}"
                                        data-class="{{ $question->academicClass->name ?? '' }}"
                                        data-subject="{{ $question->subject->name ?? '' }}"
                                        data-lesson="{{ $question->chapter->name ?? '' }}"
                                    >
                                        {{ $question->question_text }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-exam">Cancel</button>
                            <button type="submit">Save exam</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($exams as $exam)
                <div class="modal-backdrop" id="edit-exam-{{ $exam->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-exam-{{ $exam->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit Exam</span>
                                <h3 id="edit-exam-{{ $exam->id }}-title">{{ $exam->title }}</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-exam-{{ $exam->id }}">Close</button>
                        </div>

                        <form class="stack js-exam-form" method="POST" action="{{ route('exams.update', $exam) }}">
                            @csrf
                            @method('PATCH')
                            <label>
                                Title
                                <input name="title" value="{{ old('title', $exam->title) }}" placeholder="Write exam title">
                            </label>
                            <label>
                                Class
                                <select class="js-exam-class" name="academic_class_id">
                                    <option value="">Select class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected($exam->academic_class_id === $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Subject
                                <select class="js-exam-subject" name="subject_id">
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected($exam->subject_id === $subject->id)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Lesson
                                <select class="js-exam-lesson" name="chapter_id">
                                    <option value="">Select lesson</option>
                                    @foreach ($lessons as $lesson)
                                        <option
                                            value="{{ $lesson->id }}"
                                            data-class-id="{{ $lesson->academic_class_id }}"
                                            data-subject-id="{{ $lesson->subject_id }}"
                                            data-lesson-name="{{ $lesson->name }}"
                                            @selected($exam->chapter_id === $lesson->id)
                                        >
                                            {{ $lesson->academicClass->name }} / {{ $lesson->subject->name }} / {{ $lesson->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Questions
                                <select class="js-exam-questions" name="question_ids[]" multiple size="8">
                                    @foreach ($questions as $question)
                                        <option
                                            value="{{ $question->id }}"
                                            data-class="{{ $question->academicClass->name ?? '' }}"
                                            data-subject="{{ $question->subject->name ?? '' }}"
                                            data-lesson="{{ $question->chapter->name ?? '' }}"
                                            @selected($exam->questions->contains($question))
                                        >
                                            {{ $question->question_text }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-exam-{{ $exam->id }}">Cancel</button>
                                <button type="submit">Update exam</button>
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
    <script>
        if (window.jQuery) {
            $(function () {
                $('.admin-menu-toggle').on('click', function () {
                    const $group = $(this).closest('.admin-menu-group');
                    const $submenu = $group.children('.admin-submenu');

                    $group.toggleClass('is-open');
                    $submenu.stop(true, true).slideToggle(180);
                });

                const $class = $('#exam-class');
                const $subject = $('#exam-subject');
                const $lesson = $('#exam-lesson');
                const $questions = $('#exam-questions');

                function filterLessons($form) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $lessonField = $form.find('.js-exam-lesson, #exam-lesson');
                    const classId = $class.val();
                    const subjectId = $subject.val();

                    $lessonField.find('option').each(function () {
                        const $option = $(this);
                        const isPlaceholder = ! $option.val();
                        const matches = isPlaceholder || (
                            (! $classField.val() || $option.data('class-id') == $classField.val())
                            && (! $subjectField.val() || $option.data('subject-id') == $subjectField.val())
                        );

                        $option.prop('hidden', ! matches);
                    });

                    filterQuestions($form);
                }

                function filterQuestions($form) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $lessonField = $form.find('.js-exam-lesson, #exam-lesson');
                    const $questionsField = $form.find('.js-exam-questions, #exam-questions');
                    const selectedLesson = $lessonField.find(':selected');
                    const lessonText = selectedLesson.data('lesson-name');
                    const classText = $classField.find(':selected').text().trim();
                    const subjectText = $subjectField.find(':selected').text().trim();
                    const hasFullSelection = $classField.val() && $subjectField.val() && $lessonField.val();

                    $questionsField.find('option').each(function () {
                        const $option = $(this);
                        const matches = hasFullSelection
                            && $option.data('class') === classText
                            && $option.data('subject') === subjectText
                            && $option.data('lesson') === lessonText;

                        $option.prop('hidden', ! matches);
                        if (! matches) {
                            $option.prop('selected', false);
                        }
                    });
                }

                $('.js-exam-form, form[action="{{ route('exams.store') }}"]').each(function () {
                    const $form = $(this);
                    filterLessons($form);
                    filterQuestions($form);
                });

                $(document).on('change', '.js-exam-class, #exam-class, .js-exam-subject, #exam-subject', function () {
                    const $form = $(this).closest('form');
                    $form.find('.js-exam-lesson, #exam-lesson').val('');
                    filterLessons($form);
                });

                $(document).on('change', '.js-exam-lesson, #exam-lesson', function () {
                    filterQuestions($(this).closest('form'));
                });

                $(document).on('click', '.js-edit-exam', function (event) {
                    event.preventDefault();
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

