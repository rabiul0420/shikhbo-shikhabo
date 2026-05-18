@extends('layouts.app', ['title' => 'Question Add'])

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
                        Result
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a class="admin-nav-link" href="{{ route('admin.results.index') }}">All Results</a>
                    </div>
                </div>

                <div class="admin-menu-group">
                    <button class="admin-menu-toggle" type="button">
                        Site
                        <span></span>
                    </button>
                    <div class="admin-submenu">
                        <a href="{{ route('home') }}">Public Exams</a>
                    </div>
                </div>
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
                        <select name="chapter_id">
                            <option value="">Select oddhay / chapter</option>
                            @foreach ($lessons as $lesson)
                                <option value="{{ $lesson->id }}" @selected((string) old('chapter_id') === (string) $lesson->id)>
                                    {{ $lesson->academicClass->name }} / {{ $lesson->subject->name }} / {{ $lesson->name }}
                                </option>
                            @endforeach
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
            });
        }
    </script>
@endpush

