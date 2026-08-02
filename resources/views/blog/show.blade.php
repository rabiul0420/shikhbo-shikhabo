@extends('layouts.app', [
    'title' => $blog->metaTitle(),
    'description' => $blog->metaDescription(),
    'keywords' => __('site.blog.seo_keywords'),
    'canonical' => route('blog.show', $blog),
    'image' => $blog->hero_image ? asset($blog->hero_image) : null,
    'robots' => 'index, follow',
    'schemaExtra' => [
        [
            '@type' => 'BlogPosting',
            '@id' => route('blog.show', $blog) . '#article',
            'headline' => $blog->title(),
            'description' => $blog->metaDescription(),
            'datePublished' => optional($blog->published_at)->toAtomString(),
            'dateModified' => optional($blog->updated_at)->toAtomString(),
            'inLanguage' => app()->getLocale(),
            'mainEntityOfPage' => route('blog.show', $blog),
            'image' => $blog->hero_image ? asset($blog->hero_image) : asset('logo.svg'),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Shikhbo Shikhabo',
            ],
            'publisher' => ['@id' => url('/') . '#organization'],
            'isPartOf' => ['@id' => url('/') . '#website'],
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
                    'name' => __('site.nav.blog'),
                    'item' => route('blog.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $blog->title(),
                    'item' => route('blog.show', $blog),
                ],
            ],
        ],
    ],
])

@push('styles')
    <style>
        .blog-hero {
            margin: 0 0 22px;
            border-radius: 18px;
            overflow: hidden;
            aspect-ratio: 21 / 9;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
        }
        .blog-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .blog-article {
            max-width: 820px;
        }
        .blog-article .page-head h1 {
            font-size: clamp(1.7rem, 3vw, 2.4rem);
        }
        .blog-body {
            line-height: 1.75;
            font-size: 1.05rem;
        }
        .blog-body h2,
        .blog-body h3 {
            margin-top: 1.4em;
        }
        .blog-body img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
        }
        .blog-body p {
            margin: 0 0 1em;
        }
        .blog-related {
            margin-top: 36px;
        }
        .blog-related-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
        }
        .blog-related a {
            display: block;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid color-mix(in srgb, var(--line) 85%, transparent);
            text-decoration: none;
            color: inherit;
            background: color-mix(in srgb, var(--panel) 92%, white);
        }
        .blog-related a h3 {
            margin: 0 0 6px;
            font-size: 1rem;
        }
        @media (max-width: 800px) {
            .blog-hero { aspect-ratio: 16 / 9; }
            .blog-related-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <article class="blog-article">
        @if ($blog->hero_image)
            <div class="blog-hero">
                <img src="{{ asset($blog->hero_image) }}" alt="{{ $blog->title() }}">
            </div>
        @endif

        <div class="page-head">
            <div>
                <p class="muted">
                    <a href="{{ route('blog.index') }}">{{ __('site.nav.blog') }}</a>
                    · {{ $blog->published_at?->format('d M Y') }}
                </p>
                <h1>{{ $blog->title() }}</h1>
                @if ($blog->excerpt())
                    <p class="muted">{{ $blog->excerpt() }}</p>
                @endif
            </div>
        </div>

        <section class="panel content-panel blog-body">
            {!! $blog->body() !!}
        </section>
    </article>

    @if ($related->isNotEmpty())
        <section class="blog-related" aria-labelledby="related-blogs-title">
            <div class="section-head">
                <div>
                    <span class="eyebrow">{{ __('site.blog.related_eyebrow') }}</span>
                    <h2 id="related-blogs-title">{{ __('site.blog.related_title') }}</h2>
                </div>
            </div>
            <div class="blog-related-grid">
                @foreach ($related as $post)
                    <a href="{{ route('blog.show', $post) }}">
                        <h3>{{ $post->title() }}</h3>
                        <p class="muted">{{ $post->published_at?->format('d M Y') }}</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
@endsection
