@extends('layouts.app', ['title' => 'Custom Exam Result'])

@section('content')
    @php
        $percentage = $attempt->total_marks > 0 ? round(($attempt->score / $attempt->total_marks) * 100) : 0;
    @endphp

    <div class="page-head">
        <div>
            <h1>Custom Result: {{ $attempt->customExam->title }}</h1>
            <p class="muted">Submitted {{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</p>
        </div>
        <div class="row">
            <a class="button secondary" href="{{ route(auth()->user()->is_admin ? 'admin.custom-results.index' : 'custom-exams.results') }}">Custom Results</a>
            <a class="button secondary" href="{{ route('custom-exams.create') }}">Create another</a>
        </div>
    </div>

    <section class="panel">
        <p class="score">{{ $attempt->score }} / {{ $attempt->total_marks }}</p>
        <p class="muted">{{ $percentage }}% score</p>
    </section>

    <section class="stack" style="margin-top:18px;">
        <h2>Review</h2>
        @foreach ($attempt->customExam->questions as $question)
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
