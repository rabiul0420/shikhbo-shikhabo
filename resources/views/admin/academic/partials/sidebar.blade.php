<aside class="admin-sidebar">
    <div class="admin-brand">
        <img class="admin-logo" src="{{ asset('logo.svg') }}" alt="" aria-hidden="true">
        <div>
            <h2>Shikhbo Shikhabo Admin</h2>
            <p>{{ auth()->user()->name }}</p>
        </div>
    </div>

    <nav class="admin-menu" aria-label="Admin navigation">
        <a class="admin-nav-link" href="{{ route('admin.index') }}#dashboard">Dashboard</a>

        <div class="admin-menu-group is-open">
            <button class="admin-menu-toggle" type="button">
                Academic Setup
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link {{ $activeAcademic === 'classes' ? 'is-active' : '' }}" href="{{ route('admin.academic.classes') }}">Class</a>
                <a class="admin-nav-link {{ $activeAcademic === 'subjects' ? 'is-active' : '' }}" href="{{ route('admin.academic.subjects') }}">Subject</a>
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
                Schools
                <span></span>
            </button>
            <div class="admin-submenu">
                <a class="admin-nav-link" href="{{ route('admin.schools.index') }}">School List</a>
                        <a class="admin-nav-link" href="{{ route('admin.schools.index') }}#add-school">Add School</a>
                    </div>
        </div>

        @if (auth()->user()->is_super_admin)
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

        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
        <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
    </nav>
</aside>




