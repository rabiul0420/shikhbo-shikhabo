<aside class="admin-sidebar">
    <div class="admin-brand">
        <span class="admin-mark">M</span>
        <div>
            <h2>MCQ Admin</h2>
            <p>{{ auth()->user()->name }}</p>
        </div>
    </div>

    <nav class="admin-menu" aria-label="Admin navigation">
        <div class="admin-menu-group is-open">
            <button class="admin-menu-toggle" type="button">
                Main Menu
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.index') }}#dashboard">Dashboard</a>
            </div>
        </div>

        <div class="admin-menu-group is-open">
            <button class="admin-menu-toggle" type="button">
                Academic Setup
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link {{ $activeAcademic === 'classes' ? 'is-active' : '' }}" href="{{ route('admin.academic.classes') }}">Class</a>
                <a class="admin-nav-link {{ $activeAcademic === 'lessons' ? 'is-active' : '' }}" href="{{ route('admin.academic.lessons') }}">Lesson</a>
                <a class="admin-nav-link {{ $activeAcademic === 'chapters' ? 'is-active' : '' }}" href="{{ route('admin.academic.chapters') }}">Oddhay / Chapter</a>
            </div>
        </div>

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

        <div class="admin-menu-group">
            <button class="admin-menu-toggle" type="button">
                Result
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.results.index') }}">All Results</a>
            </div>
        </div>
    </nav>
</aside>
