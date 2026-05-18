@extends('layouts.app', ['title' => $exam->title])

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $exam->title }}</h1>
            <div class="row">
                <span class="pill">{{ $exam->academicClass->name }}</span>
                <span class="pill">{{ $exam->subject->name }}</span>
                <span class="pill">{{ $exam->chapter->name }}</span>
                <span class="pill">{{ $exam->questions->count() }} questions</span>
                <span class="pill">{{ $exam->questions->sum('marks') }} marks</span>
            </div>
        </div>
        <a class="button secondary" href="{{ route('home') }}">Back</a>
    </div>

    @if ($exam->questions->isEmpty())
        <section class="panel">
            <h2>This exam has no questions yet</h2>
        </section>
    @else
        <form class="stack" method="POST" action="{{ route('exams.submit', $exam) }}">
            @csrf
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
    @endif
@endsection
