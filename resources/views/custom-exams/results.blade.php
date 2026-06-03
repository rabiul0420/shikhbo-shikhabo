@extends('layouts.app', ['title' => 'Custom Results'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Custom Results</h1>
            <p class="muted">Results from exams you generated yourself.</p>
        </div>
        <a class="button" href="{{ route('custom-exams.create') }}">Create custom exam</a>
    </div>

    @if ($attempts->isEmpty())
        <section class="panel stack">
            <h2>No custom results yet</h2>
            <p class="muted">Create a custom exam and submit answers to see your result here.</p>
        </section>
    @else
        <section class="panel">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Exam</th>
                            <th>Subject</th>
                            <th>Chapter</th>
                            <th>Score</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attempts as $attempt)
                            <tr>
                                <td>{{ $attempt->customExam->title }}</td>
                                <td>{{ $attempt->customExam->subject->name }}</td>
                                <td>{{ $attempt->customExam->chapter->display_name }}</td>
                                <td>{{ $attempt->score }} / {{ $attempt->total_marks }}</td>
                                <td>{{ optional($attempt->submitted_at)->format('M d, Y h:i A') }}</td>
                                <td>
                                    <a class="button secondary small" href="{{ route('custom-exam-attempts.result', $attempt) }}">View details</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
@endsection
