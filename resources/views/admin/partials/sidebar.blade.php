@php
    $activeNav = $activeNav ?? '';
@endphp
<aside class="admin-sidebar">
    <div class="admin-brand">
        <img class="admin-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
        <div>
            <h2>Bd ModelTest Admin</h2>
            <p>{{ auth()->user()->name }}</p>
        </div>
    </div>

    <nav class="admin-menu" aria-label="Admin navigation">
        <a class="admin-nav-link admin-menu-direct {{ $activeNav === 'profile' ? 'is-active' : '' }}" href="{{ route('admin.profile.show') }}">My Profile</a>
        @if (auth()->user()->canAccessAdminRoute('admin.index'))
        <a class="admin-nav-link" href="{{ route('admin.index') }}#dashboard">Dashboard</a>
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.academic.classes'))
        <div class="admin-menu-group">
            <button class="admin-menu-toggle" type="button">
                Academic Setup
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.academic.classes') }}">Class</a>
                <a class="admin-nav-link" href="{{ route('admin.academic.subjects') }}">Subject</a>
                <a class="admin-nav-link" href="{{ route('admin.academic.chapters') }}">Oddhay / Chapter</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.index'))
        <div class="admin-menu-group">
            <button class="admin-menu-toggle" type="button">
                Question
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.index') }}#question-list">Question List</a>
                <a class="admin-nav-link" href="{{ route('admin.questions.create') }}">Question Add</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.exams.index'))
        <div class="admin-menu-group">
            <button class="admin-menu-toggle" type="button">
                Exam
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.exams.index') }}#exam-list">Exam List</a>
                <a class="admin-nav-link" href="{{ route('admin.exams.index') }}#add-exam">Add Exam</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.schools.index'))
        <div class="admin-menu-group">
            <button class="admin-menu-toggle" type="button">
                Schools
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.schools.index') }}">School List</a>
                <a class="admin-nav-link" href="{{ route('admin.schools.index') }}#add-school">Add School</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.blogs.index'))
        <div class="admin-menu-group {{ str_starts_with($activeNav, 'blogs') ? 'is-open' : '' }}">
            <button class="admin-menu-toggle" type="button">
                Blog
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link {{ $activeNav === 'blogs.index' ? 'is-active' : '' }}" href="{{ route('admin.blogs.index') }}">Blog List</a>
                <a class="admin-nav-link {{ $activeNav === 'blogs.create' ? 'is-active' : '' }}" href="{{ route('admin.blogs.create') }}">Add Blog</a>
            </div>
        </div>
        @endif

        @if (auth()->user()->is_super_admin)
            @if (auth()->user()->canAccessAdminRoute('admin.users.index'))
        <div class="admin-menu-group">
                <button class="admin-menu-toggle" type="button">
                    User
                    <span></span>
                </button>
                <div class="admin-submenu">
                    <a class="admin-nav-link" href="{{ route('admin.users.index') }}">User List</a>
                    <a class="admin-nav-link" href="{{ route('admin.users.create') }}">Add User</a>
                </div>
            </div>
        @endif
        @endif

        @if (auth()->user()->canAccessAdminRoute('admin.students.index'))
        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
        @endif
        @if (auth()->user()->canAccessAdminRoute('admin.results.index'))
        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
        @endif
        @if (auth()->user()->canAccessAdminRoute('admin.custom-results.index'))
        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.custom-results.index') }}">Custom Result</a>
        @endif
        @if (auth()->user()->canAccessAdminRoute('admin.gift-recipients.index'))
        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
        @endif
    </nav>
</aside>
<script>
    (() => {
        const sidebar = document.currentScript.previousElementSibling;
        const links = Array.from(sidebar.querySelectorAll('.admin-nav-link'));
        const groups = Array.from(sidebar.querySelectorAll('.admin-menu-group'));
        const initialActive = sidebar.querySelector('.admin-nav-link.is-active');

        function setGroupOpen(group, open) {
            group.classList.toggle('is-open', open);
            group.querySelector('.admin-menu-toggle').setAttribute('aria-expanded', String(open));
            group.querySelector('.admin-submenu').style.removeProperty('display');
        }

        function updateActiveMenu() {
            const current = new URL(window.location.href);
            const matches = links.filter(link => {
                const target = new URL(link.href);
                return target.origin === current.origin && target.pathname === current.pathname;
            });
            const active = matches.find(link => new URL(link.href).hash === current.hash)
                || matches[0] || initialActive;

            links.forEach(link => {
                const selected = link === active;
                link.classList.toggle('is-active', selected);
                if (selected) {
                    link.setAttribute('aria-current', current.hash ? 'location' : 'page');
                } else {
                    link.removeAttribute('aria-current');
                }
            });

            groups.forEach(group => {
                const selected = Boolean(active && group.contains(active));
                group.classList.toggle('is-active', selected);
                setGroupOpen(group, selected);
            });
        }

        // Handle toggles here before legacy page-specific handlers can toggle them again.
        sidebar.addEventListener('click', event => {
            const button = event.target.closest('.admin-menu-toggle');
            if (!button) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            const group = button.closest('.admin-menu-group');
            setGroupOpen(group, !group.classList.contains('is-open'));
        }, true);

        window.addEventListener('hashchange', updateActiveMenu);
        window.addEventListener('pageshow', updateActiveMenu);
        updateActiveMenu();
    })();
</script>
