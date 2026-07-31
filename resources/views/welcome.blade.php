@extends('layouts.app', [
    'title' => __('site.home.seo_title'),
    'description' => __('site.home.seo_description'),
    'keywords' => __('site.home.seo_keywords'),
    'canonical' => route('home'),
    'robots' => 'index, follow',
])

@push('styles')
    <style>

        .home-hero {
            position: relative;
            overflow: hidden;
            min-height: 0;
            display: grid;
            align-items: center;
            color: #fff;
            background:
                radial-gradient(circle at 18% 20%, rgba(14, 165, 233, .45), transparent 36%),
                radial-gradient(circle at 82% 18%, rgba(52, 211, 153, .35), transparent 34%),
                linear-gradient(145deg, #0b1f3a 0%, #0f3d6e 42%, #065f46 100%);
        }

        .home-hero::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .18), rgba(15, 23, 42, .55)),
                url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            pointer-events: none;
        }

        .home-hero-orb {
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            filter: blur(10px);
            opacity: .35;
            animation: floatOrb 10s ease-in-out infinite;
        }

        .home-hero-orb.one {
            top: -120px;
            right: -80px;
            background: radial-gradient(circle, #0ea5e9, transparent 70%);
        }

        .home-hero-orb.two {
            bottom: -160px;
            left: -100px;
            background: radial-gradient(circle, #34d399, transparent 70%);
            animation-delay: -4s;
        }

        @keyframes floatOrb {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-18px) scale(1.05); }
        }

        .home-hero-inner {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) minmax(240px, .85fr);
            gap: 24px;
            align-items: center;
            padding: 36px 0 32px;
        }

        .home-hero-copy {
            display: grid;
            gap: 12px;
            animation: riseIn .7s ease both;
        }

        .home-brand-mark {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            width: fit-content;
        }

        .home-brand-mark img {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, .28);
        }

        .home-brand-mark strong {
            font-family: var(--font-display);
            font-size: clamp(22px, 3.2vw, 30px);
            line-height: 1.15;
            letter-spacing: -.02em;
        }

        .home-hero h1 {
            max-width: 18ch;
            font-size: clamp(28px, 4.4vw, 42px);
            line-height: 1.25;
            color: #fff;
        }

        .home-hero p {
            max-width: 42ch;
            margin: 0;
            color: rgba(255, 255, 255, .86);
            font-size: 16px;
            line-height: 1.65;
        }

        .home-hero-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .home-hero-actions .button {
            min-height: 44px;
            padding-inline: 18px;
        }

        .home-hero-actions .button.ghost {
            background: transparent;
            border-color: rgba(255, 255, 255, .35);
            color: #fff;
            box-shadow: none;
        }

        .home-hero-actions .button.ghost:hover {
            background: rgba(255, 255, 255, .1);
            border-color: #fff;
            color: #fff;
        }

        .home-hero-visual {
            position: relative;
            min-height: 240px;
            animation: riseIn .9s ease both .12s;
        }

        .hero-stage {
            position: relative;
            display: grid;
            place-items: center;
            min-height: 240px;
        }

        .hero-stage svg {
            width: min(100%, 380px);
            height: auto;
            filter: drop-shadow(0 24px 40px rgba(0, 0, 0, .28));
            animation: gentleBob 6s ease-in-out infinite;
        }

        @keyframes gentleBob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes riseIn {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .home-section {
            padding: 52px 0;
        }

        .home-section-alt {
            background:
                linear-gradient(180deg, rgba(14, 165, 233, .05), rgba(5, 150, 105, .05)),
                #ffffff;
        }

        .section-intro {
            display: grid;
            gap: 10px;
            max-width: 640px;
            margin-bottom: 34px;
        }

        .section-intro h2 {
            font-size: clamp(26px, 4vw, 36px);
        }

        .section-intro p {
            margin: 0;
            color: var(--muted);
            font-size: 16px;
        }

        .why-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 18px;
        }

        .why-item {
            display: grid;
            gap: 12px;
            padding: 8px 4px;
            animation: riseIn .6s ease both;
        }

        .why-item:nth-child(2) { animation-delay: .08s; }
        .why-item:nth-child(3) { animation-delay: .16s; }
        .why-item:nth-child(4) { animation-delay: .24s; }

        .why-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(14, 165, 233, .16), rgba(5, 150, 105, .16));
            color: var(--primary);
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 18px;
        }

        .why-item h3 {
            margin: 0;
            font-family: var(--font-display);
            font-size: 18px;
        }

        .why-item p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .exam-toolbar {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin-bottom: 28px;
            flex-wrap: wrap;
            padding: 22px 24px;
            border: 1px solid rgba(14, 165, 233, .18);
            border-radius: 22px;
            background:
                radial-gradient(circle at 10% 20%, rgba(14, 165, 233, .16), transparent 45%),
                radial-gradient(circle at 90% 80%, rgba(5, 150, 105, .14), transparent 42%),
                linear-gradient(135deg, #e0f2fe 0%, #ecfdf5 100%);
        }

        .exam-summary {
            display: grid;
            grid-template-columns: repeat(4, minmax(88px, 1fr));
            gap: 10px;
            min-width: min(100%, 420px);
        }

        .exam-summary-item {
            padding: 12px 14px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: rgba(255, 255, 255, .9);
        }

        .exam-summary-item strong {
            display: block;
            color: var(--text);
            font-family: var(--font-display);
            font-size: 24px;
            line-height: 1;
            margin-bottom: 5px;
        }

        .exam-summary-item span {
            color: var(--muted);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .04em;

        }

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
            background:
                linear-gradient(90deg, rgba(14, 165, 233, .08), rgba(5, 150, 105, .06));
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            padding: 16px;
        }

        .class-section-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button.class-view-all {
            min-height: 36px;
            padding: 8px 16px;
            border: 0;
            border-radius: 999px;
            background: linear-gradient(135deg, #0ea5e9, #059669);
            color: #fff;
            box-shadow: 0 10px 22px rgba(5, 150, 105, .22);
            white-space: nowrap;
        }

        .button.class-view-all:hover {
            background: linear-gradient(135deg, #0284c7, #047857);
            color: #fff;
            transform: translateY(-1px);
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
            transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease;
        }

        .offer-tooltip:hover,
        .offer-tooltip:focus {
            border-color: #d97706;
            box-shadow: inset 4px 0 0 #f59e0b, 0 12px 24px rgba(245, 158, 11, .18);
            transform: translateY(-1px);
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

        .site-carousel {
            position: relative;
            display: grid;
            gap: 16px;
        }

        .site-carousel-track {
            display: flex;
            gap: 16px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            scroll-behavior: smooth;
            scrollbar-width: none;
            -ms-overflow-style: none;
            padding: 4px 2px 8px;
        }

        .site-carousel-track::-webkit-scrollbar { display: none; }

        .site-carousel-slide {
            flex: 0 0 100%;
            scroll-snap-align: start;
            min-width: 0;
        }

        .site-carousel-controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .site-carousel-btn {
            width: 42px;
            height: 42px;
            min-height: 42px;
            padding: 0;
            border: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, #0ea5e9, #059669);
            color: #fff;
            box-shadow: 0 8px 18px rgba(37, 99, 235, .22);
            font-size: 18px;
            line-height: 1;
        }

        .site-carousel-btn:hover {
            background: linear-gradient(135deg, #0284c7, #047857);
            transform: translateY(-1px);
            color: #fff;
        }

        .site-carousel-btn:disabled {
            opacity: .45;
            cursor: default;
            transform: none;
            box-shadow: none;
        }

        .site-carousel-dots {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .site-carousel-dot {
            width: 9px;
            height: 9px;
            padding: 0;
            min-height: 0;
            border: 0;
            border-radius: 999px;
            background: #cbd5e1;
            box-shadow: none;
        }

        .site-carousel-dot.is-active {
            width: 22px;
            background: linear-gradient(135deg, #0ea5e9, #059669);
        }

        .testimonial {
            display: grid;
            gap: 18px;
            height: 100%;
            padding: 22px;
            border: 1px solid var(--line);
            border-radius: 20px;
            background: #fff;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .05);
        }

        .testimonial blockquote {
            margin: 0;
            font-size: 16px;
            line-height: 1.7;
            color: var(--text);
        }

        .testimonial-author {
            display: grid;
            gap: 2px;
            margin-top: auto;
        }

        .testimonial-author strong {
            font-family: var(--font-display);
            font-size: 15px;
        }

        .testimonial-author span {
            color: var(--muted);
            font-size: 13px;
        }

        .faq-list {
            display: grid;
            gap: 10px;
            width: min(820px, 100%);
            margin-inline: auto;
        }

        #faq .section-intro {
            margin-inline: auto;
            text-align: center;
        }

        .faq-item {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: #fff;
            overflow: hidden;
        }

        .faq-item summary {
            cursor: pointer;
            list-style: none;
            padding: 16px 18px;
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .faq-item summary::-webkit-details-marker { display: none; }

        .faq-item summary::after {
            content: "+";
            color: var(--primary);
            font-size: 22px;
            line-height: 1;
            font-weight: 500;
        }

        .faq-item[open] summary::after { content: "–"; }

        .faq-item p {
            margin: 0;
            padding: 0 18px 18px;
            color: var(--muted);
        }

        .gift-winners-section {
            position: relative;
            display: grid;

            gap: 14px;
            margin-top: 8px;
            padding: 22px;
            border: 1px solid #f4c27a;
            border-radius: 22px;
            background: linear-gradient(135deg, #fff7ed, #f0fdf4);
        }


        .gift-winner-card {
            position: relative;
            display: grid;

            gap: 8px;
            height: 100%;
            padding: 16px;
            border: 1px solid #f1d39b;
            border-radius: 16px;
            background: rgba(255, 255, 255, .92);

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

            border: 1px solid #f1d39b;
            background: #eef6ff;
            color: #2563eb;
            font-size: 21px;

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

        @media (min-width: 760px) {
            .site-carousel-slide {
                flex-basis: calc((100% - 32px) / 3);
            }
        }

        @media (max-width: 980px) {
            .home-hero-inner { grid-template-columns: 1fr; padding: 28px 0 24px; }
            .home-hero-visual { min-height: 200px; order: -1; }
            .hero-stage { min-height: 200px; }
            .hero-stage svg { width: min(100%, 300px); }
            .why-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 760px) {

            .home-hero { min-height: auto; }
            .home-section { padding: 40px 0; }
            .why-grid, .exam-summary { grid-template-columns: 1fr; }
            .status-section-head,
            .class-section-head,
            .exam-toolbar,
            .class-section-actions { align-items: flex-start; flex-direction: column; }
            .exam-meta { grid-template-columns: 1fr; }
            .home-brand-mark strong { font-size: 22px; }
            .home-hero h1 { max-width: none; font-size: 26px; }
            .site-carousel-slide { flex-basis: 85%; }

        }
    </style>
@endpush

@section('content')
    @php
        $examGroups = collect(['running', 'upcoming', 'expired'])
            ->mapWithKeys(fn ($status) => [$status => $exams->filter(fn ($exam) => $exam->scheduleStatus() === $status)]);
        $statusLabels = [
            'running' => __('site.home.running'),
            'upcoming' => __('site.home.upcoming'),
            'expired' => __('site.home.expired'),
        ];
        $featuredStatuses = ['running', 'upcoming'];
        $whyNumbers = app()->getLocale() === 'bn'
            ? ['০১', '০২', '০৩', '০৪']
            : ['01', '02', '03', '04'];
    @endphp


    <section class="home-hero" aria-labelledby="home-hero-title">
        <div class="home-hero-orb one" aria-hidden="true"></div>
        <div class="home-hero-orb two" aria-hidden="true"></div>
        <div class="shell home-hero-inner">
            <div class="home-hero-copy">
                <div class="home-brand-mark">
                    <img src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
                    <strong>Shikhbo Shikhabo</strong>
                </div>
                <h1 id="home-hero-title">{{ __('site.home.hero_title') }}</h1>
                <p>{{ __('site.home.hero_text') }}</p>
                <div class="home-hero-actions">
                    <a class="button" href="{{ route('exams.directory') }}">{{ __('site.home.browse_exams') }}</a>
                    @auth
                        @if (auth()->user()->is_admin)
                            <a class="button ghost" href="{{ route('admin.exams.index') }}">{{ __('site.home.manage_exams') }}</a>
                        @else
                            <a class="button ghost" href="{{ route('custom-exams.create') }}">{{ __('site.home.custom_exam') }}</a>
                        @endif
                    @else
                        <a class="button ghost" href="{{ route('register') }}">{{ __('site.home.create_account') }}</a>
                    @endauth
                </div>
            </div>
            <div class="home-hero-visual" aria-hidden="true">
                <div class="hero-stage">
                    <svg viewBox="0 0 480 360" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="54" y="48" width="372" height="264" rx="28" fill="url(#heroPanel)"/>
                        <path d="M96 98c0-10 7.8-17 17.6-15.4 20.6 3.2 36.2 10.8 50.8 22.8v102c-14-10.4-30.4-17.2-49-20.2-10.8-1.6-19.4 6.2-19.4 17V98Z" fill="url(#heroBlue)"/>
                        <path d="M232 105.4v102c14-10.4 30.4-17.2 49-20.2 10.8-1.6 19.4 6.2 19.4 17V98c0-10-7.8-17-17.6-15.4-20.6 3.2-36.2 10.8-50.8 22.8Z" fill="url(#heroGreen)"/>
                        <path d="M164 106v102" stroke="#fff" stroke-width="6" stroke-linecap="round"/>
                        <circle cx="164" cy="188" r="24" fill="#111827"/>
                        <text x="164" y="195" text-anchor="middle" fill="#fff" font-family="Sora, sans-serif" font-size="16" font-weight="800">SS</text>
                        <rect x="268" y="118" width="118" height="14" rx="7" fill="#fff" fill-opacity=".55"/>
                        <rect x="268" y="148" width="96" height="14" rx="7" fill="#fff" fill-opacity=".35"/>
                        <rect x="268" y="178" width="108" height="14" rx="7" fill="#fff" fill-opacity=".28"/>
                        <rect x="268" y="220" width="88" height="36" rx="18" fill="#fff"/>
                        <text x="312" y="243" text-anchor="middle" fill="#2563eb" font-family="Hind Siliguri, sans-serif" font-size="14" font-weight="700">{{ __('site.home.start') }}</text>
                        <defs>
                            <linearGradient id="heroPanel" x1="54" y1="48" x2="426" y2="312" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#0ea5e9"/>
                                <stop offset="1" stop-color="#059669"/>
                            </linearGradient>
                            <linearGradient id="heroBlue" x1="96" y1="82" x2="164" y2="220" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#7dd3fc"/>
                                <stop offset="1" stop-color="#2563eb"/>
                            </linearGradient>
                            <linearGradient id="heroGreen" x1="164" y1="90" x2="300" y2="220" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#6ee7b7"/>
                                <stop offset="1" stop-color="#059669"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
            </div>
        </div>
    </section>

    <section id="why-choose" class="home-section home-section-alt" aria-labelledby="why-choose-title">
        <div class="shell">
            <div class="section-intro">
                <span class="eyebrow">{{ __('site.home.why_eyebrow') }}</span>
                <h2 id="why-choose-title">{{ __('site.home.why_title') }}</h2>
                <p>{{ __('site.home.why_text') }}</p>
            </div>
            <div class="why-grid">
                <article class="why-item">
                    <div class="why-icon">{{ $whyNumbers[0] }}</div>
                    <h3>{{ __('site.home.why_1_title') }}</h3>
                    <p>{{ __('site.home.why_1_text') }}</p>
                </article>
                <article class="why-item">
                    <div class="why-icon">{{ $whyNumbers[1] }}</div>
                    <h3>{{ __('site.home.why_2_title') }}</h3>
                    <p>{{ __('site.home.why_2_text') }}</p>
                </article>
                <article class="why-item">
                    <div class="why-icon">{{ $whyNumbers[2] }}</div>
                    <h3>{{ __('site.home.why_3_title') }}</h3>
                    <p>{{ __('site.home.why_3_text') }}</p>
                </article>
                <article class="why-item">
                    <div class="why-icon">{{ $whyNumbers[3] }}</div>
                    <h3>{{ __('site.home.why_4_title') }}</h3>
                    <p>{{ __('site.home.why_4_text') }}</p>
                </article>
            </div>
        </div>
    </section>

    <section id="featured-exams" class="home-section" aria-labelledby="featured-exams-title">
        <div class="shell">
            <div class="exam-toolbar">
                <div class="section-intro" style="margin-bottom: 0;">
                    <span class="eyebrow">{{ __('site.home.featured_eyebrow') }}</span>
                    <h2 id="featured-exams-title">{{ __('site.home.featured_title') }}</h2>
                    <p>
                        @auth
                            @if (! auth()->user()->is_admin && auth()->user()->academicClass)
                                {{ __('site.home.featured_for_class', ['class' => auth()->user()->academicClass->name]) }}
                            @else
                                {{ __('site.home.featured_pick') }}
                            @endif
                        @else
                            {{ __('site.home.featured_pick') }}
                        @endauth
                    </p>
                </div>
                <div class="exam-summary" aria-label="{{ __('site.home.featured_eyebrow') }}">
                    <div class="exam-summary-item">
                        <strong>{{ $exams->count() }}</strong>
                        <span>{{ __('site.home.stat_exams') }}</span>
                    </div>
                    <div class="exam-summary-item">
                        <strong>{{ $examsByClass->count() }}</strong>
                        <span>{{ __('site.home.stat_classes') }}</span>
                    </div>
                    <div class="exam-summary-item">
                        <strong>{{ $subjectCount }}</strong>
                        <span>{{ __('site.home.stat_subjects') }}</span>
                    </div>
                    <div class="exam-summary-item">
                        <strong>{{ $exams->sum(fn ($exam) => $exam->questions->count()) }}</strong>
                        <span>{{ __('site.home.stat_questions') }}</span>
                    </div>
                </div>
            </div>

            @if ($exams->isEmpty())
                <section class="empty-exam-panel">
                    <h2>{{ __('site.home.no_exams_title') }}</h2>
                    <p class="muted">
                        @auth
                            @if (! auth()->user()->is_admin)
                                {{ __('site.home.no_exams_student') }}
                            @else
                                {{ __('site.home.no_exams_admin') }}
                            @endif
                        @else
                            {{ __('site.home.no_exams_admin') }}
                        @endauth
                    </p>
                    @auth
                        @if (auth()->user()->is_admin)
                            <a class="button" href="{{ route('admin.index') }}">{{ __('site.home.open_admin') }}</a>
                        @endif
                    @endauth
                </section>
            @else
                <div class="class-exam-list">
                    @foreach ($featuredStatuses as $status)
                        @php $statusExams = $examGroups[$status]; @endphp
                        <section class="status-section" aria-labelledby="status-{{ $status }}">
                            <div class="status-section-head">
                                <div>
                                    <span class="eyebrow">{{ $statusLabels[$status] }}</span>
                                    <h2 id="status-{{ $status }}">{{ $statusLabels[$status] }}</h2>
                                </div>
                                <span class="pill status-{{ $status }}">{{ __('site.home.exam_count', ['count' => $statusExams->count()]) }}</span>
                            </div>

                            @if ($statusExams->isEmpty())
                                <section class="empty-exam-panel">
                                    <p class="muted">{{ __('site.home.no_status_exams', ['status' => $statusLabels[$status]]) }}</p>
                                </section>
                            @else
                                @foreach ($statusExams->groupBy(fn ($exam) => $exam->academic_class_id ?? 0) as $classId => $classExams)
                                    @include('partials.exam-class-section', [
                                        'status' => $status,
                                        'classId' => $classId ?: null,
                                        'academicClass' => $classExams->first()->academicClass,
                                        'className' => $classExams->first()->academicClass->name ?? 'Unassigned Class',
                                        'classExams' => $classExams,
                                        'limit' => 3,
                                    ])
                                @endforeach
                            @endif
                        </section>
                    @endforeach

                    @php $expiredExams = $examGroups['expired']; @endphp
                    @if ($expiredExams->isNotEmpty())
                        <section class="status-section" aria-labelledby="status-expired">
                            <div class="status-section-head">
                                <div>
                                    <span class="eyebrow">{{ $statusLabels['expired'] }}</span>
                                    <h2 id="status-expired">{{ $statusLabels['expired'] }}</h2>
                                </div>
                                <span class="pill status-expired">{{ __('site.home.exam_count', ['count' => $expiredExams->count()]) }}</span>
                            </div>
                            @foreach ($expiredExams->groupBy(fn ($exam) => $exam->academic_class_id ?? 0) as $classId => $classExams)
                                @include('partials.exam-class-section', [
                                    'status' => 'expired',
                                    'classId' => $classId ?: null,
                                    'academicClass' => $classExams->first()->academicClass,
                                    'className' => $classExams->first()->academicClass->name ?? 'Unassigned Class',
                                    'classExams' => $classExams,
                                    'limit' => 3,
                                ])
                            @endforeach
                        </section>

                    @endif
                </div>
            @endif

            @if ($givenGiftAwards->isNotEmpty())
                <section id="gift-winners" class="gift-winners-section" aria-labelledby="gift-winners-title" style="margin-top: 36px;">
                    <div class="status-section-head">
                        <div>
                            <span class="eyebrow">{{ __('site.home.gift_eyebrow') }}</span>
                            <h2 id="gift-winners-title">{{ __('site.home.gift_title') }}</h2>
                        </div>
                        <span class="pill status-running">{{ __('site.home.gift_count', ['count' => $givenGiftAwards->count()]) }}</span>
                    </div>

                    <div class="site-carousel" data-carousel data-autoplay="5000">
                        <div class="site-carousel-track" data-carousel-track tabindex="0">
                            @foreach ($givenGiftAwards as $award)
                                <div class="site-carousel-slide">
                                    <article class="gift-winner-card">
                                        <div class="gift-winner-meta">
                                            <span class="pill">{{ $award->position }}{{ app()->getLocale() === 'bn' ? ($award->position === 1 ? 'ম' : ($award->position === 2 ? 'য়' : ($award->position === 3 ? 'য়' : 'তম'))) : ($award->position === 1 ? 'st' : ($award->position === 2 ? 'nd' : ($award->position === 3 ? 'rd' : 'th'))) }}</span>
                                            <span class="pill published">{{ __('site.home.gift_given') }}</span>
                                        </div>

                                        <div class="gift-winner-head">
                                            @php
                                                $winner = $award->attempt->user;
                                                $winnerInitial = \Illuminate\Support\Str::of($winner->name)->substr(0, 1)->upper();
                                                $winnerPhoto = $winner->profile_photo_path;
                                                $hasWinnerPhoto = $winnerPhoto && is_file(public_path($winnerPhoto));
                                            @endphp
                                            <span class="gift-winner-photo">
                                                @if ($hasWinnerPhoto)
                                                    <img
                                                        src="{{ asset($winnerPhoto) }}"
                                                        alt="{{ $winner->name }}"
                                                        onerror="this.replaceWith(document.createTextNode(@json((string) $winnerInitial)));"
                                                    >
                                                @else
                                                    {{ $winnerInitial }}
                                                @endif
                                            </span>
                                            <div>
                                                <h3>{{ $winner->name }}</h3>
                                                <p class="muted gift-school">{{ $winner->school->title ?? __('site.home.school_missing') }}</p>
                                            </div>
                                        </div>
                                        <p class="muted">
                                            {{ $award->attempt->user->academicClass->name ?? '-' }}
                                            / {{ $award->attempt->exam->title }}
                                        </p>
                                        <p class="gift-name">{{ $award->gift_title }}</p>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        <div class="site-carousel-controls">
                            <button class="site-carousel-btn" type="button" data-carousel-prev aria-label="{{ __('site.exam.prev') }}">‹</button>
                            <div class="site-carousel-dots" data-carousel-dots></div>
                            <button class="site-carousel-btn" type="button" data-carousel-next aria-label="{{ __('site.exam.next') }}">›</button>
                        </div>
                    </div>

                </section>
            @endif
        </div>

    </section>

    <section id="testimonials" class="home-section home-section-alt" aria-labelledby="testimonials-title">
        <div class="shell">
            <div class="section-intro">
                <span class="eyebrow">{{ __('site.home.testimonials_eyebrow') }}</span>
                <h2 id="testimonials-title">{{ __('site.home.testimonials_title') }}</h2>
                <p>{{ __('site.home.testimonials_text') }}</p>
            </div>
            <div class="site-carousel" data-carousel data-autoplay="5500">
                <div class="site-carousel-track" data-carousel-track tabindex="0">
                    <div class="site-carousel-slide">
                        <article class="testimonial">
                            <blockquote>{{ __('site.home.t1_quote') }}</blockquote>
                            <div class="testimonial-author">
                                <strong>{{ __('site.home.t1_name') }}</strong>
                                <span>{{ __('site.home.t1_meta') }}</span>
                            </div>
                        </article>
                    </div>
                    <div class="site-carousel-slide">
                        <article class="testimonial">
                            <blockquote>{{ __('site.home.t2_quote') }}</blockquote>
                            <div class="testimonial-author">
                                <strong>{{ __('site.home.t2_name') }}</strong>
                                <span>{{ __('site.home.t2_meta') }}</span>
                            </div>
                        </article>
                    </div>
                    <div class="site-carousel-slide">
                        <article class="testimonial">
                            <blockquote>{{ __('site.home.t3_quote') }}</blockquote>
                            <div class="testimonial-author">
                                <strong>{{ __('site.home.t3_name') }}</strong>
                                <span>{{ __('site.home.t3_meta') }}</span>
                            </div>
                        </article>
                    </div>
                </div>
                <div class="site-carousel-controls">
                    <button class="site-carousel-btn" type="button" data-carousel-prev aria-label="{{ __('site.exam.prev') }}">‹</button>
                    <div class="site-carousel-dots" data-carousel-dots></div>
                    <button class="site-carousel-btn" type="button" data-carousel-next aria-label="{{ __('site.exam.next') }}">›</button>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="home-section" aria-labelledby="faq-title">
        <div class="shell">
            <div class="section-intro">
                <span class="eyebrow">{{ __('site.home.faq_eyebrow') }}</span>
                <h2 id="faq-title">{{ __('site.home.faq_title') }}</h2>
                <p>{{ __('site.home.faq_text') }}</p>
            </div>
            <div class="faq-list">
                <details class="faq-item" open>
                    <summary>{{ __('site.home.faq_1_q') }}</summary>
                    <p>{{ __('site.home.faq_1_a') }}</p>
                </details>
                <details class="faq-item">
                    <summary>{{ __('site.home.faq_2_q') }}</summary>
                    <p>{{ __('site.home.faq_2_a') }}</p>
                </details>
                <details class="faq-item">
                    <summary>{{ __('site.home.faq_3_q') }}</summary>
                    <p>{{ __('site.home.faq_3_a') }}</p>
                </details>
                <details class="faq-item">
                    <summary>{{ __('site.home.faq_4_q') }}</summary>
                    <p>{{ __('site.home.faq_4_a') }}</p>
                </details>
                <details class="faq-item">
                    <summary>{{ __('site.home.faq_5_q') }}</summary>
                    <p>{{ __('site.home.faq_5_a') }}</p>
                </details>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>

        (() => {
            const carousels = document.querySelectorAll('[data-carousel]');
            if (!carousels.length) return;

            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            carousels.forEach((carousel) => {
                const track = carousel.querySelector('[data-carousel-track]');
                const prevBtn = carousel.querySelector('[data-carousel-prev]');
                const nextBtn = carousel.querySelector('[data-carousel-next]');
                const dotsWrap = carousel.querySelector('[data-carousel-dots]');
                const slides = Array.from(carousel.querySelectorAll('.site-carousel-slide'));
                if (!track || slides.length === 0) return;

                let index = 0;
                let timer = null;
                const autoplay = Number(carousel.dataset.autoplay || 0);

                const pageSize = () => {
                    const slideWidth = slides[0].getBoundingClientRect().width;
                    if (!slideWidth) return 1;
                    return Math.max(1, Math.round(track.clientWidth / slideWidth));
                };

                const maxIndex = () => Math.max(0, slides.length - pageSize());

                const scrollToIndex = (nextIndex) => {
                    index = Math.min(Math.max(nextIndex, 0), maxIndex());
                    const target = slides[index];
                    if (!target) return;
                    track.scrollTo({ left: target.offsetLeft, behavior: 'smooth' });
                    updateUi();
                };

                const updateUi = () => {
                    const max = maxIndex();
                    if (prevBtn) prevBtn.disabled = index <= 0;
                    if (nextBtn) nextBtn.disabled = index >= max;

                    if (!dotsWrap) return;
                    const dots = Array.from(dotsWrap.children);
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('is-active', i === index);
                    });
                };

                const buildDots = () => {
                    if (!dotsWrap) return;
                    dotsWrap.innerHTML = '';
                    const count = maxIndex() + 1;
                    for (let i = 0; i < count; i += 1) {
                        const dot = document.createElement('button');
                        dot.type = 'button';
                        dot.className = 'site-carousel-dot' + (i === index ? ' is-active' : '');
                        dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
                        dot.addEventListener('click', () => {
                            stopAutoplay();
                            scrollToIndex(i);
                            startAutoplay();
                        });
                        dotsWrap.appendChild(dot);
                    }
                };

                const syncFromScroll = () => {
                    const slideWidth = slides[0].getBoundingClientRect().width + 16;
                    if (!slideWidth) return;
                    index = Math.min(maxIndex(), Math.round(track.scrollLeft / slideWidth));
                    updateUi();
                };

                const startAutoplay = () => {
                    stopAutoplay();
                    if (prefersReducedMotion || !autoplay || slides.length <= pageSize()) return;
                    timer = window.setInterval(() => {
                        const next = index >= maxIndex() ? 0 : index + 1;
                        scrollToIndex(next);
                    }, autoplay);
                };

                const stopAutoplay = () => {
                    if (timer) {
                        window.clearInterval(timer);
                        timer = null;
                    }
                };

                prevBtn?.addEventListener('click', () => {
                    stopAutoplay();
                    scrollToIndex(index - 1);
                    startAutoplay();
                });

                nextBtn?.addEventListener('click', () => {
                    stopAutoplay();
                    scrollToIndex(index + 1);
                    startAutoplay();
                });

                track.addEventListener('scroll', () => window.requestAnimationFrame(syncFromScroll), { passive: true });
                carousel.addEventListener('mouseenter', stopAutoplay);
                carousel.addEventListener('mouseleave', startAutoplay);
                carousel.addEventListener('focusin', stopAutoplay);
                carousel.addEventListener('focusout', startAutoplay);
                window.addEventListener('resize', () => {
                    buildDots();
                    scrollToIndex(Math.min(index, maxIndex()));
                    startAutoplay();
                });

                buildDots();
                updateUi();
                startAutoplay();
            });
        })();

    </script>
@endpush
