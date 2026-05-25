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

            </nav>
        </aside>

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Bulk Question</span>
                        <h2>Many Questions Add</h2>
                    </div>
                </div>

                <form class="stack question-entry-form" method="POST" action="{{ route('questions.bulk.store') }}">
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
                        Questions
                        <textarea name="bulk_questions" rows="14" placeholder="1. কোনো বস্তুর অবস্থান পরিবর্তনের হারকে কী বলে?

A) বল
B) বেগ
C) ত্বরণ
D) কাজ

উত্তর: B) বেগ">{{ old('bulk_questions') }}</textarea>
                    </label>

                    <button type="submit">Save all questions</button>
                </form>
            </section>

            @php
                $singleQuestionOpen = old('question_text') || old('options') || old('correct_option') !== null;
            @endphp

            <section class="panel single-question-panel {{ $singleQuestionOpen ? 'is-open' : '' }}" style="margin-top: 16px;">
                <div class="section-head">
                    <div class="single-question-title">
                        <span class="eyebrow">Question</span>
                        <h2>Question Add</h2>
                    </div>
                    <button
                        class="button secondary js-toggle-single-question"
                        type="button"
                        aria-expanded="{{ $singleQuestionOpen ? 'true' : 'false' }}"
                        aria-controls="single-question-form"
                    >
                        {{ $singleQuestionOpen ? 'Close single question' : 'Open single question' }}
                    </button>
                </div>

                <form
                    id="single-question-form"
                    class="stack question-entry-form single-question-form"
                    method="POST"
                    action="{{ route('questions.standalone.store') }}"
                    @unless ($singleQuestionOpen) hidden @endunless
                >
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

@push('styles')
    <style>
        .single-question-form[hidden] {
            display: none !important;
        }

        .single-question-panel:not(.is-open) .single-question-title {
            display: none;
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

                const chapterOptionsUrl = @json(route('admin.academic.chapters.options'));

                $('.question-entry-form').each(function () {
                    const $form = $(this);
                    const $classSelect = $form.find('select[name="academic_class_id"]');
                    const $subjectSelect = $form.find('select[name="subject_id"]');
                    const $chapterSelect = $form.find('select[name="chapter_id"]');

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

                $('.js-toggle-single-question').on('click', function () {
                    const $button = $(this);
                    const $panel = $button.closest('.single-question-panel');
                    const $form = $('#' + $button.attr('aria-controls'));
                    const isOpen = $button.attr('aria-expanded') === 'true';

                    $button.attr('aria-expanded', String(! isOpen));
                    $button.text(isOpen ? 'Open single question' : 'Close single question');
                    $panel.toggleClass('is-open', ! isOpen);

                    if (isOpen) {
                        $form.stop(true, true).slideUp(180, function () {
                            $form.prop('hidden', true).removeAttr('style');
                        });
                    } else {
                        $form.prop('hidden', false).hide().stop(true, true).slideDown(180, function () {
                            $form.removeAttr('style');
                        });
                    }
                });
            });
        }
    </script>
@endpush





