@extends('layouts.app', ['title' => 'Customize Exam'])

@push('styles')
    <style>
        .custom-exam-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
            padding: 20px;
            border: 1px solid #dfe5ec;
            background: #ffffff;
        }

        .custom-exam-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr)) auto;
            gap: 12px;
            align-items: end;
        }

        .custom-exam-list { display: grid; gap: 12px; margin-top: 18px; }
        .custom-exam-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 14px;
            border: 1px solid #dcdcdc;
            background: #ffffff;
        }

        .custom-exam-card h2 { font-size: 20px; margin-bottom: 7px; }
        .custom-exam-actions { display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap; }
        .question-count-note {
            display: inline-flex;
            align-items: center;
            min-height: 22px;
            margin-left: 8px;
            padding: 2px 8px;
            border: 1px solid #fecaca;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 800;
        }

        .question-count-note[hidden] { display: none; }

        @media (max-width: 760px) {
            .custom-exam-hero,
            .custom-exam-card,
            .custom-exam-form { grid-template-columns: 1fr; }
            .custom-exam-actions { justify-content: flex-start; }
        }
    </style>
@endpush

@section('content')
    <div class="custom-exam-hero">
        <div>
            <h1>Customize Exam</h1>
            <p class="muted">
                Your class: <strong>{{ auth()->user()->academicClass->name ?? 'Not selected' }}</strong>
            </p>
        </div>
        <a class="button secondary" href="{{ route('custom-exams.results') }}">Custom Results</a>
    </div>

    <section class="panel stack">
        <h2>Create custom exam</h2>
        <form class="custom-exam-form" method="POST" action="{{ route('custom-exams.store') }}">
            @csrf
            <label>
                Subject
                <select id="custom-subject" name="subject_id" required>
                    <option value="">Select subject</option>
                    @foreach ($subjects as $subject)
                        <option value="{{ $subject->id }}" @selected((int) old('subject_id') === (int) $subject->id)>
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                Chapter
                <select id="custom-chapter" name="chapter_id" required data-selected="{{ old('chapter_id') }}">
                    <option value="">Select chapter</option>
                </select>
            </label>
            <label>
                <span>
                    Questions
                    <span id="available-question-count" class="question-count-note" hidden></span>
                </span>
                <input type="number" name="question_count" min="1" max="100" value="{{ old('question_count', 10) }}" required>
            </label>
            <button type="submit">Create exam</button>
        </form>
    </section>

    <section class="custom-exam-list" aria-label="Recent custom exams">
        <div class="between">
            <h2>Recent custom exams</h2>
        </div>

        @forelse ($customExams as $customExam)
            @php
                $attempt = $customExam->attempts->first();
            @endphp
            <article class="custom-exam-card">
                <div>
                    <h2>{{ $customExam->title }}</h2>
                    <div class="row">
                        <span class="pill">{{ $customExam->subject->name }}</span>
                        <span class="pill">{{ $customExam->chapter->display_name }}</span>
                        <span class="pill">{{ $customExam->question_count }} questions</span>
                        <span class="pill">{{ $customExam->total_marks }} marks</span>
                    </div>
                </div>
                <div class="custom-exam-actions">
                    @if ($attempt)
                        <a class="button secondary small" href="{{ route('custom-exam-attempts.result', $attempt) }}">View result</a>
                    @else
                        <a class="button small" href="{{ route('custom-exams.show', $customExam) }}">Start exam</a>
                    @endif
                </div>
            </article>
        @empty
            <section class="panel">
                <p class="muted">No custom exams yet.</p>
            </section>
        @endforelse
    </section>
@endsection

@push('scripts')
    <script>
        const subjectSelect = document.getElementById('custom-subject');
        const chapterSelect = document.getElementById('custom-chapter');
        const availableQuestionCount = document.getElementById('available-question-count');

        function updateQuestionCountNote(text = '') {
            if (! availableQuestionCount) {
                return;
            }

            availableQuestionCount.textContent = text;
            availableQuestionCount.hidden = ! text;
        }

        async function loadCustomChapters() {
            if (! subjectSelect || ! chapterSelect) {
                return;
            }

            const subjectId = subjectSelect.value;
            const selectedChapterId = chapterSelect.dataset.selected;
            chapterSelect.innerHTML = '<option value="">Select chapter</option>';
            updateQuestionCountNote();

            if (! subjectId) {
                return;
            }

            const response = await fetch(`{{ route('custom-exams.chapters.options') }}?subject_id=${encodeURIComponent(subjectId)}`);
            const data = await response.json();

            data.chapters.forEach((chapter) => {
                const option = document.createElement('option');
                option.value = chapter.id;
                option.textContent = chapter.name;
                if (selectedChapterId && String(chapter.id) === String(selectedChapterId)) {
                    option.selected = true;
                }
                chapterSelect.appendChild(option);
            });

            chapterSelect.dataset.selected = '';
            loadQuestionCount();
        }

        async function loadQuestionCount() {
            if (! subjectSelect || ! chapterSelect || ! subjectSelect.value || ! chapterSelect.value) {
                updateQuestionCountNote();
                return;
            }

            updateQuestionCountNote('Loading...');

            const params = new URLSearchParams({
                subject_id: subjectSelect.value,
                chapter_id: chapterSelect.value,
            });
            const response = await fetch(`{{ route('custom-exams.questions.count') }}?${params.toString()}`);
            const data = await response.json();
            const count = Number(data.count || 0);

            updateQuestionCountNote(`${count} available`);
        }

        subjectSelect?.addEventListener('change', loadCustomChapters);
        chapterSelect?.addEventListener('change', loadQuestionCount);
        loadCustomChapters();
    </script>
@endpush
