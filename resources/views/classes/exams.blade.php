@extends('layouts.app', [
    'title' => __('site.exam.all_exams_title', ['class' => $academicClass->name]),
    'description' => __('site.exam.all_exams_seo_description', ['class' => $academicClass->name]),
    'keywords' => __('site.exam.all_exams_seo_keywords', ['class' => $academicClass->name]),
    'canonical' => route('classes.exams', $academicClass->slug),
    'robots' => 'index, follow',
    'schemaExtra' => [
        [
            '@type' => 'CollectionPage',
            '@id' => route('classes.exams', $academicClass->slug) . '#collection',
            'name' => __('site.exam.all_exams_title', ['class' => $academicClass->name]),
            'description' => __('site.exam.all_exams_seo_description', ['class' => $academicClass->name]),
            'url' => route('classes.exams', $academicClass->slug),
            'isPartOf' => ['@id' => url('/') . '#website'],
            'about' => [
                '@type' => 'Thing',
                'name' => $academicClass->name . ' BD Model Test',
            ],
            'inLanguage' => app()->getLocale(),
            'numberOfItems' => $exams->count(),
        ],
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => __('site.nav.home'),
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => __('site.nav.exams'),
                    'item' => route('exams.directory'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $academicClass->name,
                    'item' => route('classes.exams', $academicClass->slug),
                ],
            ],
        ],
    ],
])

@push('styles')
    <style>
        .class-exams-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 24px;
            padding: 22px 24px;
            border: 1px solid rgba(14, 165, 233, .18);
            border-radius: 22px;
            background:
                radial-gradient(circle at 10% 20%, rgba(14, 165, 233, .16), transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(5, 150, 105, .14), transparent 42%),
                linear-gradient(135deg, #e0f2fe 0%, #ecfdf5 100%);
        }

        .class-exams-hero h1 {
            margin: 0 0 6px;
            font-size: clamp(24px, 4vw, 32px);
        }

        .class-exams-hero p { margin: 0; }

        .class-exam-list { display: grid; gap: 28px; }
        .status-section { display: grid; gap: 14px; }
        .status-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }
        .status-section h2 { font-size: 24px; }

        .class-section {
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .04);
        }

        .class-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 18px;
            border-bottom: 1px solid #e8eef5;
            background: linear-gradient(90deg, rgba(14, 165, 233, .08), rgba(5, 150, 105, .06));
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
            border-radius: 12px;
            background: linear-gradient(135deg, #0ea5e9, #059669);
            color: #ffffff;
            font-weight: 900;
        }

        .class-section h2 { font-size: 22px; }
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
            border: 1px solid var(--line);
            border-radius: 18px;
            border-top: 4px solid #2563eb;
            background: #ffffff;
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .exam-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, .1);
        }

        .exam-card-head { display: grid; gap: 8px; }
        .exam-card h3 { margin: 0; font-size: 19px; line-height: 1.22; }
        .exam-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px;
        }

        .exam-meta-item {
            padding: 10px;
            border: 1px solid #e1e7ee;
            border-radius: 12px;
            background: #f8fafc;
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
            content: "{{ __('site.exam.gift') }}";
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
            border-radius: 14px;
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

        .exam-card-actions .button,
        .exam-card-actions .pill {
            min-height: 26px;
            padding: 4px 9px;
            font-size: 12px;
            font-weight: 800;
            line-height: 1.2;
            box-shadow: none;
        }

        .exam-card-actions .button {
            min-width: 0;
            transform: none;
        }

        .exam-card-actions .button:hover {
            transform: none;
        }

        .empty-exam-panel {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            border-radius: 18px;
            background: #ffffff;
        }

        .empty-exam-panel {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            border-radius: 18px;
            background: #ffffff;
        }

        .seo-content {
            margin-top: 36px;
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            display: grid;
            gap: 14px;
            width: 100%;
        }

        .seo-content h2 { font-size: 22px; }
        .seo-content p {
            margin: 0;
            color: var(--muted);
            line-height: 1.7;
        }

        @media (max-width: 760px) {
            .status-section-head,
            .class-section-head,
            .class-exams-hero { align-items: flex-start; flex-direction: column; }
            .exam-meta { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    @php
        $statusLabels = [
            'running' => __('site.home.running'),
            'upcoming' => __('site.home.upcoming'),
        ];
        $examGroups = collect(['running', 'upcoming'])
            ->mapWithKeys(fn ($status) => [
                $status => $exams->filter(fn ($exam) => $exam->scheduleStatus() === $status),
            ]);
    @endphp

    <div class="class-exams-hero">
        <div>
            <span class="eyebrow">{{ $academicClass->name }}</span>
            <h1>{{ __('site.exam.all_exams_title', ['class' => $academicClass->name]) }}</h1>
            <p class="muted">{{ __('site.exam.all_exams_text', ['class' => $academicClass->name]) }}</p>
        </div>
        <div class="row">
            <a class="button secondary" href="{{ route('exams.directory') }}">{{ __('site.exam.back_directory') }}</a>
            <a class="button secondary" href="{{ route('home') }}">{{ __('site.exam.back_home') }}</a>
        </div>
    </div>

    @if ($exams->isEmpty())
        <section class="empty-exam-panel">
            <p class="muted">{{ __('site.exam.no_class_exams') }}</p>
            <a class="button" href="{{ route('exams.directory') }}">{{ __('site.exam.back_directory') }}</a>
        </section>
    @else
        <div class="class-exam-list">
            @foreach ($examGroups as $status => $statusExams)
                @continue($statusExams->isEmpty())
                <section class="status-section" aria-labelledby="class-status-{{ $status }}">
                    <div class="status-section-head">
                        <div>
                            <span class="eyebrow">{{ $statusLabels[$status] }}</span>
                            <h2 id="class-status-{{ $status }}">{{ $statusLabels[$status] }}</h2>
                        </div>
                        <span class="pill status-{{ $status }}">{{ __('site.home.exam_count', ['count' => $statusExams->count()]) }}</span>
                    </div>

                    <div class="class-section">
                        <div class="exam-card-grid">
                            @foreach ($statusExams as $exam)
                                @include('partials.exam-card', ['exam' => $exam, 'status' => $status])
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif

    <section class="seo-content" aria-labelledby="class-seo-title">
        <h2 id="class-seo-title">{{ __('site.exam.class_seo_title', ['class' => $academicClass->name]) }}</h2>
        <p>{{ __('site.exam.class_seo_p1', ['class' => $academicClass->name]) }}</p>
        <p>{{ __('site.exam.class_seo_p2') }}</p>
    </section>
@endsection
