@extends('layouts.app', ['title' => 'My Result'])

@push('styles')
    <style>
        .result-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
            padding: 22px;
            border: 1px solid #dfe5ec;
            background:
                linear-gradient(135deg, rgba(37, 99, 235, .08), rgba(5, 150, 105, .10)),
                #ffffff;
        }

        .result-hero p { margin-bottom: 0; }
        .result-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(96px, 1fr));
            gap: 10px;
            min-width: 330px;
        }

        .result-summary-item {
            padding: 12px;
            border: 1px solid #d6e2ea;
            background: rgba(255, 255, 255, .78);
        }

        .result-summary-item strong {
            display: block;
            color: #1f2d3d;
            font-size: 26px;
            line-height: 1;
            margin-bottom: 5px;
        }

        .result-summary-item span {
            color: #6c757d;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .result-list { display: grid; gap: 14px; }
        .result-card {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            padding: 16px;
            border: 1px solid #dcdcdc;
            border-left: 4px solid #2563eb;
            background: #ffffff;
        }

        .result-card h2 { font-size: 21px; margin-bottom: 8px; }
        .result-meta {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .result-score {
            display: grid;
            gap: 8px;
            justify-items: end;
            min-width: 180px;
            text-align: right;
        }

        .result-score strong {
            color: #2563eb;
            font-size: 30px;
            line-height: 1;
        }

        .progress-track {
            width: 160px;
            height: 8px;
            overflow: hidden;
            background: #e5e7eb;
        }

        .progress-fill {
            height: 100%;
            background: #059669;
        }

        .result-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
        }

        .empty-results {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            background: #ffffff;
        }

        @media (max-width: 760px) {
            .result-hero { grid-template-columns: 1fr; padding: 18px; }
            .result-summary { min-width: 0; width: 100%; grid-template-columns: 1fr; }
            .result-card { grid-template-columns: 1fr; }
            .result-score { justify-items: start; text-align: left; min-width: 0; }
            .result-actions { justify-content: flex-start; }
            .progress-track { width: 100%; }
        }
    </style>
@endpush

@section('content')
    @php
        $completedAttempts = $attempts->where('total_marks', '>', 0);
        $averageScore = $completedAttempts->isNotEmpty()
            ? round($completedAttempts->avg(fn ($attempt) => ($attempt->score / $attempt->total_marks) * 100))
            : 0;
        $bestScore = $completedAttempts->isNotEmpty()
            ? round($completedAttempts->max(fn ($attempt) => ($attempt->score / $attempt->total_marks) * 100))
            : 0;
    @endphp

    <div class="result-hero">
        <div>
            <h1>My Result</h1>
            <p class="muted">See your submitted exam results, scores, subjects, chapters, and detailed review links.</p>
        </div>
        <div class="result-summary" aria-label="Result summary">
            <div class="result-summary-item">
                <strong>{{ $attempts->count() }}</strong>
                <span>Attempts</span>
            </div>
            <div class="result-summary-item">
                <strong>{{ $averageScore }}%</strong>
                <span>Average</span>
            </div>
            <div class="result-summary-item">
                <strong>{{ $bestScore }}%</strong>
                <span>Best</span>
            </div>
        </div>
    </div>

    @if ($attempts->isEmpty())
        <section class="empty-results">
            <h2>No results yet</h2>
            <p class="muted">You have not submitted any exams yet. Start an exam to see your result here.</p>
            <a class="button" href="{{ route('home') }}">Browse exams</a>
        </section>
    @else
        <section class="result-list" aria-label="My exam results">
            @foreach ($attempts as $attempt)
                @php
                    $percentage = $attempt->total_marks > 0 ? round(($attempt->score / $attempt->total_marks) * 100) : 0;
                @endphp
                <article class="result-card">
                    <div>
                        <span class="eyebrow">{{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</span>
                        <h2>{{ $attempt->exam->title }}</h2>
                        <div class="result-meta">
                            <span class="pill">{{ $attempt->exam->academicClass->name }}</span>
                            <span class="pill">{{ $attempt->exam->subject->name }}</span>
                                <span class="pill">{{ $attempt->exam->chapter->display_name }}</span>
                            <span class="pill {{ $attempt->status === 'graded' ? 'published' : 'draft' }}">{{ ucfirst($attempt->status) }}</span>
                        </div>
                    </div>
                    <div class="result-score">
                        <strong>{{ $attempt->score }} / {{ $attempt->total_marks }}</strong>
                        <span class="muted">{{ $percentage }}% score</span>
                        <div class="progress-track" aria-hidden="true">
                            <div class="progress-fill" style="width: {{ $percentage }}%;"></div>
                        </div>
                        <div class="result-actions">
                            <a class="button secondary small" href="{{ route('exam-attempts.result', $attempt) }}">View details</a>
                            <a class="button secondary small" href="{{ route('exams.results', $attempt->exam) }}">All result</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
