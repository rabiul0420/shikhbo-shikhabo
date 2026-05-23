@extends('layouts.app', ['title' => 'Question Add'])

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

                <div class="admin-menu-group is-open">
                    <button class="admin-menu-toggle" type="button">
                        Question
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.index') }}#question-list">Question List</a>
                        <a class="admin-nav-link is-active" href="{{ route('admin.questions.create') }}">Question Add</a>
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
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Question</span>
                        <h2>Question Add</h2>
                    </div>
                </div>

                <form class="stack" method="POST" action="{{ route('questions.standalone.store') }}">
                    @csrf
                    <label>
                        Class
                        <select name="academic_class_id">
                            <option value="">Select class</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) old('academic_class_id') === (string) $class->id)>{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Subject
                        <select name="subject_id">
                            <option value="">Select subject</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) old('subject_id') === (string) $subject->id)>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label>
                        Oddhay / Chapter
                        <select name="chapter_id" data-selected-chapter="{{ old('chapter_id') }}" disabled>
                            <option value="">Select class and subject first</option>
                        </select>
                    </label>
                    <label>
                        Question
                        <textarea name="question_text" placeholder="Write your question here">{{ old('question_text') }}</textarea>
                    </label>

                    <div class="option-list">
                        @for ($i = 0; $i < 4; $i++)
                            <label class="option">
                                <input type="radio" name="correct_option" value="{{ $i }}" @checked(old('correct_option') == $i)>
                                <input name="options[]" value="{{ old('options.' . $i) }}" placeholder="Option {{ $i + 1 }}">
                            </label>
                        @endfor
                    </div>

                    <button type="submit">Save question</button>
                </form>
            </section>
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

                const $classSelect = $('select[name="academic_class_id"]');
                const $subjectSelect = $('select[name="subject_id"]');
                const $chapterSelect = $('select[name="chapter_id"]');
                const chapterOptionsUrl = @json(route('admin.academic.chapters.options'));

                function setChapterPlaceholder(text) {
                    $chapterSelect.empty().append($('<option>', {
                        value: '',
                        text,
                    }));
                }

                function loadChapterOptions() {
                    const selectedClass = $classSelect.val();
                    const selectedSubject = $subjectSelect.val();

                    if (! selectedClass || ! selectedSubject) {
                        setChapterPlaceholder('Select class and subject first');
                        $chapterSelect.prop('disabled', true);
                        return;
                    }

                    const selectedChapter = String($chapterSelect.data('selected-chapter') || '');

                    setChapterPlaceholder('Loading chapters...');
                    $chapterSelect.prop('disabled', true);

                    $.getJSON(chapterOptionsUrl, {
                        academic_class_id: selectedClass,
                        subject_id: selectedSubject,
                    }).done(function (response) {
                        const chapters = response.chapters || [];

                        setChapterPlaceholder(chapters.length ? 'Select oddhay / chapter' : 'No chapter found');

                        chapters.forEach(function (chapter) {
                            $chapterSelect.append($('<option>', {
                                value: chapter.id,
                                text: chapter.name,
                            }));
                        });

                        if (selectedChapter && $chapterSelect.find('option[value="' + selectedChapter + '"]').length) {
                            $chapterSelect.val(selectedChapter);
                        } else {
                            $chapterSelect.data('selected-chapter', '');
                        }

                        $chapterSelect.prop('disabled', chapters.length === 0);
                    }).fail(function () {
                        setChapterPlaceholder('Could not load chapters');
                        $chapterSelect.prop('disabled', true);
                    });
                }

                $classSelect.on('change', function () {
                    $chapterSelect.data('selected-chapter', '');
                    loadChapterOptions();
                });

                $subjectSelect.on('change', function () {
                    $chapterSelect.data('selected-chapter', '');
                    loadChapterOptions();
                });

                loadChapterOptions();
            });
        }
    </script>
@endpush





