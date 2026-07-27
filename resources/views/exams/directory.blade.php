@extends('layouts.app', [
    'title' => __('site.directory.seo_title'),
    'description' => __('site.directory.seo_description'),
    'keywords' => __('site.directory.seo_keywords'),
    'canonical' => route('exams.directory'),
    'robots' => 'index, follow',
    'schemaExtra' => [
        [
            '@type' => 'CollectionPage',
            '@id' => route('exams.directory') . '#collection',
            'name' => __('site.directory.title'),
            'description' => __('site.directory.seo_description'),
            'url' => route('exams.directory'),
            'isPartOf' => ['@id' => url('/') . '#website'],
            'about' => [
                '@type' => 'Thing',
                'name' => 'BD Model Test',
            ],
            'inLanguage' => app()->getLocale(),
            'numberOfItems' => $classes->count(),
        ],
        [
            '@type' => 'ItemList',
            '@id' => route('exams.directory') . '#class-list',
            'name' => __('site.directory.title'),
            'itemListElement' => $classes->values()->map(fn ($class, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $class->name,
                'url' => route('classes.exams', $class->slug),
            ])->all(),
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
            ],
        ],
    ],
])

@push('styles')
    <style>
        .directory-hero {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 28px;
            padding: 22px 24px;
            border: 1px solid rgba(14, 165, 233, .18);
            border-radius: 22px;
            background:
                radial-gradient(circle at 10% 20%, rgba(14, 165, 233, .16), transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(5, 150, 105, .14), transparent 42%),
                linear-gradient(135deg, #e0f2fe 0%, #ecfdf5 100%);
        }

        .directory-hero h1 {
            margin: 0 0 6px;
            font-size: clamp(24px, 4vw, 34px);
        }

        .directory-hero p { margin: 0; max-width: 46ch; }

        .directory-summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(88px, 1fr));
            gap: 10px;
            min-width: min(100%, 320px);
        }

        .directory-summary-item {
            padding: 12px 14px;
            border: 1px solid rgba(255, 255, 255, .7);
            border-radius: 16px;
            background: rgba(255, 255, 255, .88);
        }

        .directory-summary-item strong {
            display: block;
            font-family: var(--font-display);
            font-size: 24px;
            line-height: 1;
            margin-bottom: 5px;
        }

        .directory-summary-item span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .class-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .class-card {
            display: grid;
            gap: 16px;
            padding: 18px;
            border: 1px solid var(--line);
            border-radius: 22px;
            border-top: 4px solid #2563eb;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .class-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 16px 30px rgba(37, 99, 235, .12);
        }

        .class-card-head {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .class-card-mark {
            display: inline-grid;
            place-items: center;
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #0ea5e9, #059669);
            color: #fff;
            font-family: var(--font-display);
            font-size: 20px;
            font-weight: 800;
        }

        .class-card h2 {
            margin: 0;
            font-size: 20px;
        }

        .class-card-meta {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .class-card-meta div {
            padding: 12px;
            border: 1px solid #e1e7ee;
            border-radius: 14px;
            background: #f8fafc;
        }

        .class-card-meta span {
            display: block;
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .class-card-meta strong {
            font-size: 22px;
            font-family: var(--font-display);
            line-height: 1;
        }

        .class-card-actions {
            display: flex;
            justify-content: flex-end;
        }

        .class-card-actions .button {
            background: linear-gradient(135deg, #0ea5e9, #059669);
            box-shadow: 0 10px 22px rgba(5, 150, 105, .2);
        }

        .empty-directory {
            display: grid;
            gap: 10px;
            justify-items: start;
            padding: 28px;
            border: 1px dashed #b8c4d1;
            border-radius: 18px;
            background: #fff;
        }

        .seo-content {
            margin-top: 36px;
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #fff;
            display: grid;
            gap: 16px;
            width: 100%;
        }

        .seo-content h2 {
            font-size: 22px;
        }

        .seo-content h3 {
            margin: 8px 0 0;
            font-size: 17px;
        }

        .seo-content p,
        .seo-content li {
            color: var(--muted);
            margin: 0;
            line-height: 1.7;
        }

        .seo-content ol {
            margin: 0;
            padding-left: 20px;
            display: grid;
            gap: 8px;
        }

        @media (max-width: 760px) {
            .directory-hero,
            .class-card-actions { align-items: flex-start; flex-direction: column; }
            .directory-summary { width: 100%; }
        }
    </style>
@endpush

@section('content')
    <div class="directory-hero">
        <div>
            <span class="eyebrow">{{ __('site.directory.eyebrow') }}</span>
            <h1>{{ __('site.directory.title') }}</h1>
            <p class="muted">{{ __('site.directory.text') }}</p>
        </div>
        <div class="directory-summary" aria-label="{{ __('site.directory.summary') }}">
            <div class="directory-summary-item">
                <strong>{{ $summary['classes'] }}</strong>
                <span>{{ __('site.directory.stat_classes') }}</span>
            </div>
            <div class="directory-summary-item">
                <strong>{{ $summary['subjects'] }}</strong>
                <span>{{ __('site.directory.stat_subjects') }}</span>
            </div>
            <div class="directory-summary-item">
                <strong>{{ $summary['exams'] }}</strong>
                <span>{{ __('site.directory.stat_exams') }}</span>
            </div>
        </div>
    </div>

    @if ($classes->isEmpty())
        <section class="empty-directory">
            <h2>{{ __('site.directory.empty_title') }}</h2>
            <p class="muted">{{ __('site.directory.empty_text') }}</p>
            <a class="button" href="{{ route('home') }}">{{ __('site.exam.back_home') }}</a>
        </section>
    @else
        <div class="class-grid">
            @foreach ($classes as $class)
                <article class="class-card">
                    <div class="class-card-head">
                        <span class="class-card-mark">{{ \Illuminate\Support\Str::of($class->name)->substr(0, 1)->upper() }}</span>
                        <div>
                            <h2>{{ $class->name }}</h2>
                            <p class="muted" style="margin: 0;">{{ __('site.directory.card_hint') }}</p>
                        </div>
                    </div>
                    <div class="class-card-meta">
                        <div>
                            <span>{{ __('site.directory.stat_subjects') }}</span>
                            <strong>{{ $class->subjects_count }}</strong>
                        </div>
                        <div>
                            <span>{{ __('site.directory.stat_exams') }}</span>
                            <strong>{{ $class->exams_count }}</strong>
                        </div>
                    </div>
                    <div class="class-card-actions">
                        <a class="button" href="{{ route('classes.exams', $class->slug) }}">
                            {{ __('site.directory.view_details') }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif

    <section class="seo-content" aria-labelledby="directory-seo-title">
        <h2 id="directory-seo-title">{{ __('site.directory.seo_section_title') }}</h2>
        <p>{{ __('site.directory.seo_section_p1') }}</p>
        <p>{{ __('site.directory.seo_section_p2') }}</p>
        <h3>{{ __('site.directory.seo_how_title') }}</h3>
        <ol>
            <li>{{ __('site.directory.seo_how_1') }}</li>
            <li>{{ __('site.directory.seo_how_2') }}</li>
            <li>{{ __('site.directory.seo_how_3') }}</li>
        </ol>
        <h3>{{ __('site.directory.seo_audience_title') }}</h3>
        <p>{{ __('site.directory.seo_audience_text') }}</p>
    </section>
@endsection
