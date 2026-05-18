<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'MCQ Project' }}</title>
    @stack('styles')
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --bg: #f4f6f9;
            --panel: #ffffff;
            --panel-soft: #f8f9fb;
            --text: #1f2d3d;
            --muted: #6c757d;
            --line: #dee2e6;
            --primary: #007bff;
            --primary-dark: #0069d9;
            --danger: #dc3545;
            --accent: #17a2b8;
            --ink: #343a40;
            --sidebar: #343a40;
            --sidebar-dark: #2f353a;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--bg); color: var(--text); }
        a { color: inherit; text-decoration: none; }
        .shell { width: min(1280px, calc(100% - 32px)); margin: 0 auto; }
        .topbar { background: #ffffff; color: var(--text); border-bottom: 1px solid #dee2e6; box-shadow: 0 1px 2px rgba(0,0,0,.04); }
        .topbar-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 11px 0; }
        .brand { font-size: 20px; font-weight: 800; color: #343a40; }
        .nav { display: flex; gap: 10px; flex-wrap: wrap; }
        .nav a, .nav-button { padding: 8px 12px; border: 1px solid #dee2e6; border-radius: 4px; color: #343a40; background: transparent; min-height: auto; font-weight: 600; }
        .nav a:hover, .nav-button:hover { background: #f8f9fa; }
        .main { padding: 22px 0 56px; }
        .page-head, .section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .section-head.compact { align-items: center; margin-bottom: 12px; }
        h1 { margin: 0; font-size: 34px; line-height: 1.08; letter-spacing: 0; }
        h2 { margin: 0; font-size: 21px; }
        h3 { margin: 0 0 10px; font-size: 17px; }
        p { line-height: 1.6; margin-top: 0; }
        .muted { color: var(--muted); }
        .eyebrow { color: var(--accent); display: inline-block; font-size: 12px; font-weight: 800; letter-spacing: 0; margin-bottom: 8px; text-transform: uppercase; }
        .grid { display: grid; gap: 18px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .admin-layout { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 0; align-items: start; background: #fff; border: 1px solid #d8d8d8; box-shadow: none; min-height: calc(100vh - 130px); }
        .admin-sidebar { position: sticky; top: 0; min-height: calc(100vh - 92px); background: #332f2d; color: #d8d4d1; padding: 0; border-right: 1px solid rgba(255,255,255,.06); }
        .admin-brand { display: flex; align-items: center; gap: 11px; min-height: 58px; padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,.08); margin-bottom: 8px; background: #2d2927; }
        .admin-mark { display: inline-grid; place-items: center; width: 34px; height: 34px; border-radius: 50%; background: #007bff; color: #fff; font-weight: 900; box-shadow: 0 0 0 3px rgba(255,255,255,.08); }
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
        .stack { display: grid; gap: 14px; }
        .row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
        .between { display: flex; justify-content: space-between; gap: 12px; align-items: flex-start; }
        .button, button { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 8px 13px; border-radius: 0; border: 1px solid transparent; background: #2f6da8; color: #fff; cursor: pointer; font-weight: 700; font-family: inherit; }
        .button:hover, button:hover { background: var(--primary-dark); }
        .button.secondary, .secondary-action { background: #fff; color: var(--text); border-color: #cfcfcf; }
        .button.secondary:hover, .secondary-action:hover { background: #f3f7f5; }
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
        .admin-page .topbar { background: #3f3a37; border-bottom-color: #3f3a37; }
        .admin-page .brand { color: #fff; }
        .admin-page .nav a, .admin-page .nav-button { color: #f1efee; border-color: rgba(255,255,255,.18); }
        .admin-page .nav a:hover, .admin-page .nav-button:hover { background: rgba(255,255,255,.08); }
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

        @media (max-width: 760px) {
            .topbar-inner, .page-head, .admin-hero, .quiz-card-head, .between { align-items: flex-start; flex-direction: column; }
            .admin-layout { grid-template-columns: 1fr; }
            .admin-sidebar { position: static; }
            .admin-menu, .admin-grid, .grid-2 { grid-template-columns: 1fr; }
            .stat-grid, .mini-metrics { grid-template-columns: repeat(2, minmax(0, 1fr)); }
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
<body class="{{ request()->routeIs('admin.*') ? 'admin-page' : '' }}">
    <header class="topbar">
        <div class="shell topbar-inner">
            <a class="brand" href="{{ route('home') }}">MCQ Project</a>
            <nav class="nav">
                <a href="{{ route('home') }}">Take Exam</a>
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.index') }}">Admin</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="nav-button" type="submit">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Login</a>
                    <a href="{{ route('register') }}">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="shell main">
        @if (session('status'))
            <div class="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="errors">
                <strong>Please fix these:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
