@extends('layouts.app', ['title' => 'Available Exams'])

@push('styles')
    <style>
        .exam-hero {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            gap: 24px;
            align-items: center;
            margin-bottom: 24px;
            padding: 26px;
            border: 1px solid #dfe5ec;
            background:
                linear-gradient(135deg, rgba(14, 165, 233, .10), rgba(5, 150, 105, .10)),
                #ffffff;
        }

        .exam-hero h1 { font-size: 38px; }
        .exam-hero p { max-width: 660px; margin-bottom: 0; }
        .exam-hero-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: flex-end; }
        .exam-summary {
            display: grid;
            grid-template-columns: repeat(2, minmax(104px, 1fr));
            gap: 10px;
            min-width: 244px;
        }

        .exam-summary-item {
            padding: 12px;
            border: 1px solid #d6e2ea;
            background: rgba(255, 255, 255, .76);
        }

        .exam-summary-item strong {
            display: block;
            color: #1f2d3d;
            font-size: 25px;
            line-height: 1;
            margin-bottom: 5px;
        }

        .exam-summary-item span {
            color: #6c757d;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .class-exam-list { display: grid; gap: 22px; }
        .class-section {
            border: 1px solid #d8dee6;
            background: #ffffff;
        }

        .class-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-bottom: 1px solid #e5e9ef;
            background: #f8fafc;
        }

        .class-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .class-mark {
            display: inline-grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: #0f766e;
            color: #ffffff;
            font-weight: 900;
        }

        .class-section h2 { font-size: 24px; }
        .class-section .muted { margin-bottom: 0; }
        .class-count { background: #eef6ff; color: #1d4ed8; }
        .exam-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .exam-card {
            display: grid;
            gap: 14px;
            min-height: 210px;
            padding: 16px;
            border: 1px solid #dcdcdc;
            border-top: 4px solid #2563eb;
            background: #ffffff;
        }

        .exam-card-head { display: grid; gap: 8px; }
        .exam-card h3 { margin: 0; font-size: 20px; line-height: 1.22; }
        .exam-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .exam-meta-item {
            padding: 10px;
            border: 1px solid #e1e7ee;
            background: #fbfcfd;
        }

        .exam-meta-item span {
            display: block;
            color: #6c757d;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .exam-meta-item strong {
            color: #1f2d3d;
            font-size: 14px;
        }

        .exam-card-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: auto;
        }

        .exam-card-actions .button { min-width: 118px; }
        .empty-exam-panel {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            background: #ffffff;
        }

        @media (max-width: 760px) {
            .exam-hero { grid-template-columns: 1fr; padding: 18px; }
            .exam-hero h1 { font-size: 29px; }
            .exam-hero-actions { justify-content: flex-start; }
            .exam-summary { min-width: 0; width: 100%; }
            .class-section-head { align-items: flex-start; flex-direction: column; }
            .exam-meta { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $examsByClass = $exams->groupBy(fn ($exam) => $exam->academicClass->name ?? 'Unassigned Class');
        $subjectCount = $exams->pluck('subject.name')->filter()->unique()->count();
    @endphp

    <div class="exam-hero">
        <div>
            <h1>Available Exams</h1>
            <p class="muted">Choose your class, pick an exam, and submit your answers to see the result instantly.</p>
        </div>
        <div class="exam-hero-actions">
            @auth
                @if (auth()->user()->is_admin)
                    <a class="button secondary" href="{{ route('admin.exams.index') }}">Manage exams</a>
                @endif
            @else
                <a class="button secondary" href="{{ route('login') }}">Login to start</a>
            @endauth
            <div class="exam-summary" aria-label="Exam summary">
                <div class="exam-summary-item">
                    <strong>{{ $exams->count() }}</strong>
                    <span>Exams</span>
                </div>
                <div class="exam-summary-item">
                    <strong>{{ $examsByClass->count() }}</strong>
                    <span>Classes</span>
                </div>
                <div class="exam-summary-item">
                    <strong>{{ $subjectCount }}</strong>
                    <span>Subjects</span>
                </div>
                <div class="exam-summary-item">
                    <strong>{{ $exams->sum(fn ($exam) => $exam->questions->count()) }}</strong>
                    <span>Questions</span>
                </div>
            </div>
        </div>
    </div>

    @if ($exams->isEmpty())
        <section class="empty-exam-panel">
            <h2>No exams yet</h2>
            <p class="muted">An admin user can create an exam and add questions to it.</p>
            @auth
                @if (auth()->user()->is_admin)
                    <a class="button" href="{{ route('admin.index') }}">Open admin</a>
                @endif
            @endauth
        </section>
    @else
        <div class="class-exam-list">
            @foreach ($examsByClass as $className => $classExams)
                <section class="class-section" aria-labelledby="class-{{ \Illuminate\Support\Str::slug($className) }}">
                    <div class="class-section-head">
                        <div class="class-title">
                            <span class="class-mark">{{ \Illuminate\Support\Str::of($className)->substr(0, 1)->upper() }}</span>
                            <div>
                                <h2 id="class-{{ \Illuminate\Support\Str::slug($className) }}">{{ $className }}</h2>
                                <p class="muted">{{ $classExams->pluck('subject.name')->filter()->unique()->implode(', ') }}</p>
                            </div>
                        </div>
                        <span class="pill class-count">{{ $classExams->count() }} {{ \Illuminate\Support\Str::plural('exam', $classExams->count()) }}</span>
                    </div>

                    <div class="exam-card-grid">
                        @foreach ($classExams as $exam)
                            <article class="exam-card">
                                <div class="exam-card-head">
                                    <span class="eyebrow">{{ $exam->subject->name }}</span>
                                    <h3>{{ $exam->title }}</h3>
                                </div>

                                <div class="exam-meta">
                                    <div class="exam-meta-item">
                                        <span>Chapter</span>
                                <strong>{{ $exam->chapter->display_name }}</strong>
                                    </div>
                                    <div class="exam-meta-item">
                                        <span>Questions</span>
                                        <strong>{{ $exam->questions->count() }}</strong>
                                    </div>
                                </div>

                                <div class="exam-card-actions">
                                    <span class="pill">{{ $exam->academicClass->name }}</span>
                                    @auth
                                        <a class="button" href="{{ route('exams.show', $exam) }}">Start exam</a>
                                    @else
                                        <a class="button" href="{{ route('login') }}">Login to start</a>
                                    @endauth
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
