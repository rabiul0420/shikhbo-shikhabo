@extends('layouts.app', [
    'title' => $academicClass->name . ' Exams',
    'description' => 'All exams for ' . $academicClass->name . ' on Bd ModelTest.',
    'canonical' => route('classes.exams.index', $academicClass),
])

@push('styles')
    <style>
        .class-exam-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            padding: 14px 16px;
            border: 1px solid #dce5ee;
            border-left: 4px solid #2563eb;
            background: #ffffff;
        }

        .class-exam-head-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .class-exam-head h1 {
            margin: 0;
            color: #1f2d3d;
            font-size: 24px;
            line-height: 1.2;
        }

        .class-exam-head p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            line-height: 1.45;
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
        .class-count { background: #eef6ff; color: #1d4ed8; }
        .status-running { background: #ecfdf5; color: #047857; }
        .status-upcoming { background: #eff6ff; color: #1d4ed8; }
        .status-expired { background: #fef2f2; color: #b91c1c; }
        .exam-card-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
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
            background: linear-gradient(135deg, rgba(255, 247, 237, .96), rgba(236, 253, 245, .96));
            box-shadow: inset 4px 0 0 #f59e0b;
        }

        .offer-tooltip > span { color: #9a3412; }
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

        @media (max-width: 1020px) {
            .exam-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 760px) {
            .class-exam-head {
                align-items: flex-start;
                flex-direction: column;
            }
            .status-section-head { align-items: flex-start; flex-direction: column; }
            .exam-card-grid { grid-template-columns: 1fr; }
            .exam-meta { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $examGroups = collect(['running', 'upcoming'])
            ->mapWithKeys(fn ($status) => [$status => $exams->filter(fn ($exam) => $exam->scheduleStatus() === $status)]);
        $statusLabels = [
            'running' => __('site.home.running'),
            'upcoming' => __('site.home.upcoming'),
        ];
    @endphp

    <section class="class-exam-head" aria-labelledby="class-exam-title">
        <div class="class-exam-head-copy">
            <h1 id="class-exam-title">{{ $academicClass->name }} Exams</h1>
            <p>All available and upcoming model tests for this class.</p>
        </div>
        <a class="button secondary" href="{{ route('home') }}">Back home</a>
    </section>

    @if ($exams->isEmpty())
        <section class="empty-exam-panel">
            <h2>No exams yet</h2>
            <p class="muted">No exam is available for {{ $academicClass->name }} yet.</p>
        </section>
    @else
        <div class="class-exam-list">
            @foreach ($examGroups as $status => $statusExams)
                @continue($statusExams->isEmpty())
                <section class="status-section" aria-labelledby="status-{{ $status }}">
                    <div class="status-section-head">
                        <div>
                            <span class="eyebrow">{{ $statusLabels[$status] }}</span>
                            <h2 id="status-{{ $status }}">{{ $statusLabels[$status] }}</h2>
                        </div>
                        <span class="pill status-{{ $status }}">{{ $statusExams->count() }} {{ \Illuminate\Support\Str::plural('exam', $statusExams->count()) }}</span>
                    </div>

                    <div class="exam-card-grid">
                        @foreach ($statusExams as $exam)
                            @php
                                $existingAttempt = auth()->check() && ! auth()->user()->is_admin
                                    ? $exam->attempts->first()
                                    : null;
                            @endphp
                            @include('exams.partials.card', [
                                'exam' => $exam,
                                'status' => $status,
                                'existingAttempt' => $existingAttempt,
                            ])
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
