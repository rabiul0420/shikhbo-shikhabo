@extends('layouts.app', ['title' => 'All Result'])

@push('styles')
    <style>
        .results-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
            padding: 18px;
            border: 1px solid #dcdcdc;
            background: #ffffff;
        }

        .results-head p { margin-bottom: 0; }
        .result-total {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            min-width: 460px;
        }

        .result-total-item {
            display: grid;
            justify-items: end;
            gap: 5px;
            padding: 12px;
            border: 1px solid #d6e2ea;
            background: #f8f9fb;
            min-width: 140px;
        }

        .result-total-item strong {
            color: #2563eb;
            font-size: 32px;
            line-height: 1;
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
            .results-head { grid-template-columns: 1fr; }
            .result-total { justify-content: flex-start; min-width: 0; }
            .result-total-item { justify-items: start; }
        }
    </style>
@endpush

@section('content')
    <div class="results-head">
        <div>
            <span class="eyebrow">All Result</span>
            <h1>{{ $exam->title }}</h1>
            <p class="muted">
                {{ $exam->academicClass->name }} / {{ $exam->subject->name }} / {{ $exam->chapter->display_name }}
            </p>
        </div>
        <div class="result-total">
            <div class="result-total-item">
                <strong>{{ $attempts->count() }}</strong>
                <span class="muted">Participants</span>
            </div>
            <div class="result-total-item">
                <strong>{{ $highestAttempt ? $highestAttempt->score . ' / ' . $highestAttempt->total_marks : '0 / 0' }}</strong>
                <span class="muted">Highest Mark</span>
            </div>
            <div class="result-total-item">
                <strong>{{ $myAttempt ? '#' . $attemptPositions[$myAttempt->id] : '-' }}</strong>
                <span class="muted">My Position</span>
            </div>
        </div>
    </div>

    @if ($attempts->isEmpty())
        <section class="empty-results">
            <h2>No results yet</h2>
            <p class="muted">No one has submitted this exam yet.</p>
            <a class="button secondary" href="{{ route('exam-attempts.index') }}">Back to my results</a>
        </section>
    @else
        <section class="panel">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Student</th>
                            <th>Score</th>
                            <th>Gift</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attempts as $attempt)
                            <tr>
                                <td>{{ $attemptPositions[$attempt->id] }}</td>
                                <td>{{ $attempt->user->name }}</td>
                                <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
                                <td>{{ $exam->prizeForPosition($attemptPositions[$attempt->id]) ?: '-' }}</td>
                                <td>{{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</td>
                                <td>
                                    @if (auth()->user()->is_admin || (int) auth()->id() === (int) $attempt->user_id)
                                        <a class="button secondary small" href="{{ route('exam-attempts.result', $attempt) }}">View details</a>
                                    @else
                                        <span class="muted">Details private</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
