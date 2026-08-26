@extends('layouts.app', [
    'title' => __('site.how_to.seo_title'),
    'description' => __('site.how_to.seo_description'),
    'keywords' => __('site.how_to.seo_keywords'),
    'canonical' => route('how-to-take-bd-model-test-online'),
    'robots' => 'index, follow',
    'schemaExtra' => [
        [
            '@type' => 'HowTo',
            '@id' => route('how-to-take-bd-model-test-online') . '#howto',
            'name' => __('site.how_to.title'),
            'description' => __('site.how_to.seo_description'),
            'url' => route('how-to-take-bd-model-test-online'),
            'inLanguage' => app()->getLocale(),
            'step' => [
                [
                    '@type' => 'HowToStep',
                    'position' => 1,
                    'name' => __('site.how_to.step1_title'),
                    'text' => __('site.how_to.step1_text'),
                    'url' => route('how-to-take-bd-model-test-online') . '#step-1',
                    'image' => asset('images/how-to/register.png'),
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 2,
                    'name' => __('site.how_to.step2_title'),
                    'text' => __('site.how_to.step2_text'),
                    'url' => route('how-to-take-bd-model-test-online') . '#step-2',
                    'image' => asset('images/how-to/login.png'),
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 3,
                    'name' => __('site.how_to.step3_title'),
                    'text' => __('site.how_to.step3_text'),
                    'url' => route('how-to-take-bd-model-test-online') . '#step-3',
                    'image' => asset('images/how-to/exam-list.png'),
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 4,
                    'name' => __('site.how_to.step4_title'),
                    'text' => __('site.how_to.step4_text'),
                    'url' => route('how-to-take-bd-model-test-online') . '#step-4',
                    'image' => asset('images/how-to/take-exam.png'),
                ],
                [
                    '@type' => 'HowToStep',
                    'position' => 5,
                    'name' => __('site.how_to.step5_title'),
                    'text' => __('site.how_to.step5_text'),
                    'url' => route('how-to-take-bd-model-test-online') . '#step-5',
                    'image' => asset('images/how-to/review-result.png'),
                ],
            ],
        ],
        [
            '@type' => 'FAQPage',
            '@id' => route('how-to-take-bd-model-test-online') . '#faq',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => __('site.how_to.faq_1_q'),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => __('site.how_to.faq_1_a'),
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => __('site.how_to.faq_2_q'),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => __('site.how_to.faq_2_a'),
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => __('site.how_to.faq_3_q'),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => __('site.how_to.faq_3_a'),
                    ],
                ],
                [
                    '@type' => 'Question',
                    'name' => __('site.how_to.faq_4_q'),
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => __('site.how_to.faq_4_a'),
                    ],
                ],
            ],
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
                    'name' => __('site.how_to.title'),
                    'item' => route('how-to-take-bd-model-test-online'),
                ],
            ],
        ],
    ],
])

@push('styles')
    <style>
        .howto-page { display: grid; gap: 22px; }
        .howto-hero {
            display: grid;
            gap: 10px;
            padding: 28px 28px 26px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background:
                radial-gradient(circle at 92% 8%, rgba(14, 165, 233, .16), transparent 34%),
                linear-gradient(180deg, #fff, #f8fbff);
            box-shadow: var(--shadow);
        }
        .howto-hero p { margin-bottom: 0; max-width: 720px; }
        .howto-hero-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 8px; }
        .howto-steps { display: grid; gap: 14px; list-style: none; margin: 0; padding: 0; }
        .howto-step {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            gap: 16px;
            align-items: start;
            padding: 20px 22px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, .04);
        }
        .howto-num {
            display: inline-grid;
            place-items: center;
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--primary-soft), var(--primary));
            color: #fff;
            font-weight: 800;
            font-size: 16px;
            box-shadow: 0 8px 16px rgba(37, 99, 235, .22);
        }
        .howto-step h2 { margin: 6px 0 8px; font-size: 20px; }
        .howto-step p { margin-bottom: 0; }
        .howto-shot {
            margin: 14px 0 0;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #f8fafc;
            box-shadow: 0 6px 16px rgba(15, 23, 42, .06);
        }
        .howto-shot img {
            display: block;
            width: 100%;
            height: auto;
            vertical-align: middle;
        }
        .howto-tips {
            display: grid;
            gap: 10px;
            padding: 20px 22px;
            border: 1px solid var(--line);
            border-radius: var(--radius);
            background: #fff;
        }
        .howto-tips ul { margin: 0; padding-left: 18px; display: grid; gap: 8px; }
        .howto-tips li { line-height: 1.6; }
        .howto-faq { display: grid; gap: 10px; }
        .howto-faq-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            overflow: hidden;
        }
        .howto-faq-item summary {
            cursor: pointer;
            list-style: none;
            padding: 14px 18px;
            font-weight: 700;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }
        .howto-faq-item summary::-webkit-details-marker { display: none; }
        .howto-faq-item summary::after { content: "+"; color: var(--primary); font-size: 20px; line-height: 1; }
        .howto-faq-item[open] summary::after { content: "–"; }
        .howto-faq-item p { margin: 0; padding: 0 18px 16px; color: var(--muted); }
        .howto-cta {
            display: grid;
            gap: 10px;
            padding: 22px;
            border: 1px solid #bfdbfe;
            border-radius: var(--radius);
            background: linear-gradient(135deg, #eff6ff, #ecfeff);
        }
        .howto-cta p { margin-bottom: 0; }
        .howto-cta-actions { display: flex; flex-wrap: wrap; gap: 10px; }

        @media (max-width: 640px) {
            .howto-hero, .howto-step, .howto-tips, .howto-cta { padding: 16px; }
            .howto-step { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <article class="howto-page">
        <header class="howto-hero">
            <span class="eyebrow">{{ __('site.how_to.eyebrow') }}</span>
            <h1>{{ __('site.how_to.title') }}</h1>
            <p class="muted">{{ __('site.how_to.intro') }}</p>
            <div class="howto-hero-actions">
                <a class="button" href="{{ route('exams.directory') }}">{{ __('site.how_to.browse_exams') }}</a>
                @guest
                    <a class="button secondary" href="{{ route('register') }}">{{ __('site.nav.register') }}</a>
                    <a class="button secondary" href="{{ route('login') }}">{{ __('site.nav.login') }}</a>
                @else
                    <a class="button secondary" href="{{ route('exam-attempts.index') }}">{{ __('site.nav.results') }}</a>
                @endguest
            </div>
        </header>

        <ol class="howto-steps">
            <li class="howto-step" id="step-1">
                <span class="howto-num" aria-hidden="true">1</span>
                <div>
                    <h2>{{ __('site.how_to.step1_title') }}</h2>
                    <p>{{ __('site.how_to.step1_text') }}</p>
                    <figure class="howto-shot">
                        <img src="{{ asset('images/how-to/register.png') }}" alt="{{ __('site.how_to.step1_alt') }}" width="980" height="720">
                    </figure>
                </div>
            </li>
            <li class="howto-step" id="step-2">
                <span class="howto-num" aria-hidden="true">2</span>
                <div>
                    <h2>{{ __('site.how_to.step2_title') }}</h2>
                    <p>{{ __('site.how_to.step2_text') }}</p>
                    <figure class="howto-shot">
                        <img src="{{ asset('images/how-to/login.png') }}" alt="{{ __('site.how_to.step2_alt') }}" width="980" height="560" loading="lazy">
                    </figure>
                </div>
            </li>
            <li class="howto-step" id="step-3">
                <span class="howto-num" aria-hidden="true">3</span>
                <div>
                    <h2>{{ __('site.how_to.step3_title') }}</h2>
                    <p>{{ __('site.how_to.step3_text') }}</p>
                    <figure class="howto-shot">
                        <img src="{{ asset('images/how-to/exam-list.png') }}" alt="{{ __('site.how_to.step3_alt') }}" width="1200" height="640" loading="lazy">
                    </figure>
                </div>
            </li>
            <li class="howto-step" id="step-4">
                <span class="howto-num" aria-hidden="true">4</span>
                <div>
                    <h2>{{ __('site.how_to.step4_title') }}</h2>
                    <p>{{ __('site.how_to.step4_text') }}</p>
                    <figure class="howto-shot">
                        <img src="{{ asset('images/how-to/take-exam.png') }}" alt="{{ __('site.how_to.step4_alt') }}" width="1100" height="720" loading="lazy">
                    </figure>
                </div>
            </li>
            <li class="howto-step" id="step-5">
                <span class="howto-num" aria-hidden="true">5</span>
                <div>
                    <h2>{{ __('site.how_to.step5_title') }}</h2>
                    <p>{{ __('site.how_to.step5_text') }}</p>
                    <figure class="howto-shot">
                        <img src="{{ asset('images/how-to/review-result.png') }}" alt="{{ __('site.how_to.step5_alt') }}" width="1100" height="720" loading="lazy">
                    </figure>
                </div>
            </li>
        </ol>

        <section class="howto-tips" aria-labelledby="howto-tips-title">
            <h2 id="howto-tips-title">{{ __('site.how_to.tips_title') }}</h2>
            <ul>
                <li>{{ __('site.how_to.tip_1') }}</li>
                <li>{{ __('site.how_to.tip_2') }}</li>
                <li>{{ __('site.how_to.tip_3') }}</li>
                <li>{{ __('site.how_to.tip_4') }}</li>
            </ul>
        </section>

        <section aria-labelledby="howto-faq-title">
            <h2 id="howto-faq-title" style="margin-bottom: 12px;">{{ __('site.how_to.faq_title') }}</h2>
            <div class="howto-faq">
                <details class="howto-faq-item" open>
                    <summary>{{ __('site.how_to.faq_1_q') }}</summary>
                    <p>{{ __('site.how_to.faq_1_a') }}</p>
                </details>
                <details class="howto-faq-item">
                    <summary>{{ __('site.how_to.faq_2_q') }}</summary>
                    <p>{{ __('site.how_to.faq_2_a') }}</p>
                </details>
                <details class="howto-faq-item">
                    <summary>{{ __('site.how_to.faq_3_q') }}</summary>
                    <p>{{ __('site.how_to.faq_3_a') }}</p>
                </details>
                <details class="howto-faq-item">
                    <summary>{{ __('site.how_to.faq_4_q') }}</summary>
                    <p>{{ __('site.how_to.faq_4_a') }}</p>
                </details>
            </div>
        </section>

        <section class="howto-cta">
            <h2>{{ __('site.how_to.cta_title') }}</h2>
            <p class="muted">{{ __('site.how_to.cta_text') }}</p>
            <div class="howto-cta-actions">
                <a class="button" href="{{ route('exams.directory') }}">{{ __('site.how_to.browse_exams') }}</a>
                @guest
                    <a class="button secondary" href="{{ route('register') }}">{{ __('site.nav.register') }}</a>
                @endguest
            </div>
        </section>
    </article>
@endsection
