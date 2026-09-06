@extends('layouts.app', [
    'title' => 'Privacy Policy',
    'description' => 'Read how Bd ModelTest handles student account information, exam attempts, answers, scores, and learning records.',
    'canonical' => route('privacy-policy'),
])

@section('content')
    <div class="page-head">
        <div>
            <h1>Privacy Policy</h1>
            <p class="muted">How Bd ModelTest handles learner information.</p>
        </div>
    </div>

    <section class="panel content-panel">
        <h2>Overview</h2>
        <p>
            This Privacy Policy explains how Bd ModelTest collects, uses, and protects information when students,
            teachers, and administrators use the platform. We aim to collect only the information needed to run exams,
            manage learning content, and show results securely.
        </p>

        <h2>Information We Use</h2>
        <p>
            We use basic account information such as name, email address, exam attempts, answers, and scores to provide
            the learning and exam experience.
        </p>
        <p>
            We may also store academic information connected to exams, such as class, subject, chapter, question data,
            selected answers, total marks, scores, and submission time. This information helps the platform show accurate
            results and helps admins manage academic progress.
        </p>

        <h2>How We Use Information</h2>
        <p>
            Account information is used to identify users, protect access to exams, and show the correct dashboard or
            profile page. Exam information is used to start exams, record answers, calculate results, and display previous
            attempts where allowed.
        </p>
        <p>
            Admin users may use stored exam and result information to review performance, manage questions, and improve
            future exams. We do not use student information for unrelated advertising purposes.
        </p>

        <h2>Login and Account Access</h2>
        <p>
            Users must log in to take exams and view their own profile or results. Guest users can browse public pages such
            as About Us, Contact Us, and this Privacy Policy, but they cannot access protected exam attempt pages without
            authentication.
        </p>

        <h2>How We Protect It</h2>
        <p>
            Access to admin areas is restricted, and student results are shown only to the account owner and authorized admins.
        </p>
        <p>
            The platform uses role-based access so students cannot open admin pages. Result pages are protected so one
            student cannot view another student's exam result. Passwords are handled through the application's authentication
            system and should never be shared with others.
        </p>

        <h2>Information Sharing</h2>
        <p>
            Bd ModelTest does not sell student information. Information may be visible to authorized administrators who
            manage exams, questions, and results for the institution or organization using the platform.
        </p>

        <h2>Data Accuracy</h2>
        <p>
            Users should keep their account information accurate. If a name, email address, or result appears incorrect,
            the user should contact the administrator responsible for managing the platform.
        </p>

        <h2>Data Retention</h2>
        <p>
            Exam attempts, scores, and related academic records may be kept as long as needed for learning, reporting, and
            administrative purposes. Admins may remove or update content when it is no longer needed.
        </p>

        <h2>User Responsibility</h2>
        <p>
            Users are responsible for keeping login details private, logging out from shared devices, and using the platform
            honestly. Sharing accounts or attempting to access another user's result is not allowed.
        </p>

        <h2>Policy Updates</h2>
        <p>
            This Privacy Policy may be updated when platform features, security practices, or institutional requirements
            change. The latest version will be available on this page.
        </p>

        <h2>Contact</h2>
        <p>
            For privacy-related questions, please contact your institution administrator.
        </p>
    </section>
@endsection
