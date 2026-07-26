@extends('layouts.app', [
    'title' => 'Online Exam Practice for Students',
    'description' => 'Practise class-wise, subject-wise, and chapter-wise online exams on Shikhbo Shikhabo. Students can prepare, participate, and check results easily.',
    'canonical' => route('home'),
])

@push('styles')
    <style>
        .exam-intro-bar {
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

        .exam-intro-copy {
            display: grid;
            gap: 4px;
            min-width: 0;
        }

        .exam-intro-copy h1 {
            margin: 0;
            color: #1f2d3d;
            font-size: 22px;
            line-height: 1.2;
        }

        .exam-intro-copy p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            line-height: 1.45;
        }

        .exam-intro-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 8px;
            flex-wrap: wrap;
            flex: 0 0 auto;
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
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
        .exam-list-controls {
            display: flex;
            justify-content: center;
            padding: 0 16px 16px;
        }

        .empty-exam-panel {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            background: #ffffff;
        }

        .gift-winners-section {
            position: relative;
            display: grid;
            gap: 18px;
            margin-bottom: 24px;
            padding: 20px;
            border: 1px solid #e4c475;
            background:
                linear-gradient(135deg, rgba(255, 248, 228, .96), rgba(238, 249, 245, .96)),
                #ffffff;
            box-shadow: 0 16px 36px rgba(31, 45, 61, .08);
            overflow: hidden;
        }

        .gift-winners-section::before {
            content: "";
            position: absolute;
            inset: 0 auto 0 0;
            width: 7px;
            background: linear-gradient(180deg, #f59e0b, #20a16b, #2f6da8);
        }

        .gift-winners-section .status-section-head {
            position: relative;
            padding-left: 10px;
        }

        .gift-winners-section .eyebrow {
            color: #a15c00;
        }

        .gift-winners-section h2 {
            color: #1f2d3d;
            font-size: 28px;
        }

        .gift-winner-count {
            border: 1px solid #b7ead4;
            background: #e9fbf2;
            color: #07533e;
            box-shadow: 0 7px 18px rgba(7, 83, 62, .08);
        }

        .gift-winners-slider {
            position: relative;
            overflow: hidden;
            padding: 0 48px;
        }

        .gift-winners-track {
            display: flex;
            transition: transform .45s ease;
            will-change: transform;
        }

        .gift-winner-card {
            position: relative;
            display: grid;
            grid-template-columns: 172px minmax(0, 1fr);
            grid-template-areas:
                "photo meta"
                "photo name"
                "photo school"
                "photo details"
                "photo gift";
            align-items: center;
            flex: 0 0 100%;
            gap: 9px 20px;
            min-height: 214px;
            padding: 22px;
            border: 1px solid rgba(202, 157, 70, .5);
            background:
                linear-gradient(90deg, rgba(255, 255, 255, .96), rgba(255, 255, 255, .86)),
                #ffffff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
            overflow: hidden;
        }

        .gift-winner-card::after {
            content: "Gift";
            position: absolute;
            right: 22px;
            bottom: 14px;
            color: rgba(47, 109, 168, .08);
            font-size: 56px;
            font-weight: 900;
            line-height: 1;
            pointer-events: none;
        }

        .gift-winner-head {
            display: contents;
        }

        .gift-winner-head > div {
            display: contents;
        }

        .gift-winner-photo {
            grid-area: photo;
            position: relative;
            display: inline-grid;
            place-items: center;
            width: 156px;
            height: 156px;
            border-radius: 50%;
            border: 5px solid #ffffff;
            background: linear-gradient(135deg, #e8f3ff, #fef3c7);
            color: #2f6da8;
            font-size: 48px;
            font-weight: 900;
            box-shadow: 0 0 0 1px #e4c475, 0 16px 28px rgba(31, 45, 61, .14);
            overflow: hidden;
        }

        .gift-winner-photo::after {
            content: "";
            position: absolute;
            inset: 8px;
            border-radius: inherit;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.55);
            pointer-events: none;
        }

        .gift-winner-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .gift-winner-card h3 {
            grid-area: name;
            position: relative;
            margin: 0;
            color: #1f2d3d;
            font-size: 26px;
            line-height: 1.2;
            z-index: 1;
        }

        .gift-winner-meta {
            grid-area: meta;
            position: relative;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
            z-index: 1;
        }

        .gift-position-pill {
            background: #fff4d6;
            border: 1px solid #f3c961;
            color: #8a4b00;
        }

        .gift-given-pill {
            border: 1px solid #b7ead4;
        }

        .gift-name {
            grid-area: gift;
            position: relative;
            width: fit-content;
            max-width: 100%;
            padding: 11px 14px;
            border: 1px solid #f3c961;
            background: #fff8e6;
            color: #8a4b00;
            font-size: 18px;
            font-weight: 900;
            margin-bottom: 0;
            overflow-wrap: anywhere;
            z-index: 1;
        }

        .gift-school {
            grid-area: school;
            position: relative;
            margin-bottom: 0;
            font-size: 14px;
            z-index: 1;
        }

        .gift-winner-details {
            grid-area: details;
            position: relative;
            margin-bottom: 0;
            z-index: 1;
        }

        .gift-slider-control {
            position: absolute;
            top: 50%;
            z-index: 2;
            width: 38px;
            height: 50px;
            min-height: 50px;
            padding: 0;
            border: 1px solid #dfc37e;
            background: #ffffff;
            color: #8a4b00;
            font-size: 28px;
            line-height: 1;
            box-shadow: 0 9px 22px rgba(31,45,61,.1);
            transform: translateY(-50%);
        }

        .gift-slider-control:hover {
            background: #fff8e6;
            color: #663700;
        }

        .gift-slider-prev { left: 0; }
        .gift-slider-next { right: 0; }

        .gift-slider-dots {
            display: flex;
            justify-content: center;
            gap: 6px;
            margin-top: 12px;
        }

        .gift-slider-dot {
            width: 8px;
            height: 8px;
            border: 0;
            border-radius: 999px;
            background: #d7c7a6;
            cursor: pointer;
            transition: width .18s ease, background .18s ease;
        }

        .gift-slider-dot.is-active {
            width: 20px;
            background: #20a16b;
        }

        @media (max-width: 1020px) {
            .exam-card-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 760px) {
            .exam-intro-bar {
                align-items: flex-start;
                flex-direction: column;
                padding: 13px 14px;
            }
            .exam-intro-copy h1 { font-size: 20px; }
            .exam-intro-actions { justify-content: flex-start; width: 100%; }
            .status-section-head { align-items: flex-start; flex-direction: column; }
            .class-section-head { align-items: flex-start; flex-direction: column; }
            .exam-card-grid { grid-template-columns: 1fr; }
            .exam-meta { grid-template-columns: 1fr; }
            .gift-winners-section { padding: 16px; }
            .gift-winners-section h2 { font-size: 24px; }
            .gift-winners-slider { padding: 0 34px; }
            .gift-winner-card {
                grid-template-columns: 1fr;
                grid-template-areas:
                    "meta"
                    "photo"
                    "name"
                    "school"
                    "details"
                    "gift";
                justify-items: center;
                text-align: center;
                min-height: 354px;
                padding: 18px 14px;
            }
            .gift-winner-photo {
                width: 126px;
                height: 126px;
            }
            .gift-winner-meta { justify-content: center; }
            .gift-name { width: 100%; }
            .gift-slider-control {
                width: 30px;
                height: 44px;
                min-height: 44px;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $examGroups = collect(['running', 'upcoming', 'expired'])
            ->mapWithKeys(fn ($status) => [$status => $exams->filter(fn ($exam) => $exam->scheduleStatus() === $status)]);
        $statusLabels = [
            'running' => 'Running Exam',
            'upcoming' => 'Upcoming Exam',
            'expired' => 'Expired Exam',
        ];
    @endphp

    @if ($givenGiftAwards->isNotEmpty())
        <section id="gift-winners" class="gift-winners-section" aria-labelledby="gift-winners-title">
            <div class="status-section-head">
                <div>
                    <span class="eyebrow">Gift Winners</span>
                    <h2 id="gift-winners-title">Gift Received Students</h2>
                </div>
                <span class="pill gift-winner-count">{{ $givenGiftAwards->count() }} students</span>
            </div>

            <div class="gift-winners-slider" data-gift-slider>
                <div class="gift-winners-track" data-gift-track>
                    @foreach ($givenGiftAwards as $award)
                        <article class="gift-winner-card">
                            <div class="gift-winner-meta">
                                <span class="pill gift-position-pill">{{ $award->position }}{{ $award->position === 1 ? 'st' : ($award->position === 2 ? 'nd' : ($award->position === 3 ? 'rd' : 'th')) }}</span>
                                <span class="pill published gift-given-pill">Given</span>
                            </div>
                            <div class="gift-winner-head">
                                <span class="gift-winner-photo">
                                    @if ($award->attempt->user->profile_photo_path)
                                        <img
                                            src="{{ asset($award->attempt->user->profile_photo_path) }}"
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
                            <p class="muted gift-winner-details">
                                {{ $award->attempt->user->academicClass->name ?? '-' }}
                                / {{ $award->attempt->exam->title }}
                            </p>
                            <p class="gift-name">{{ $award->gift_title }}</p>
                        </article>
                    @endforeach
                </div>

                @if ($givenGiftAwards->count() > 1)
                    <button class="gift-slider-control gift-slider-prev" type="button" data-gift-prev aria-label="Previous gift winner">&lsaquo;</button>
                    <button class="gift-slider-control gift-slider-next" type="button" data-gift-next aria-label="Next gift winner">&rsaquo;</button>

                    <div class="gift-slider-dots" aria-label="Gift winner slideshow controls">
                        @foreach ($givenGiftAwards as $award)
                            <button
                                class="gift-slider-dot {{ $loop->first ? 'is-active' : '' }}"
                                type="button"
                                data-gift-slide="{{ $loop->index }}"
                                aria-label="Show gift winner {{ $loop->iteration }}"
                            ></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    <section class="exam-intro-bar" aria-labelledby="exam-intro-title">
        <div class="exam-intro-copy">
            <h1 id="exam-intro-title">Exams</h1>
            <p>
                @auth
                    @if (! auth()->user()->is_admin && auth()->user()->academicClass)
                        Showing exams for {{ auth()->user()->academicClass->name }}.
                    @else
                        Running, upcoming, and expired exams in one place.
                    @endif
                @else
                    Pick an exam and login when you are ready to start.
                @endauth
            </p>
        </div>
        <div class="exam-intro-actions">
            @auth
                @if (auth()->user()->is_admin)
                    <a class="button secondary" href="{{ route('admin.exams.index') }}">Manage exams</a>
                @else
                    <a class="button" href="{{ route('custom-exams.create') }}">Customize Exam</a>
                    <a class="button secondary" href="{{ route('custom-exams.results') }}">Custom Results</a>
                @endif
            @else
                <a class="button" href="{{ route('login', ['redirect_to' => route('custom-exams.create', [], false)]) }}">Customize Exam</a>
                <a class="button secondary" href="{{ route('login') }}">Login</a>
            @endauth
        </div>
    </section>

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
                                    @foreach ($classExams->take(3) as $exam)
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
                                @if ($classExams->count() > 3 && $classExams->first()->academicClass)
                                    <div class="exam-list-controls">
                                        <a class="button secondary" href="{{ route('classes.exams.index', $classExams->first()->academicClass) }}">
                                            See more
                                        </a>
                                    </div>
                                @endif
                            </section>
                        @endforeach
                    @endif
                </section>
            @endforeach
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-gift-slider]').forEach((slider) => {
            const track = slider.querySelector('[data-gift-track]');
            const cards = Array.from(track.querySelectorAll('.gift-winner-card'));
            const dots = Array.from(slider.querySelectorAll('[data-gift-slide]'));
            const prev = slider.querySelector('[data-gift-prev]');
            const next = slider.querySelector('[data-gift-next]');

            if (cards.length < 2) {
                return;
            }

            let activeIndex = 0;

            const showSlide = (index) => {
                activeIndex = (index + cards.length) % cards.length;
                track.style.transform = `translateX(-${activeIndex * 100}%)`;
                dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === activeIndex));
            };

            dots.forEach((dot) => {
                dot.addEventListener('click', () => showSlide(Number(dot.dataset.giftSlide)));
            });
            prev.addEventListener('click', () => showSlide(activeIndex - 1));
            next.addEventListener('click', () => showSlide(activeIndex + 1));

            window.setInterval(() => showSlide(activeIndex + 1), 3500);
        });
    </script>
@endpush
