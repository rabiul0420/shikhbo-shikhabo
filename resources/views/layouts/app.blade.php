<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

<script async src="https://www.googletagmanager.com/gtag/js?id=G-LFJ2HP52SW"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-LFJ2HP52SW');
</script>
    @php
        $siteName = 'Shikhbo Shikhabo';
        $defaultDescription = 'Shikhbo Shikhabo helps students practise class-wise, subject-wise, and chapter-wise online exams with quick results.';
        $seoTitle = $title ?? $siteName;
        $fullTitle = str_contains($seoTitle, $siteName) ? $seoTitle : $seoTitle . ' | ' . $siteName;
        $seoDescription = $description ?? $defaultDescription;
        $privateRoute = request()->routeIs([
            'admin.*',
            'login',
            'register',
            'profile.*',
            'exam-attempts.*',
            'exams.submit',
            'exams.results',
            'exams.store',
            'exams.update',
            'exams.destroy',
            'custom-exams.*',
            'custom-exam-attempts.*',
        ]);
        $seoRobots = $robots ?? ($privateRoute ? 'noindex, nofollow' : 'index, follow');
        $seoCanonical = $canonical ?? url()->current();
        $seoImage = $image ?? asset('logo.svg');
        $seoKeywords = $keywords ?? null;
        $schemaGraph = [
            [
                '@type' => 'Organization',
                '@id' => url('/') . '#organization',
                'name' => $siteName,
                'url' => url('/'),
                'logo' => asset('logo.svg'),
            ],
            [
                '@type' => 'WebSite',
                '@id' => url('/') . '#website',
                'name' => $siteName,
                'url' => url('/'),
                'publisher' => ['@id' => url('/') . '#organization'],
                'inLanguage' => ['bn', 'en'],
            ],
        ];
        if (! empty($schemaExtra ?? [])) {
            $schemaGraph = array_merge($schemaGraph, $schemaExtra);
        }
    @endphp
    @unless (request()->routeIs('admin.*'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-KX5YMCXV92"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', 'G-KX5YMCXV92');
        </script>
    @endunless
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if ($seoKeywords)
        <meta name="keywords" content="{{ $seoKeywords }}">
    @endif
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $fullTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    @if ($seoRobots === 'index, follow')
        <script type="application/ld+json">
            {!! json_encode(['@context' => 'https://schema.org', '@graph' => $schemaGraph], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
        </script>
    @endif
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            color-scheme: light;
            font-family: "Hind Siliguri", "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
            --bg: #f0f7fb;
            --panel: #ffffff;
            --panel-soft: #f8fafc;
            --text: #0f172a;
            --muted: #64748b;
            --line: #dbe7f0;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #0ea5e9;
            --danger: #dc3545;
            --accent: #059669;
            --accent-soft: #34d399;
            --ink: #111827;
            --sidebar: #343a40;
            --sidebar-dark: #2f353a;
            --success: #059669;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --radius: 14px;
            --shadow: 0 10px 30px rgba(15, 23, 42, .08);
            --font-display: "Hind Siliguri", "Sora", "Plus Jakarta Sans", sans-serif;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--bg); color: var(--text); }
        a { color: inherit; text-decoration: none; }
        .shell { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }
        .topbar {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 255, 255, .86);
            color: var(--text);
            border-bottom: 1px solid rgba(219, 231, 240, .9);
            backdrop-filter: blur(14px);
            box-shadow: 0 1px 0 rgba(255,255,255,.6);
        }
        .topbar-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 12px 0; }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.02em;
        }
        .brand-logo { width: 38px; height: 38px; flex: 0 0 auto; border-radius: 10px; }
        .nav-toggle {
            display: none;
            width: 42px;
            height: 42px;
            padding: 0;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #fff;
            color: var(--ink);
            cursor: pointer;
        }
        .nav-toggle span {
            display: block;
            width: 18px;
            height: 2px;
            margin: 4px auto;
            background: currentColor;
            border-radius: 999px;
        }
        .nav { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
        .nav a, .nav-button {
            padding: 8px 12px;
            border: 0;
            border-radius: 999px;
            color: #334155;
            background: transparent;
            min-height: auto;
            font-weight: 600;
            font-size: 14px;
        }
        .nav a.nav-highlight {
            background: linear-gradient(135deg, var(--primary-soft), var(--primary));
            border-color: transparent;
            color: #fff;
            padding-inline: 16px;
            box-shadow: 0 8px 18px rgba(37, 99, 235, .24);
        }
        .nav a:hover, .nav-button:hover { background: #eef6ff; color: var(--primary); }
        .nav a.nav-highlight:hover {
            background: linear-gradient(135deg, #0284c7, var(--primary-dark));
            color: #fff;
        }
        .nav a.is-active { color: var(--primary); background: #eef6ff; }
        .lang-switch {
            display: inline-flex;
            align-items: center;
            gap: 2px;
            margin-left: 4px;
            padding: 3px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: #fff;
        }
        .lang-switch a {
            min-width: 40px;
            min-height: 30px;
            padding: 4px 10px !important;
            border-radius: 999px !important;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .02em;
            color: var(--muted) !important;
            background: transparent !important;
            justify-content: center;
        }
        .lang-switch a.is-active {
            background: linear-gradient(135deg, #34d399, #059669) !important;
            color: #fff !important;
            box-shadow: 0 1px 2px rgba(5, 150, 105, .25);
        }
        .lang-switch a:hover:not(.is-active) {
            background: #ecfdf5 !important;
            color: #047857 !important;
        }
        .main { padding: 22px 0 56px; min-height: calc(100vh - 154px); }
        .main-home { padding: 0 0 0; min-height: 0; width: 100%; }
        .site-footer {
            background:
                radial-gradient(circle at 10% 0%, rgba(14, 165, 233, .18), transparent 42%),
                radial-gradient(circle at 90% 100%, rgba(5, 150, 105, .16), transparent 40%),
                #0f172a;
            border-top: 0;
            color: #94a3b8;
        }
        .footer-inner {
            display: grid;
            grid-template-columns: 1.4fr repeat(3, minmax(0, 1fr));
            gap: 32px;
            padding: 48px 0 28px;
        }
        .footer-brand { display: grid; gap: 12px; align-content: start; }
        .footer-brand .brand { font-size: 18px; width: fit-content; color: #fff; }
        .footer-brand p { margin: 0; font-size: 14px; line-height: 1.7; max-width: 320px; }
        .footer-col h3 {
            margin: 0 0 14px;
            color: #fff;
            font-family: var(--font-display);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: .02em;
            text-transform: uppercase;
        }
        .footer-links {
            display: grid;
            gap: 10px;
            justify-content: start;
            font-size: 14px;
            font-weight: 600;
        }
        .footer-links a { color: #cbd5e1; width: fit-content; }
        .footer-links a:hover { color: #fff; }
        .footer-bottom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 18px 0 28px;
            border-top: 1px solid rgba(148, 163, 184, .18);
            font-size: 13px;
        }
        .footer-copy { font-size: 13px; color: #94a3b8; }
        .page-head, .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .section-head.compact { align-items: center; margin-bottom: 12px; }
        h1 { margin: 0; font-family: var(--font-display); font-size: 34px; line-height: 1.08; letter-spacing: -.03em; }
        h2 { margin: 0; font-family: var(--font-display); font-size: 21px; letter-spacing: -.02em; }
        h3 { margin: 0 0 10px; font-size: 17px; }
        p { line-height: 1.6; margin-top: 0; }
        .muted { color: var(--muted); }
        .eyebrow { color: var(--primary); display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: .06em; margin-bottom: 8px; text-transform: uppercase; }
        .grid { display: grid; gap: 18px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-layout { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 0; align-items: start; background: #fff; border: 1px solid #d8d8d8; box-shadow: none; min-height: calc(100vh - 130px); }
        .admin-sidebar { position: sticky; top: 0; min-height: calc(100vh - 92px); background: #332f2d; color: #d8d4d1; padding: 0; border-right: 1px solid rgba(255,255,255,.06); }
        .admin-brand { display: flex; align-items: center; gap: 11px; min-height: 58px; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.08); margin-bottom: 8px; background: #2d2927; }
        .admin-logo { width: 34px; height: 34px; flex: 0 0 auto; border-radius: 10px; box-shadow: 0 0 0 3px rgba(255,255,255,.08); }
        .admin-brand h2 { font-size: 18px; margin: 0; color: #fff; font-weight: 500; }
        .admin-brand p { color: #adb5bd; font-size: 13px; margin: 2px 0 0; }
        .admin-menu { display: grid; gap: 4px; padding: 8px; }
        .admin-menu-group { display: grid; gap: 4px; }
        .admin-menu-toggle { width: 100%; min-height: 34px; justify-content: space-between; padding: 7px 10px; background: transparent; color: #aaa39e; border: 0; border-radius: 0; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        .admin-menu-toggle:hover { background: rgba(255,255,255,.08); color: #fff; }
        .admin-menu-toggle span { width: 8px; height: 8px; border-right: 2px solid currentColor; border-bottom: 2px solid currentColor; transform: rotate(45deg); transition: transform .18s ease; }
        .admin-menu-group.is-open .admin-menu-toggle span { transform: rotate(225deg); }
        .admin-submenu { display: none; gap: 2px; padding-left: 0; margin-left: 0; }
        .admin-menu-group.is-open .admin-submenu { display: grid; }
        .admin-menu a { display: block; padding: 10px 12px; border-left: 3px solid transparent; border-radius: 0; color: #d8d4d1; font-weight: 500; transition: background .18s ease, color .18s ease, border-color .18s ease; }
        .admin-menu a.admin-menu-direct { min-height: 34px; padding: 7px 10px; border-left: 0; font-size: 11px; text-transform: uppercase; letter-spacing: .04em; }
        .admin-menu a:hover { background: rgba(255,255,255,.07); border-left-color: #f0f0f0; color: #fff; }
        .admin-menu a.is-active { background: #007bff; border-left-color: #8ec5ff; color: #fff; }
        .admin-content { min-width: 0; display: grid; gap: 14px; padding: 0 14px 16px; background: #fff; }
        .admin-pagebar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 2px 12px; border-bottom: 1px solid #ececec; }
        .admin-pagebar h1 { color: #3c3c3c; font-size: 24px; font-weight: 400; }
        .admin-pagebar p { margin-bottom: 0; font-size: 13px; }
        .admin-breadcrumb { display: flex; align-items: center; gap: 8px; color: #6c757d; font-size: 13px; }
        .admin-breadcrumb a { color: #007bff; }
        .admin-breadcrumb span::before { content: "/"; margin-right: 8px; color: #adb5bd; }
        .admin-hero { background: #454a4f; border: 1px solid #454a4f; border-radius: 0; padding: 12px 14px; display: flex; justify-content: space-between; gap: 18px; align-items: center; box-shadow: none; color: #fff; }
        .admin-hero .eyebrow, .admin-hero .muted { color: rgba(255,255,255,.8); }
        .admin-hero h2 { font-size: 18px; font-weight: 500; }
        .admin-hero p { margin-bottom: 0; max-width: 680px; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 10px; }
        .stat-card { position: relative; overflow: hidden; background: var(--panel); border: 1px solid #dcdcdc; border-radius: 0; padding: 12px; min-height: 92px; display: flex; flex-direction: column-reverse; justify-content: flex-end; box-shadow: none; color: #fff; }
        .stat-card i { position: absolute; right: 12px; top: 10px; color: rgba(255,255,255,.28); font-style: normal; font-size: 28px; font-weight: 800; }
        .stat-card span { color: rgba(255,255,255,.92); font-size: 13px; font-weight: 700; }
        .stat-card strong { font-size: 32px; line-height: 1; margin-bottom: 8px; }
        .stat-info { background: var(--info); }
        .stat-success { background: var(--success); }
        .stat-warning { background: var(--warning); color: #1f2d3d; }
        .stat-warning span { color: rgba(31,45,61,.82); }
        .stat-warning i { color: rgba(31,45,61,.16); }
        .stat-danger { background: var(--danger); }
        .admin-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; align-items: start; }
        .admin-grid-tight { align-items: stretch; }
        .panel { background: var(--panel); border: 1px solid #dcdcdc; border-radius: 0; padding: 14px; box-shadow: none; }
        .panel-soft { background: #fafafa; border: 1px solid #dcdcdc; border-radius: 0; padding: 14px; }
        .content-panel { max-width: 860px; }
        .content-panel h2 { margin-top: 18px; }
        .content-panel h2:first-child { margin-top: 0; }
        .profile-page { display: grid; justify-items: center; gap: 18px; }
        .profile-page .page-head { width: min(680px, 100%); justify-content: center; text-align: center; }
        .profile-page .page-head p { margin-bottom: 0; }
        .profile-card { width: min(680px, 100%); padding: 24px; }
        .profile-card .row { justify-content: center; }
        .profile-form { width: min(640px, 100%); margin: 0 auto; }
        .profile-form label { gap: 8px; }
        .profile-form-actions { justify-content: center; padding-top: 4px; }
        .profile-photo-field { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 12px; align-items: center; }
        .profile-photo-field input { min-width: 0; }
        .profile-list { display: grid; gap: 10px; margin: 0; }
        .profile-list div { display: grid; grid-template-columns: 160px minmax(0, 1fr); gap: 14px; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--line); }
        .profile-list div:last-child { border-bottom: 0; }
        .profile-list dt { color: var(--muted); font-weight: 800; }
        .profile-list dd { margin: 0; font-weight: 700; overflow-wrap: anywhere; }
        .profile-photo-preview { width: 104px; height: 104px; object-fit: cover; border-radius: 50%; border: 1px solid var(--line); background: #f8f9fa; }
        .profile-photo-field .profile-photo-preview { width: 58px; height: 58px; }
        .stack { display: grid; gap: 14px; }
        .row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .between { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
        .button, button { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 10px 16px; border-radius: 999px; border: 1px solid transparent; background: linear-gradient(135deg, var(--primary-soft), var(--primary)); color: #fff; cursor: pointer; font-weight: 700; font-family: inherit; box-shadow: 0 10px 22px rgba(37, 99, 235, .18); transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
        .button:hover, button:hover { background: linear-gradient(135deg, #0284c7, var(--primary-dark)); transform: translateY(-1px); }
        .button.secondary, .secondary-action { background: #fff; color: var(--text); border-color: var(--line); box-shadow: none; }
        .button.secondary:hover, .secondary-action:hover { background: #f0f9ff; border-color: #bae6fd; color: var(--primary); }
        button.danger { background: #fff; color: var(--danger); border-color: #f1c4bf; }
        button.danger:hover { background: #fff1ef; }
        button.small { min-height: 32px; padding: 6px 10px; font-size: 13px; }
        label { display: grid; gap: 6px; font-weight: 800; }
        input, textarea, select { width: 100%; padding: 9px 10px; border: 1px solid #cfcfcf; border-radius: 0; background: #fff; color: var(--text); font: inherit; }
        input:focus, textarea:focus, select:focus { outline: 0; border-color: #80bdff; box-shadow: 0 0 0 .2rem rgba(0,123,255,.18); }
        textarea { min-height: 92px; resize: vertical; }
        .check-label, .toggle-label { display: flex; align-items: center; gap: 8px; font-weight: 700; }
        .check-label input, .toggle-label input, .option input[type="checkbox"], .option input[type="radio"] { width: auto; }
        .status { margin-bottom: 18px; padding: 12px 14px; border-radius: 6px; background: #e7f8f2; color: #07533e; border: 1px solid #b8eadb; }
        .errors { margin-bottom: 18px; padding: 12px 14px; border-radius: 6px; background: #fff1ef; color: var(--danger); border: 1px solid #f1c4bf; }
        .list, .quiz-list, .question-list, .option-list { display: grid; gap: 12px; }
        .pill, .count-badge { display: inline-flex; align-items: center; justify-content: center; min-height: 26px; padding: 4px 9px; border-radius: 999px; background: #eaf1f5; color: #344450; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .published { background: #dff8ed; color: #07533e; }
        .draft { background: #fff3d8; color: #7a4b00; }
        .activity-item { display: flex; justify-content: space-between; gap: 14px; align-items: center; padding: 13px 0; border-bottom: 1px solid var(--line); }
        .activity-item:last-child { border-bottom: 0; padding-bottom: 0; }
        .activity-item p { margin-bottom: 0; font-size: 13px; }
        .quiz-section { display: grid; gap: 14px; }
        .quiz-card { background: var(--panel); border: 1px solid #dcdcdc; border-top: 3px solid #454a4f; border-radius: 0; padding: 16px; display: grid; gap: 16px; box-shadow: none; }
        .quiz-card-head { display: flex; justify-content: space-between; gap: 18px; align-items: flex-start; }
        .quiz-card h3 { font-size: 22px; margin: 12px 0 8px; }
        .quiz-card p { margin-bottom: 0; }
        .quiz-actions { display: flex; gap: 10px; flex-wrap: wrap; justify-content: flex-end; }
        .mini-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .mini-metrics div { background: #f8f9fa; border: 1px solid var(--line); border-radius: 4px; padding: 12px; }
        .mini-metrics strong { display: block; font-size: 22px; }
        .mini-metrics span { color: var(--muted); font-size: 12px; font-weight: 800; }
        .question-grid { display: grid; grid-template-columns: minmax(280px, .92fr) minmax(0, 1.08fr); gap: 16px; align-items: start; }
        .option { display: flex; gap: 10px; align-items: flex-start; padding: 10px; border: 1px solid var(--line); border-radius: 6px; background: #fff; }
        .option input[type="checkbox"] { margin-top: 12px; }
        .question-card { background: #fff; border: 1px solid #dcdcdc; border-radius: 0; padding: 14px; }
        .answer-list { display: grid; gap: 8px; list-style: none; margin: 12px 0 0; padding: 0; }
        .answer-list li { border: 1px solid var(--line); border-radius: 6px; padding: 8px 10px; background: #fbfcfd; }
        .answer-list li span { color: var(--muted); display: inline-block; font-size: 12px; font-weight: 800; margin-right: 8px; text-transform: uppercase; }
        .answer-list li.is-correct { background: #ecfdf3; border-color: #b8eadb; }
        .score { font-size: 42px; font-weight: 800; color: var(--primary); margin: 0; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 520px; }
        th, td { padding: 10px; border-bottom: 1px solid #dddddd; border-right: 1px solid #e5e5e5; text-align: left; vertical-align: top; }
        th:first-child, td:first-child { border-left: 1px solid #e5e5e5; }
        th { color: #202020; background: #f3f3f3; font-size: 12px; text-transform: none; }
        .table-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 132px; }
        .dt-container { color: var(--text); font-size: 14px; }
        .dt-container .dt-layout-row { gap: 12px; margin: 0 0 12px; }
        .dt-container .dt-layout-row:last-child { margin: 12px 0 0; }
        .dt-container .dt-length label, .dt-container .dt-search label { display: inline-flex; align-items: center; gap: 8px; font-weight: 600; color: var(--muted); }
        .dt-container .dt-length select { width: auto; min-width: 74px; margin-right: 6px; }
        .dt-container .dt-search input { width: min(220px, 100%); margin-left: 8px; }
        .dt-container .dt-info { color: var(--muted); font-size: 13px; }
        .dt-container .dt-paging .dt-paging-button { border: 1px solid var(--line); border-radius: 4px; color: #343a40 !important; margin-left: 4px; padding: 6px 10px; }
        .dt-container .dt-paging .dt-paging-button.current, .dt-container .dt-paging .dt-paging-button.current:hover { background: var(--primary); border-color: var(--primary); color: #fff !important; }
        .dt-container .dt-paging .dt-paging-button:hover { background: #f8f9fa; border-color: #ced4da; color: #343a40 !important; }
        table.dataTable.admin-data-table > thead > tr > th { border-bottom: 1px solid var(--line); }
        table.dataTable.admin-data-table > tbody > tr:hover { background: #f7f7f7; }
        .admin-page .shell { width: min(1360px, calc(100% - 20px)); }
        .admin-page .topbar { background: #3f3a37; border-bottom-color: #3f3a37; backdrop-filter: none; }
        .admin-page .brand { color: #fff; }
        .admin-page .nav-toggle { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.18); color: #fff; }
        .admin-page .nav a, .admin-page .nav-button { color: #f1efee; border-color: rgba(255,255,255,.18); }
        .admin-page .nav a.nav-highlight { border-color: var(--primary); color: #fff; }
        .admin-page .nav a:hover, .admin-page .nav-button:hover { background: rgba(255,255,255,.08); color: #fff; }
        .admin-page .nav a.nav-highlight:hover { background: #0056b3; border-color: #0056b3; }
        .admin-page .nav a.is-active { background: rgba(255,255,255,.1); color: #fff; }
        .admin-page .site-footer { background: #3f3a37; border-top-color: rgba(255,255,255,.12); }
        .admin-page .site-footer .brand { color: #fff; }
        .admin-page .site-footer, .admin-page .footer-copy, .admin-page .footer-brand p { color: #d8d4d1; }
        .admin-page .footer-links a:hover { color: #fff; }
        .modal-backdrop { position: fixed; inset: 0; z-index: 50; display: none; align-items: flex-start; justify-content: center; overflow-y: auto; padding: 36px 16px; background: rgba(15, 23, 42, .52); }
        .modal-backdrop.is-open { display: flex; }
        .modal-panel { width: min(620px, 100%); background: #fff; border: 1px solid var(--line); border-radius: 4px; box-shadow: 0 20px 45px rgba(0,0,0,.24); padding: 18px; }
        .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        .modal-head h3 { margin-bottom: 0; }
        .modal-actions { display: flex; justify-content: flex-end; gap: 10px; flex-wrap: wrap; }
        body.modal-open { overflow: hidden; }

        @media (max-width: 1040px) {
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { position: static; }
            .admin-menu { grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; }
            .stat-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .question-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 900px) {
            .nav-toggle { display: inline-grid; place-items: center; }
            .nav {
                display: none;
                position: absolute;
                left: 16px;
                right: 16px;
                top: calc(100% + 8px);
                padding: 12px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: #fff;
                box-shadow: var(--shadow);
                flex-direction: column;
                align-items: stretch;
                gap: 4px;
            }
            .nav.is-open { display: flex; }
            .nav a, .nav-button { width: 100%; justify-content: flex-start; border-radius: 12px; }
            .lang-switch { width: 100%; justify-content: stretch; margin: 6px 0 0; }
            .lang-switch a { flex: 1; }
            .topbar { position: sticky; }
            .topbar-inner { position: relative; }
            .footer-inner { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 760px) {
            .topbar-inner, .page-head, .admin-hero, .quiz-card-head, .between { align-items: flex-start; flex-direction: column; }
            .footer-inner { grid-template-columns: 1fr; gap: 24px; }
            .footer-links { justify-content: flex-start; }
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { position: static; }
            .admin-menu, .admin-grid, .grid-2 { grid-template-columns: 1fr; }
            .stat-grid, .mini-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .profile-page .page-head { align-items: center; }
            .profile-card { padding: 18px; }
            .profile-photo-field { grid-template-columns: 1fr; justify-items: center; text-align: center; }
            .profile-list div { grid-template-columns: 1fr; gap: 5px; text-align: center; }
            .profile-list dd { display: flex; justify-content: center; }
            h1 { font-size: 25px; }
        }

        @media (max-width: 460px) {
            .shell { width: min(100% - 20px, 1280px); }
            .stat-grid, .mini-metrics { grid-template-columns: 1fr; }
            .quiz-actions { width: 100%; }
            .quiz-actions form, .quiz-actions button { width: 100%; }
        }
    </style>
</head>
<body class="{{ request()->routeIs('admin.*') ? 'admin-page' : '' }}{{ request()->routeIs('home') ? ' is-home' : '' }}">
    <header class="topbar">
        <div class="shell topbar-inner">
            <a class="brand" href="{{ route('home') }}">
                <img class="brand-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
                <span>Shikhbo Shikhabo</span>
            </a>
            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="{{ __('site.nav.menu') }}">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="nav" id="site-nav">
                @if (request()->routeIs('admin.*'))
                    <a href="{{ route('home') }}">{{ __('site.nav.take_exam') }}</a>
                    <a href="{{ route('admin.index') }}">{{ __('site.nav.admin') }}</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-button" type="submit">{{ __('site.nav.logout') }}</button>
                        </form>
                    @endauth
                @else
                    <a class="{{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">{{ __('site.nav.home') }}</a>
                    <a class="{{ request()->routeIs('exams.directory', 'classes.exams') ? 'is-active' : '' }}" href="{{ route('exams.directory') }}">{{ __('site.nav.exams') }}</a>
                    <a class="{{ request()->routeIs('about-us') ? 'is-active' : '' }}" href="{{ route('about-us') }}">{{ __('site.nav.about') }}</a>
                    <a class="{{ request()->routeIs('contact-us') ? 'is-active' : '' }}" href="{{ route('contact-us') }}">{{ __('site.nav.contact') }}</a>
                    @auth
                        @unless (auth()->user()->is_admin)
                            <a href="{{ route('custom-exams.create') }}">{{ __('site.nav.custom_exam') }}</a>
                        @endunless
                        <a href="{{ route('profile.show') }}">{{ __('site.nav.profile') }}</a>
                        <a href="{{ route('exam-attempts.index') }}">{{ __('site.nav.results') }}</a>
                        @if (auth()->user()->is_admin)
                            <a href="{{ route('admin.index') }}">{{ __('site.nav.admin') }}</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-button" type="submit">{{ __('site.nav.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}">{{ __('site.nav.login') }}</a>
                        <a class="nav-highlight" href="{{ route('register') }}">{{ __('site.nav.register') }}</a>
                    @endauth
                    <div class="lang-switch" role="group" aria-label="{{ __('site.lang.label') }}">
                        <a class="{{ app()->getLocale() === 'bn' ? 'is-active' : '' }}" href="{{ route('locale.switch', 'bn') }}" hreflang="bn" lang="bn">{{ __('site.lang.bn') }}</a>
                        <a class="{{ app()->getLocale() === 'en' ? 'is-active' : '' }}" href="{{ route('locale.switch', 'en') }}" hreflang="en" lang="en">{{ __('site.lang.en') }}</a>
                    </div>
                @endif
            </nav>
        </div>
    </header>

    <main class="{{ request()->routeIs('home') ? 'main main-home' : 'shell main' }}">
        @if (session('status'))
            <div class="{{ request()->routeIs('home') ? 'shell' : '' }} status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="{{ request()->routeIs('home') ? 'shell' : '' }} errors">
                <strong>{{ __('site.fix.fix_errors') }}</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @unless (request()->routeIs('admin.*'))
        <footer class="site-footer">
            <div class="shell footer-inner">
                <div class="footer-brand">
                    <a class="brand" href="{{ route('home') }}">
                        <img class="brand-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
                        <span>Shikhbo Shikhabo</span>
                    </a>
                    <p>{{ __('site.footer.tagline') }}</p>
                </div>
                <div class="footer-col">
                    <h3>{{ __('site.footer.explore') }}</h3>
                    <nav class="footer-links" aria-label="{{ __('site.footer.explore') }}">
                        <a href="{{ route('home') }}">{{ __('site.nav.home') }}</a>
                        <a href="{{ route('exams.directory') }}">{{ __('site.nav.exams') }}</a>
                        <a href="{{ route('home') }}#featured-exams">{{ __('site.footer.featured_exams') }}</a>
                        <a href="{{ route('home') }}#why-choose">{{ __('site.footer.why_us') }}</a>
                        <a href="{{ route('home') }}#faq">{{ __('site.footer.faq') }}</a>
                    </nav>
                </div>
                <div class="footer-col">
                    <h3>{{ __('site.footer.students') }}</h3>
                    <nav class="footer-links" aria-label="{{ __('site.footer.students') }}">
                        @auth
                            @unless (auth()->user()->is_admin)
                                <a href="{{ route('custom-exams.create') }}">{{ __('site.nav.custom_exam') }}</a>
                                <a href="{{ route('custom-exams.results') }}">{{ __('site.footer.custom_results') }}</a>
                            @endunless
                            <a href="{{ route('profile.show') }}">{{ __('site.nav.profile') }}</a>
                            <a href="{{ route('exam-attempts.index') }}">{{ __('site.nav.results') }}</a>
                        @else
                            <a href="{{ route('register') }}">{{ __('site.footer.create_account') }}</a>
                            <a href="{{ route('login') }}">{{ __('site.nav.login') }}</a>
                            <a href="{{ route('login', ['redirect_to' => route('custom-exams.create', [], false)]) }}">{{ __('site.nav.custom_exam') }}</a>
                        @endauth
                        <a href="{{ route('home') }}#gift-winners">{{ __('site.footer.gift_winners') }}</a>
                    </nav>
                </div>
                <div class="footer-col">
                    <h3>{{ __('site.footer.company') }}</h3>
                    <nav class="footer-links" aria-label="{{ __('site.footer.company') }}">
                        <a href="{{ route('about-us') }}">{{ __('site.nav.about') }}</a>
                        <a href="{{ route('contact-us') }}">{{ __('site.nav.contact') }}</a>
                        <a href="{{ route('privacy-policy') }}">{{ __('site.footer.privacy') }}</a>
                    </nav>
                </div>
            </div>
            <div class="shell footer-bottom">
                <span class="footer-copy">&copy; {{ date('Y') }} Shikhbo Shikhabo. {{ __('site.footer.rights') }}</span>
                <span class="footer-copy">{{ __('site.footer.made_for') }}</span>
            </div>
        </footer>
    @endunless
    <script>
        (() => {
            const toggle = document.querySelector('.nav-toggle');
            const nav = document.getElementById('site-nav');
            if (!toggle || !nav) return;

            toggle.addEventListener('click', () => {
                const open = nav.classList.toggle('is-open');
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            });

            nav.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    nav.classList.remove('is-open');
                    toggle.setAttribute('aria-expanded', 'false');
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
