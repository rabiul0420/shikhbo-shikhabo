@extends('layouts.app', ['title' => $exam->title])

@push('styles')
    <style>
        .gift-offer-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .gift-offer-item {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 10px;
            align-items: center;
            min-height: 58px;
            padding: 12px;
            border: 1px solid #dbe4ea;
            background: #fbfcfd;
        }

        .gift-offer-item p {
            margin: 0;
            font-weight: 800;
            line-height: 1.35;
        }

        @media (max-width: 760px) {
            .gift-offer-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $exam->title }}</h1>
            <div class="row">
                <span class="pill">{{ $exam->academicClass->name }}</span>
                <span class="pill">{{ $exam->subject->name }}</span>
            <span class="pill">{{ $exam->chapter->display_name }}</span>
                <span class="pill">{{ $exam->questions->count() }} questions</span>
                <span class="pill">{{ $exam->questions->sum('marks') }} marks</span>
                @if ($exam->duration_minutes)
                    <span class="pill">{{ $exam->duration_minutes }} mins</span>
                @endif
                <span class="pill">{{ ucfirst($exam->scheduleStatus()) }}</span>
            </div>
            <p class="muted">
                Starts: {{ $exam->starts_at ? $exam->starts_at->format('M d, Y') : 'Not scheduled' }}
                | Deadline: {{ $exam->ends_at ? $exam->ends_at->format('M d, Y') : 'Not scheduled' }}
            </p>
        </div>
        <a class="button secondary" href="{{ route('home') }}">Back</a>
    </div>

    @if ($exam->questions->isEmpty())
        <section class="panel">
            <h2>This exam has no questions yet</h2>
        </section>
    @else
        @if ($exam->hasPrizes())
            <section class="panel stack">
                <h2>Gift offer</h2>
                <div class="gift-offer-grid">
                    @if ($exam->first_prize)
                        <div class="gift-offer-item">
                            <span class="pill">1st</span>
                            <p>{{ $exam->first_prize }}</p>
                        </div>
                    @endif
                    @if ($exam->second_prize)
                        <div class="gift-offer-item">
                            <span class="pill">2nd</span>
                            <p>{{ $exam->second_prize }}</p>
                        </div>
                    @endif
                    @if ($exam->third_prize)
                        <div class="gift-offer-item">
                            <span class="pill">3rd</span>
                            <p>{{ $exam->third_prize }}</p>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

    @if (! $exam->questions->isEmpty() && $existingAttempt)
        <section class="panel stack">
            <h2>You have already participated in this exam</h2>
            <p class="muted">Each student can participate in an exam one time only.</p>
            <a class="button" href="{{ route('exam-attempts.result', $existingAttempt) }}">View result</a>
        </section>
    @elseif (! $exam->questions->isEmpty() && auth()->check() && $exam->isRunning())
        @if ($exam->duration_minutes && $attemptStartedAt)
            @php
                $timerDeadline = \Illuminate\Support\Carbon::parse($attemptStartedAt)->addMinutes($exam->duration_minutes);
                $remainingSeconds = max(0, now()->diffInSeconds($timerDeadline, false));
            @endphp
            <section class="panel">
                <h2>Time remaining</h2>
                <p class="muted">
                    Finish this exam within {{ $exam->duration_minutes }} minutes.
                    <strong id="exam-timer" data-remaining-seconds="{{ $remainingSeconds }}"></strong>
                </p>
            </section>
        @endif

        <form id="exam-form" class="stack" method="POST" action="{{ route('exams.submit', $exam) }}" data-auto-submit="true">
            @csrf
            <input id="auto-submitted" type="hidden" name="auto_submitted" value="0">
            @foreach ($exam->questions as $question)
                <section class="panel stack">
                    <div class="between">
                        <h2>{{ $loop->iteration }}. {{ $question->question_text }}</h2>
                        <span class="pill">{{ $question->marks }} marks</span>
                    </div>
                    <div class="stack">
                        @foreach ($question->options as $option)
                            <label class="option">
                                <input
                                    type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                    name="answers[{{ $question->id }}][]"
                                    value="{{ $option->id }}"
                                >
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
            <button type="submit">Submit answers</button>
        </form>
    @elseif (! $exam->questions->isEmpty())
        <div class="stack">
            <section class="panel stack">
                <h2>Questions preview</h2>
                @guest
                    <p class="muted">You can read the questions without logging in. Login is required to submit answers and get a result.</p>
                    <a class="button" href="{{ route('login') }}">Login to participate</a>
                @else
                    <p class="muted">This exam is {{ $exam->scheduleStatus() }}. You can read the questions, but participation is available only while the exam is running.</p>
                @endguest
            </section>

            @foreach ($exam->questions as $question)
                <section class="panel stack">
                    <div class="between">
                        <h2>{{ $loop->iteration }}. {{ $question->question_text }}</h2>
                        <span class="pill">{{ $question->marks }} marks</span>
                    </div>
                    <div class="stack">
                        @foreach ($question->options as $option)
                            <label class="option">
                                <input
                                    type="{{ $question->type === 'multiple_choice' ? 'checkbox' : 'radio' }}"
                                    disabled
                                >
                                <span>{{ $option->option_text }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const examTimer = document.getElementById('exam-timer');
        const examForm = document.getElementById('exam-form');
        const autoSubmittedInput = document.getElementById('auto-submitted');

        if (examTimer) {
            const initialRemainingSeconds = Number(examTimer.dataset.remainingSeconds || 0);
            const timerStartedAt = performance.now();
            let hasAutoSubmitted = false;
            let timerInterval = null;
            let autoSubmitTimeout = null;

            function updateExamTimer() {
                const elapsedSeconds = (performance.now() - timerStartedAt) / 1000;
                const remainingSeconds = Math.max(0, Math.ceil(initialRemainingSeconds - elapsedSeconds));

                if (remainingSeconds <= 0) {
                    examTimer.textContent = 'Time is over. Submitting...';
                    autoSubmitExam();
                    return;
                }

                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = String(remainingSeconds % 60).padStart(2, '0');
                examTimer.textContent = minutes + ':' + seconds + ' left';
            }

            function autoSubmitExam() {
                if (! examForm || hasAutoSubmitted) {
                    return;
                }

                hasAutoSubmitted = true;
                clearInterval(timerInterval);
                clearTimeout(autoSubmitTimeout);
                if (autoSubmittedInput) {
                    autoSubmittedInput.value = '1';
                }
                examForm.querySelector('button[type="submit"]')?.setAttribute('disabled', 'disabled');
                HTMLFormElement.prototype.submit.call(examForm);
            }

            updateExamTimer();
            timerInterval = setInterval(updateExamTimer, 1000);
            autoSubmitTimeout = setTimeout(autoSubmitExam, initialRemainingSeconds * 1000);
        }
    </script>
@endpush
