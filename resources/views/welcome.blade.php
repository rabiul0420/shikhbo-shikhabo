@extends('layouts.app', ['title' => 'Available Exams'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Available Exams</h1>
            <p class="muted">Choose an exam and submit your answers to see the result instantly.</p>
        </div>
        @auth
            @if (auth()->user()->is_admin)
                <a class="button secondary" href="{{ route('admin.exams.index') }}">Manage exams</a>
            @endif
        @else
            <a class="button secondary" href="{{ route('login') }}">Login to start</a>
        @endauth
    </div>

    @if ($exams->isEmpty())
        <section class="panel">
            <h2>No exams yet</h2>
            <p class="muted">An admin user can create an exam and add questions to it.</p>
            @auth
                @if (auth()->user()->is_admin)
                    <a class="button" href="{{ route('admin.index') }}">Open admin</a>
                @endif
            @endauth
        </section>
    @else
        <div class="grid grid-2">
            @foreach ($exams as $exam)
                <article class="panel stack">
                    <div class="between">
                        <h2>{{ $exam->title }}</h2>
                    </div>
                    <div class="row">
                        <span class="pill">{{ $exam->academicClass->name }}</span>
                        <span class="pill">{{ $exam->subject->name }}</span>
                        <span class="pill">{{ $exam->chapter->name }}</span>
                        <span class="pill">{{ $exam->questions->count() }} questions</span>
                    </div>
                    @auth
                        <a class="button" href="{{ route('exams.show', $exam) }}">Start exam</a>
                    @else
                        <a class="button" href="{{ route('login') }}">Login to start</a>
                    @endauth
                </article>
            @endforeach
        </div>
    @endif
@endsection
