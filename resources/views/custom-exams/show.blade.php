@extends('layouts.app', ['title' => $customExam->title])

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $customExam->title }}</h1>
            <div class="row">
                <span class="pill">{{ $customExam->academicClass->name }}</span>
                <span class="pill">{{ $customExam->subject->name }}</span>
                <span class="pill">{{ $customExam->chapter->display_name }}</span>
                <span class="pill">{{ $customExam->question_count }} questions</span>
                <span class="pill">{{ $customExam->total_marks }} marks</span>
            </div>
        </div>
        <a class="button secondary" href="{{ route('custom-exams.create') }}">Back</a>
    </div>

    @if ($existingAttempt)
        <section class="panel stack">
            <h2>You already submitted this custom exam</h2>
            <p class="muted">Open the result page to review your answers.</p>
            <a class="button" href="{{ route('custom-exam-attempts.result', $existingAttempt) }}">View result</a>
        </section>
    @else
        <form class="stack" method="POST" action="{{ route('custom-exams.submit', $customExam) }}">
            @csrf
            @foreach ($customExam->questions as $question)
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
    @endif
@endsection
