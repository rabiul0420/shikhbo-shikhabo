@extends('layouts.app', ['title' => 'Exam'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
    <style>
        .filter-row { margin-bottom: 12px; }
        .filter-row select { width: min(240px, 100%); }
    </style>
@endpush

@section('content')
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-brand">
                <img class="admin-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
                <div>
                    <h2>Bd ModelTest Admin</h2>
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
            <section id="exam-list" class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Exam</span>
                        <h2>Exam List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $examCount }}</span>
                        <button class="js-edit-exam" type="button" data-modal-target="add-exam">Add Exam</button>
                    </div>
                </div>

                <div class="row filter-row">
                    <select id="filter-class" aria-label="Filter by class">
                        <option value="">All classes</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}">{{ $class->name }}</option>
                        @endforeach
                    </select>
                    <select id="filter-subject" aria-label="Filter by subject">
                        <option value="">All subjects</option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

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
                    </table>
                </div>
                <div hidden>
                    @foreach ($resultExamIds as $examId)
                        <a href="{{ route('admin.exams.results', $examId) }}">Result</a>
                    @endforeach
                </div>
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
                                    <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
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
                                <option value="">Select class, subject, and chapter first</option>
                            </select>
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-exam">Cancel</button>
                            <button type="submit">Save exam</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-backdrop" id="edit-exam" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-exam-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Edit Exam</span>
                            <h3 id="edit-exam-title">Loading...</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-exam">Close</button>
                    </div>

                    <form class="stack js-exam-form" method="POST" action="">
                        @csrf
                        @method('PATCH')
                        <label>
                            Title
                            <input name="title" placeholder="Write exam title">
                        </label>
                        <label>
                            Start date
                            <input type="date" name="starts_at">
                        </label>
                        <label>
                            Deadline
                            <input type="date" name="ends_at">
                        </label>
                        <label>
                            Duration (minutes)
                            <input type="number" name="duration_minutes" min="1" max="1440">
                        </label>
                        <label>
                            1st position gift
                            <input name="first_prize" placeholder="Example: Trophy + certificate">
                        </label>
                        <label>
                            2nd position gift
                            <input name="second_prize" placeholder="Example: Medal">
                        </label>
                        <label>
                            3rd position gift
                            <input name="third_prize" placeholder="Example: Gift box">
                        </label>
                        <label>
                            Class
                            <select class="js-exam-class" name="academic_class_id">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subject
                            <select class="js-exam-subject" name="subject_id">
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Oddhay / Chapter
                            <select class="js-exam-chapter" name="chapter_id" disabled>
                                <option value="">Select class and subject first</option>
                            </select>
                        </label>
                        <label>
                            Question selection
                            <select class="js-question-selection-mode" name="question_selection_mode">
                                <option value="manual">Select manually</option>
                                <option value="random">Random questions</option>
                            </select>
                        </label>
                        <label class="js-random-question-count-field">
                            Random question count
                            <input class="js-random-question-count" type="number" name="random_question_count" min="1" max="500">
                        </label>
                        <label>
                            Questions
                            <select class="js-exam-questions" name="question_ids[]" multiple size="8">
                                <option value="">Select class, subject, and chapter first</option>
                            </select>
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="edit-exam">Cancel</button>
                            <button type="submit">Update exam</button>
                        </div>
                    </form>
                </div>
            </div>
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
                    const examsTable = $('#exams-table').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: @json(route('admin.exams.data')),
                            data: function (d) {
                                d.class_id = $('#filter-class').val();
                                d.subject_id = $('#filter-subject').val();
                            },
                        },
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50],
                        order: [[0, 'asc']],
                        columns: [
                            { data: 'title', name: 'title' },
                            { data: 'academic_class', name: 'academic_class' },
                            { data: 'subject', name: 'subject' },
                            { data: 'chapter', name: 'chapter' },
                            { data: 'schedule', name: 'starts_at' },
                            { data: 'duration', name: 'duration_minutes' },
                            { data: 'offer', name: 'offer' },
                            { data: 'questions_count', name: 'questions_count' },
                            { data: 'actions', name: 'actions' },
                        ],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                            { orderable: false, targets: 6 },
                        ],
                    });

                    function filterSubjectOptionsByClass() {
                        const selectedClass = String($('#filter-class').val() || '');
                        let subjectOptionVisible = false;

                        $('#filter-subject option').each(function () {
                            const $option = $(this);

                            if (! $option.val()) {
                                $option.prop('hidden', false);
                                return;
                            }

                            const optionClasses = String($option.data('class-ids') || '').split(',').filter(Boolean);
                            const isVisible = optionClasses.includes(selectedClass);
                            $option.prop('hidden', ! isVisible);

                            if ($option.is(':selected') && isVisible) {
                                subjectOptionVisible = true;
                            }
                        });

                        if (! subjectOptionVisible) {
                            $('#filter-subject').val('');
                        }
                    }

                    $('#filter-class').on('change', function () {
                        filterSubjectOptionsByClass();
                        examsTable.ajax.reload(null, false);
                    });

                    $('#filter-subject').on('change', function () {
                        examsTable.ajax.reload(null, false);
                    });
                }

                const chapterOptionsUrl = @json(route('admin.academic.chapters.options'));
                const questionOptionsUrl = @json(route('admin.exams.questions.options'));

                function setChapterPlaceholder($chapterField, text) {
                    $chapterField.empty().append($('<option>', {
                        value: '',
                        text,
                    }));
                }

                function setQuestionsPlaceholder($questionsField, text) {
                    $questionsField.empty().append($('<option>', {
                        value: '',
                        text,
                        disabled: true,
                    }));
                }

                function filterSubjectOptions($form) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const selectedClass = String($classField.val() || '');
                    let selectedOptionVisible = false;

                    $subjectField.find('option').each(function () {
                        const $option = $(this);

                        if (! $option.val()) {
                            $option.prop('hidden', false);
                            return;
                        }

                        const optionClasses = String($option.data('class-ids') || '').split(',').filter(Boolean);
                        const isVisible = optionClasses.includes(selectedClass);
                        $option.prop('hidden', ! isVisible);

                        if ($option.is(':selected') && isVisible) {
                            selectedOptionVisible = true;
                        }
                    });

                    if (! selectedOptionVisible) {
                        $subjectField.val('');
                    }
                }

                function loadChapterOptions($form, selectedQuestionIds = []) {
                    filterSubjectOptions($form);
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $chapterField = $form.find('.js-exam-chapter, #exam-chapter');
                    const $questionsField = $form.find('.js-exam-questions, #exam-questions');
                    const selectedClass = $classField.val();
                    const selectedSubject = $subjectField.val();

                    if (! selectedClass || ! selectedSubject) {
                        setChapterPlaceholder($chapterField, 'Select class and subject first');
                        $chapterField.prop('disabled', true);
                        setQuestionsPlaceholder($questionsField, 'Select class, subject, and chapter first');
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
                        loadQuestionOptions($form, selectedQuestionIds);
                    }).fail(function () {
                        setChapterPlaceholder($chapterField, 'Could not load chapters');
                        $chapterField.prop('disabled', true);
                        setQuestionsPlaceholder($questionsField, 'Could not load questions');
                    });
                }

                function loadQuestionOptions($form, selectedQuestionIds = []) {
                    const $classField = $form.find('.js-exam-class, #exam-class');
                    const $subjectField = $form.find('.js-exam-subject, #exam-subject');
                    const $chapterField = $form.find('.js-exam-chapter, #exam-chapter');
                    const $questionsField = $form.find('.js-exam-questions, #exam-questions');
                    const selectedClass = $classField.val();
                    const selectedSubject = $subjectField.val();
                    const selectedChapter = $chapterField.val();
                    const selectedIds = (selectedQuestionIds || []).map(String);

                    if (! selectedClass || ! selectedSubject || ! selectedChapter) {
                        setQuestionsPlaceholder($questionsField, 'Select class, subject, and chapter first');
                        return;
                    }

                    setQuestionsPlaceholder($questionsField, 'Loading questions...');

                    $.getJSON(questionOptionsUrl, {
                        academic_class_id: selectedClass,
                        subject_id: selectedSubject,
                        chapter_id: selectedChapter,
                    }).done(function (response) {
                        const questions = response.questions || [];

                        $questionsField.empty();

                        if (! questions.length) {
                            setQuestionsPlaceholder($questionsField, 'No question found');
                            return;
                        }

                        questions.forEach(function (question) {
                            $questionsField.append($('<option>', {
                                value: question.id,
                                text: question.text,
                                selected: selectedIds.includes(String(question.id)),
                            }));
                        });
                    }).fail(function () {
                        setQuestionsPlaceholder($questionsField, 'Could not load questions');
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
                    if ($(this).is('.js-exam-class, #exam-class')) {
                        filterSubjectOptions($form);
                    }
                    $form.find('.js-exam-chapter, #exam-chapter').data('selected-chapter', '');
                    loadChapterOptions($form);
                });

                $(document).on('change', '.js-exam-chapter, #exam-chapter', function () {
                    $(this).data('selected-chapter', $(this).val());
                    loadQuestionOptions($(this).closest('form'));
                });

                $(document).on('click', '.js-edit-exam', function (event) {
                    event.preventDefault();
                    const modalId = $(this).data('modal-target');
                    const $modal = $('#' + modalId);

                    if (modalId === 'edit-exam') {
                        const editUrl = $(this).data('edit-url');
                        const $form = $modal.find('form');

                        $('#edit-exam-title').text('Loading...');
                        $form.find('input[name="title"], input[name="starts_at"], input[name="ends_at"], input[name="duration_minutes"], input[name="first_prize"], input[name="second_prize"], input[name="third_prize"]').val('');
                        setChapterPlaceholder($form.find('.js-exam-chapter'), 'Select class and subject first');
                        setQuestionsPlaceholder($form.find('.js-exam-questions'), 'Loading exam...');

                        $.getJSON(editUrl).done(function (response) {
                            const exam = response.exam || {};

                            $('#edit-exam-title').text(exam.title || 'Edit Exam');
                            $form.attr('action', exam.update_url || '');
                            $form.find('[name="title"]').val(exam.title || '');
                            $form.find('[name="starts_at"]').val(exam.starts_at || '');
                            $form.find('[name="ends_at"]').val(exam.ends_at || '');
                            $form.find('[name="duration_minutes"]').val(exam.duration_minutes || '');
                            $form.find('[name="first_prize"]').val(exam.first_prize || '');
                            $form.find('[name="second_prize"]').val(exam.second_prize || '');
                            $form.find('[name="third_prize"]').val(exam.third_prize || '');
                            $form.find('[name="academic_class_id"]').val(exam.academic_class_id || '');
                            $form.find('[name="subject_id"]').val(exam.subject_id || '');
                            $form.find('[name="chapter_id"]').data('selected-chapter', exam.chapter_id || '');
                            $form.find('[name="question_selection_mode"]').val('manual');
                            $form.find('[name="random_question_count"]').val(exam.questions_count || '');
                            toggleQuestionSelectionMode($form);
                            loadChapterOptions($form, exam.question_ids || []);
                        }).fail(function () {
                            $('#edit-exam-title').text('Could not load exam');
                        });
                    }

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
