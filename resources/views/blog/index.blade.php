@extends('layouts.app', [
    'title' => __('site.blog.seo_index_title'),
    'description' => __('site.blog.seo_index_description'),
    'keywords' => __('site.blog.seo_keywords'),
    'canonical' => route('blog.index'),
    'robots' => 'index, follow',
])

@push('styles')
    <style>
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        .blog-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 0;
            overflow: hidden;
            border: 1px solid color-mix(in srgb, var(--line) 85%, transparent);
            border-radius: 18px;
            background: color-mix(in srgb, var(--panel) 92%, white);
            text-decoration: none;
            color: inherit;
            transition: transform .18s ease, box-shadow .18s ease;
        }
        .blog-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
        }
        .blog-card-media {
            aspect-ratio: 16 / 9;
            background: linear-gradient(135deg, #dbeafe, #eff6ff);
            overflow: hidden;
        }
        .blog-card-media img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .blog-card-body {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 0 16px 16px;
        }
        .blog-card-body h2 {
            margin: 0;
            font-size: 1.15rem;
            line-height: 1.35;
        }
        .blog-card-body p {
            margin: 0;
            color: var(--muted);
            font-size: 0.95rem;
        }
        .blog-card-meta {
            font-size: 0.85rem;
            color: var(--muted);
        }
        .blog-pagination {
            margin-top: 28px;
            display: flex;
            justify-content: center;
        }
        @media (max-width: 900px) {
            .blog-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .blog-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ __('site.blog.index_title') }}</h1>
            <p class="muted">{{ __('site.blog.index_text') }}</p>
        </div>
    </div>

    @if ($blogs->isEmpty())
        <section class="panel">
            <h2>{{ __('site.blog.empty_title') }}</h2>
            <p class="muted">{{ __('site.blog.empty_text') }}</p>
        </section>
    @else
        <div class="blog-grid">
            @foreach ($blogs as $post)
                <a class="blog-card" href="{{ route('blog.show', $post) }}">
                    <div class="blog-card-media">
                        @if ($post->hero_image)
                            <img src="{{ asset($post->hero_image) }}" alt="{{ $post->title() }}">
                        @endif
                    </div>
                    <div class="blog-card-body">
                        <span class="blog-card-meta">{{ $post->published_at?->format('d M Y') }}</span>
                        <h2>{{ $post->title() }}</h2>
                        @if ($post->excerpt())
                            <p>{{ \Illuminate\Support\Str::limit(strip_tags($post->excerpt()), 140) }}</p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="blog-pagination">
            {{ $blogs->links() }}
        </div>
    @endif
@endsection
