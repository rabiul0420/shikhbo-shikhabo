@extends('layouts.app', ['title' => 'Exam'])

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
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
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
                        <table id="exams-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Class</th>
                                    <th>Subject</th>
                                    <th>Oddhay / Chapter</th>
                                    <th>Schedule</th>
                                    <th>Duration</th>
                                    <th>Offer</th>
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
                                        <td>{{ $exam->chapter->display_name }}</td>
                                        <td>
                                            @if ($exam->starts_at && $exam->ends_at)
                                                <strong>{{ $exam->starts_at->format('M d, Y') }}</strong><br>
                                                <span class="muted">to {{ $exam->ends_at->format('M d, Y') }}</span>
                                            @else
                                                <span class="muted">Not scheduled</span>
                                            @endif
                                        </td>
                                        <td>{{ $exam->duration_minutes ? $exam->duration_minutes . ' mins' : '-' }}</td>
                                        <td>
                                            @if ($exam->hasPrizes())
                                                <span class="muted">
                                                    1st: {{ $exam->first_prize ?: '-' }}<br>
                                                    2nd: {{ $exam->second_prize ?: '-' }}<br>
                                                    3rd: {{ $exam->third_prize ?: '-' }}
                                                </span>
                                            @else
                                                <span class="muted">No offer</span>
                                            @endif
                                        </td>
                                        <td>{{ $exam->questions_count }}</td>
                                        <td>
                                            <div class="table-actions">
                                                <a class="button secondary small" href="{{ route('admin.exams.results', $exam) }}">Result</a>
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
                            Start date
                            <input type="date" name="starts_at" value="{{ old('starts_at') }}">
                        </label>
                        <label>
                            Deadline
                            <input type="date" name="ends_at" value="{{ old('ends_at') }}">
                        </label>
                        <label>
                            Duration (minutes)
                            <input type="number" name="duration_minutes" value="{{ old('duration_minutes', 30) }}" min="1" max="1440">
                        </label>
                        <label>
                            1st position gift
                            <input name="first_prize" value="{{ old('first_prize') }}" placeholder="Example: Trophy + certificate">
                        </label>
                        <label>
                            2nd position gift
                            <input name="second_prize" value="{{ old('second_prize') }}" placeholder="Example: Medal">
                        </label>
                        <label>
                            3rd position gift
                            <input name="third_prize" value="{{ old('third_prize') }}" placeholder="Example: Gift box">
                        </label>
                        <label>
                            Class
                            <select id="exam-class" class="js-exam-class" name="academic_class_id">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}" @selected((string) old('academic_class_id') === (string) $class->id)>{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subject
                            <select id="exam-subject" class="js-exam-subject" name="subject_id">
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Oddhay / Chapter
                            <select id="exam-chapter" class="js-exam-chapter" name="chapter_id" data-selected-chapter="{{ old('chapter_id') }}" disabled>
                                <option value="">Select class and subject first</option>
                            </select>
                        </label>
                        <label>
                            Question selection
                            <select class="js-question-selection-mode" name="question_selection_mode">
                                <option value="manual" @selected(old('question_selection_mode', 'manual') === 'manual')>Select manually</option>
                                <option value="random" @selected(old('question_selection_mode') === 'random')>Random questions</option>
                            </select>
                        </label>
                        <label class="js-random-question-count-field">
                            Random question count
                            <input class="js-random-question-count" type="number" name="random_question_count" value="{{ old('random_question_count') }}" min="1" max="500">
                        </label>
                        <label>
                            Questions
                            <select id="exam-questions" class="js-exam-questions" name="question_ids[]" multiple size="8">
                                @foreach ($questions as $question)
                                    <option
                                        value="{{ $question->id }}"
                                        data-class-id="{{ $question->academic_class_id }}"
                                        data-subject-id="{{ $question->subject_id }}"
                                        data-chapter-id="{{ $question->chapter_id }}"
                                        data-class="{{ $question->academicClass->name ?? '' }}"
                                        data-subject="{{ $question->subject->name ?? '' }}"
                                        data-chapter="{{ $question->chapter->display_name ?? '' }}"
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
                                Start date
                                <input type="date" name="starts_at" value="{{ old('starts_at', optional($exam->starts_at)->format('Y-m-d')) }}">
                            </label>
                            <label>
                                Deadline
                                <input type="date" name="ends_at" value="{{ old('ends_at', optional($exam->ends_at)->format('Y-m-d')) }}">
                            </label>
                            <label>
                                Duration (minutes)
                                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $exam->duration_minutes) }}" min="1" max="1440">
                            </label>
                            <label>
                                1st position gift
                                <input name="first_prize" value="{{ old('first_prize', $exam->first_prize) }}" placeholder="Example: Trophy + certificate">
                            </label>
                            <label>
                                2nd position gift
                                <input name="second_prize" value="{{ old('second_prize', $exam->second_prize) }}" placeholder="Example: Medal">
                            </label>
                            <label>
                                3rd position gift
                                <input name="third_prize" value="{{ old('third_prize', $exam->third_prize) }}" placeholder="Example: Gift box">
                            </label>
                            <label>
                                Class
                                <select class="js-exam-class" name="academic_class_id">
                                    <option value="">Select class</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected((string) $exam->academic_class_id === (string) $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Subject
                                <select class="js-exam-subject" name="subject_id">
                                    <option value="">Select subject</option>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected((string) $exam->subject_id === (string) $subject->id)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Oddhay / Chapter
                                <select class="js-exam-chapter" name="chapter_id" data-selected-chapter="{{ old('chapter_id', $exam->chapter_id) }}" disabled>
                                    <option value="">Select class and subject first</option>
                                </select>
                            </label>
                            <label>
                                Question selection
                                <select class="js-question-selection-mode" name="question_selection_mode">
                                    <option value="manual" @selected(old('question_selection_mode', 'manual') === 'manual')>Select manually</option>
                                    <option value="random" @selected(old('question_selection_mode') === 'random')>Random questions</option>
                                </select>
                            </label>
                            <label class="js-random-question-count-field">
                                Random question count
                                <input class="js-random-question-count" type="number" name="random_question_count" value="{{ old('random_question_count', $exam->questions_count) }}" min="1" max="500">
                            </label>
                            <label>
                                Questions
                                <select class="js-exam-questions" name="question_ids[]" multiple size="8">
                                    @foreach ($questions as $question)
                                        <option
                                            value="{{ $question->id }}"
                                            data-class-id="{{ $question->academic_class_id }}"
                                            data-subject-id="{{ $question->subject_id }}"
                                            data-chapter-id="{{ $question->chapter_id }}"
                                            data-class="{{ $question->academicClass->name ?? '' }}"
                                            data-subject="{{ $question->subject->name ?? '' }}"
                                            data-chapter="{{ $question->chapter->display_name ?? '' }}"
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

                if ($.fn.DataTable && $('#exams-table').length) {
                    $('#exams-table').DataTable({
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50],
                        order: [[0, 'asc']],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }

                const $class = $('#exam-class');
                const $subject = $('#exam-subject');
                const $chapter = $('#exam-chapter');
                const $questions = $('#exam-questions');
                const chapterOptionsUrl = @json(route('admin.academic.chapters.options'));

                function setChapterPlaceholder($chapterField, text) {
                    $chapterField.empty().append($('<option>', {
                        value: '',
                        text,
                    }));
                }

                function loadChapterOptions($form) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $chapterField = $form.find('.js-exam-chapter, #exam-chapter');
                    const selectedClass = $classField.val();
                    const selectedSubject = $subjectField.val();

                    if (! selectedClass || ! selectedSubject) {
                        setChapterPlaceholder($chapterField, 'Select class and subject first');
                        $chapterField.prop('disabled', true);
                        filterQuestions($form);
                        return;
                    }

                    const selectedChapter = String($chapterField.data('selected-chapter') || '');

                    setChapterPlaceholder($chapterField, 'Loading chapters...');
                    $chapterField.prop('disabled', true);

                    $.getJSON(chapterOptionsUrl, {
                        academic_class_id: selectedClass,
                        subject_id: selectedSubject,
                    }).done(function (response) {
                        const chapters = response.chapters || [];

                        setChapterPlaceholder($chapterField, chapters.length ? 'Select oddhay / chapter' : 'No chapter found');

                        chapters.forEach(function (chapter) {
                            $chapterField.append($('<option>', {
                                value: chapter.id,
                                text: chapter.name,
                            }).attr('data-chapter-name', chapter.name));
                        });

                        if (selectedChapter && $chapterField.find('option[value="' + selectedChapter + '"]').length) {
                            $chapterField.val(selectedChapter);
                        } else {
                            $chapterField.data('selected-chapter', '');
                        }

                        $chapterField.prop('disabled', chapters.length === 0);
                        filterQuestions($form);
                    }).fail(function () {
                        setChapterPlaceholder($chapterField, 'Could not load chapters');
                        $chapterField.prop('disabled', true);
                        filterQuestions($form);
                    });
                }

                function filterQuestions($form) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $chapterField = $form.find('.js-exam-chapter, #exam-chapter');
                    const $questionsField = $form.find('.js-exam-questions, #exam-questions');
                    const selectedClass = String($classField.val() || '');
                    const selectedSubject = String($subjectField.val() || '');
                    const selectedChapter = String($chapterField.val() || '');
                    const hasFullSelection = selectedClass && selectedSubject && selectedChapter;

                    $questionsField.find('option').each(function () {
                        const $option = $(this);
                        const matches = hasFullSelection
                            && String($option.data('class-id') || '') === selectedClass
                            && String($option.data('subject-id') || '') === selectedSubject
                            && String($option.data('chapter-id') || '') === selectedChapter;

                        $option.prop('hidden', ! matches);
                        $option.prop('disabled', ! matches);
                        if (! matches) {
                            $option.prop('selected', false);
                        }
                    });
                }

                function toggleQuestionSelectionMode($form) {
                    const mode = $form.find('.js-question-selection-mode').val() || 'manual';
                    const isRandom = mode === 'random';
                    const $questionsField = $form.find('.js-exam-questions, #exam-questions');
                    const $randomCountField = $form.find('.js-random-question-count-field');
                    const $randomCountInput = $form.find('.js-random-question-count');

                    $questionsField.closest('label').toggle(! isRandom);
                    $questionsField.prop('disabled', isRandom);
                    $randomCountField.toggle(isRandom);
                    $randomCountInput.prop('disabled', ! isRandom);
                }

                $('.js-exam-form, form[action="{{ route('exams.store') }}"]').each(function () {
                    const $form = $(this);
                    loadChapterOptions($form);
                    toggleQuestionSelectionMode($form);
                });

                $(document).on('change', '.js-question-selection-mode', function () {
                    toggleQuestionSelectionMode($(this).closest('form'));
                });

                $(document).on('change', '.js-exam-class, #exam-class, .js-exam-subject, #exam-subject', function () {
                    const $form = $(this).closest('form');
                    $form.find('.js-exam-chapter, #exam-chapter').data('selected-chapter', '');
                    loadChapterOptions($form);
                });

                $(document).on('change', '.js-exam-chapter, #exam-chapter', function () {
                    $(this).data('selected-chapter', $(this).val());
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





