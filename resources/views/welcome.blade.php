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
        .status-section {
            display: grid;
            gap: 14px;
        }

        .status-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .status-section h2 { font-size: 26px; }

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
        .status-running { background: #ecfdf5; color: #047857; }
        .status-upcoming { background: #eff6ff; color: #1d4ed8; }
        .status-expired { background: #fef2f2; color: #b91c1c; }
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

        .offer-tooltip {
            position: relative;
            cursor: help;
            border-color: #f59e0b;
            background:
                linear-gradient(135deg, rgba(255, 247, 237, .96), rgba(236, 253, 245, .96));
            box-shadow: inset 4px 0 0 #f59e0b;
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .offer-tooltip:hover,
        .offer-tooltip:focus {
            border-color: #d97706;
            box-shadow:
                inset 4px 0 0 #f59e0b,
                0 12px 24px rgba(245, 158, 11, .18);
            transform: translateY(-1px);
        }

        .offer-tooltip > span {
            color: #9a3412;
        }

        .offer-tooltip > strong {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #0f172a;
        }

        .offer-tooltip > strong::before {
            content: "Gift";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 22px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #f59e0b;
            color: #ffffff;
            font-size: 11px;
            line-height: 1;
        }

        .offer-tooltip-popover {
            position: absolute;
            left: 0;
            bottom: calc(100% + 10px);
            z-index: 20;
            display: grid;
            gap: 8px;
            width: min(260px, 80vw);
            padding: 12px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            box-shadow: 0 16px 34px rgba(15, 23, 42, .18);
            opacity: 0;
            pointer-events: none;
            transform: translateY(6px);
            transition: opacity .16s ease, transform .16s ease;
        }

        .offer-tooltip:hover .offer-tooltip-popover,
        .offer-tooltip:focus .offer-tooltip-popover,
        .offer-tooltip:focus-within .offer-tooltip-popover {
            opacity: 1;
            transform: translateY(0);
        }

        .offer-tooltip-popover::after {
            content: "";
            position: absolute;
            left: 18px;
            bottom: -7px;
            width: 12px;
            height: 12px;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #cbd5e1;
            background: #ffffff;
            transform: rotate(45deg);
        }

        .offer-tooltip-row {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 8px;
            align-items: start;
        }

        .offer-tooltip-row span {
            display: inline-flex;
            justify-content: center;
            min-height: 24px;
            padding: 3px 7px;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 900;
        }

        .offer-tooltip-row strong {
            color: #1f2d3d;
            font-size: 13px;
            line-height: 1.35;
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

        .gift-winners-section {
            display: grid;
            gap: 14px;
            margin-top: 24px;
            padding: 18px;
            border: 1px solid #f4c27a;
            background: linear-gradient(135deg, #fff7ed, #f0fdf4);
        }

        .gift-winners-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 12px;
        }

        .gift-winner-card {
            display: grid;
            gap: 8px;
            padding: 12px;
            border: 1px solid #f1d39b;
            background: rgba(255, 255, 255, .82);
        }

        .gift-winner-head {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 12px;
            align-items: center;
        }

        .gift-winner-photo {
            display: inline-grid;
            place-items: center;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            border: 1px solid #f1d39b;
            background: #eef6ff;
            color: #2f6da8;
            font-size: 21px;
            font-weight: 900;
            overflow: hidden;
        }

        .gift-winner-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gift-winner-card h3 {
            margin: 0;
            font-size: 18px;
        }

        .gift-winner-meta {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .gift-name {
            color: #9a3412;
            font-weight: 900;
        }

        .gift-school {
            margin-bottom: 0;
            font-size: 14px;
        }

        @media (max-width: 760px) {
            .exam-hero { grid-template-columns: 1fr; padding: 18px; }
            .exam-hero h1 { font-size: 29px; }
            .exam-hero-actions { justify-content: flex-start; }
            .exam-summary { min-width: 0; width: 100%; }
            .status-section-head { align-items: flex-start; flex-direction: column; }
            .class-section-head { align-items: flex-start; flex-direction: column; }
            .exam-meta { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $examGroups = collect(['running', 'upcoming', 'expired'])
            ->mapWithKeys(fn ($status) => [$status => $exams->filter(fn ($exam) => $exam->scheduleStatus() === $status)]);
        $examsByClass = $exams->groupBy(fn ($exam) => $exam->academicClass->name ?? 'Unassigned Class');
        $subjectCount = $exams->pluck('subject.name')->filter()->unique()->count();
        $statusLabels = [
            'running' => 'Running Exam',
            'upcoming' => 'Upcoming Exam',
            'expired' => 'Expired Exam',
        ];
    @endphp

    <div class="exam-hero">
        <div>
            <h1>Available Exams</h1>
            <p class="muted">
                @auth
                    @if (! auth()->user()->is_admin && auth()->user()->academicClass)
                        Exams for {{ auth()->user()->academicClass->name }} are shown here.
                    @else
                        Choose your class, pick an exam, and submit your answers to see the result instantly.
                    @endif
                @else
                    Choose your class, pick an exam, and submit your answers to see the result instantly.
                @endauth
            </p>
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
            <p class="muted">
                @auth
                    @if (! auth()->user()->is_admin)
                        No exam is available for your class yet.
                    @else
                        An admin user can create an exam and add questions to it.
                    @endif
                @else
                    An admin user can create an exam and add questions to it.
                @endauth
            </p>
            @auth
                @if (auth()->user()->is_admin)
                    <a class="button" href="{{ route('admin.index') }}">Open admin</a>
                @endif
            @endauth
        </section>
    @else
        <div class="class-exam-list">
            @foreach ($examGroups as $status => $statusExams)
                <section class="status-section" aria-labelledby="status-{{ $status }}">
                    <div class="status-section-head">
                        <div>
                            <span class="eyebrow">{{ $statusLabels[$status] }}</span>
                            <h2 id="status-{{ $status }}">{{ $statusLabels[$status] }}</h2>
                        </div>
                        <span class="pill status-{{ $status }}">{{ $statusExams->count() }} {{ \Illuminate\Support\Str::plural('exam', $statusExams->count()) }}</span>
                    </div>

                    @if ($statusExams->isEmpty())
                        <section class="empty-exam-panel">
                            <p class="muted">No {{ strtolower($statusLabels[$status]) }} available.</p>
                        </section>
                    @else
                        @foreach ($statusExams->groupBy(fn ($exam) => $exam->academicClass->name ?? 'Unassigned Class') as $className => $classExams)
                            <section class="class-section" aria-labelledby="{{ $status }}-class-{{ \Illuminate\Support\Str::slug($className) }}">
                                <div class="class-section-head">
                                    <div class="class-title">
                                        <span class="class-mark">{{ \Illuminate\Support\Str::of($className)->substr(0, 1)->upper() }}</span>
                                        <div>
                                            <h2 id="{{ $status }}-class-{{ \Illuminate\Support\Str::slug($className) }}">{{ $className }}</h2>
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
                                                <div class="exam-meta-item">
                                                    <span>Starts</span>
                                                    <strong>{{ $exam->starts_at ? $exam->starts_at->format('M d, Y') : 'Not scheduled' }}</strong>
                                                </div>
                                                <div class="exam-meta-item">
                                                    <span>Deadline</span>
                                                    <strong>{{ $exam->ends_at ? $exam->ends_at->format('M d, Y') : 'Not scheduled' }}</strong>
                                                </div>
                                                <div class="exam-meta-item">
                                                    <span>Duration</span>
                                                    <strong>{{ $exam->duration_minutes ? $exam->duration_minutes . ' mins' : '-' }}</strong>
                                                </div>
                                                @if ($exam->hasPrizes())
                                                    <div class="exam-meta-item offer-tooltip" tabindex="0">
                                                        <span>Offer</span>
                                                        <strong>Top 3 gifts</strong>
                                                        <div class="offer-tooltip-popover" role="tooltip">
                                                            @if ($exam->first_prize)
                                                                <div class="offer-tooltip-row">
                                                                    <span>1st</span>
                                                                    <strong>{{ $exam->first_prize }}</strong>
                                                                </div>
                                                            @endif
                                                            @if ($exam->second_prize)
                                                                <div class="offer-tooltip-row">
                                                                    <span>2nd</span>
                                                                    <strong>{{ $exam->second_prize }}</strong>
                                                                </div>
                                                            @endif
                                                            @if ($exam->third_prize)
                                                                <div class="offer-tooltip-row">
                                                                    <span>3rd</span>
                                                                    <strong>{{ $exam->third_prize }}</strong>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="exam-card-actions">
                                                <span class="pill status-{{ $status }}">{{ ucfirst($status) }}</span>
                                                @if ($status === 'running' && auth()->check())
                                                    <a class="button" href="{{ route('exams.show', $exam) }}">Start exam</a>
                                                @elseif ($status === 'upcoming')
                                                    <span class="pill status-upcoming">Not started</span>
                                                @else
                                                    <a class="button" href="{{ route('exams.show', $exam) }}">View questions</a>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            </section>
                        @endforeach
                    @endif
                </section>
            @endforeach
        </div>
    @endif

    @if ($givenGiftAwards->isNotEmpty())
        <section id="gift-winners" class="gift-winners-section" aria-labelledby="gift-winners-title">
            <div class="status-section-head">
                <div>
                    <span class="eyebrow">Gift Winners</span>
                    <h2 id="gift-winners-title">Gift Received Students</h2>
                </div>
                <span class="pill status-running">{{ $givenGiftAwards->count() }} students</span>
            </div>

            <div class="gift-winners-grid">
                @foreach ($givenGiftAwards as $award)
                    <article class="gift-winner-card">
                        <div class="gift-winner-meta">
                            <span class="pill">{{ $award->position }}{{ $award->position === 1 ? 'st' : ($award->position === 2 ? 'nd' : ($award->position === 3 ? 'rd' : 'th')) }}</span>
                            <span class="pill published">Given</span>
                        </div>
                        <div class="gift-winner-head">
                            <span class="gift-winner-photo">
                                @if ($award->attempt->user->profile_photo_path)
                                    <img
                                        src="{{ Storage::url($award->attempt->user->profile_photo_path) }}"
                                        alt="{{ $award->attempt->user->name }} profile picture"
                                    >
                                @else
                                    {{ \Illuminate\Support\Str::of($award->attempt->user->name)->substr(0, 1)->upper() }}
                                @endif
                            </span>
                            <div>
                                <h3>{{ $award->attempt->user->name }}</h3>
                                <p class="muted gift-school">{{ $award->attempt->user->school->title ?? 'School not added' }}</p>
                            </div>
                        </div>
                        <p class="muted">
                            {{ $award->attempt->user->academicClass->name ?? '-' }}
                            / {{ $award->attempt->exam->title }}
                        </p>
                        <p class="gift-name">{{ $award->gift_title }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif
@endsection
