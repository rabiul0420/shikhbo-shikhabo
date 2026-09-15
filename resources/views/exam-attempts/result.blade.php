@extends('layouts.app', ['title' => 'Exam Result'])

@push('styles')
    <style>
        .result-overview {
            display: grid;
            gap: 18px;
        }

        .result-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(120px, 1fr));
            gap: 12px;
        }

        .result-stat {
            padding: 14px;
            border: 1px solid #d6e2ea;
            background: #f8f9fb;
        }

        .result-stat strong {
            display: block;
            color: #007bff;
            font-size: 34px;
            line-height: 1;
            margin-bottom: 7px;
        }

        .result-stat span {
            color: #6c757d;
            font-size: 13px;
            font-weight: 700;
        }

        .result-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 760px) {
            .result-overview { grid-template-columns: 1fr; }
            .result-stats { grid-template-columns: 1fr; }
            .result-actions { justify-content: flex-start; }
        }
    </style>
@endpush

@section('content')
    @php
        $percentage = $attempt->total_marks > 0 ? round(($attempt->score / $attempt->total_marks) * 100) : 0;
    @endphp

    <div class="page-head">
        <div>
            <h1>Result: {{ $attempt->exam->title }}</h1>
            <p class="muted">Submitted {{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</p>
        </div>
        <div class="result-actions">
            <a class="button secondary" href="{{ route(auth()->user()->is_admin ? 'admin.exams.results' : 'exams.results', $attempt->exam) }}">All result</a>
            <a class="button secondary" href="{{ route('home') }}">Take another exam</a>
        </div>
    </div>

    <section class="panel">
        <div class="result-overview">
            <div class="result-stats">
                <div class="result-stat">
                    <strong>{{ $attempt->score }} / {{ $attempt->total_marks }}</strong>
                    <span>{{ $percentage }}% score</span>
                </div>
                <div class="result-stat">
                    <strong>{{ $attemptPosition ? '#' . $attemptPosition : '-' }}</strong>
                    <span>Position</span>
                </div>
                <div class="result-stat">
                    <strong>{{ $participantCount }}</strong>
                    <span>Total Participants</span>
                </div>
            </div>
        </div>
    </section>

    <section class="stack" style="margin-top:18px;">
        <h2>Review</h2>
        @foreach ($attempt->exam->questions as $question)
            @php
                $selectedOptionIds = $attempt->answers
                    ->where('question_id', $question->id)
                    ->pluck('question_option_id')
                    ->filter()
                    ->all();
                $questionCorrect = $attempt->answers
                    ->where('question_id', $question->id)
                    ->contains('is_correct', true);
            @endphp
            <article class="panel stack">
                <div class="between">
                    <h3>{{ $loop->iteration }}. {{ $question->question_text }}</h3>
                    <span class="pill {{ $questionCorrect ? 'published' : 'draft' }}">
                        {{ $questionCorrect ? 'Correct' : 'Incorrect' }}
                    </span>
                </div>
                <div class="stack">
                    @foreach ($question->options as $option)
                        <div class="option">
                            <span>{{ in_array($option->id, $selectedOptionIds, true) ? '[selected]' : '[ ]' }}</span>
                            <span>
                                {{ $option->option_text }}
                                @if ($option->is_correct)
                                    <strong> - correct answer</strong>
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </article>
        @endforeach
    </section>
@endsection
