@extends('layouts.app', [
    'title' => 'About Bd ModelTest',
    'description' => 'Learn about Bd ModelTest, an online learning and exam practice platform for students, teachers, coaching centers, and admins.',
    'canonical' => route('about-us'),
])

@section('content')
    <div class="page-head">
        <div>
            <h1>About Us</h1>
            <p class="muted">Learn more about Bd ModelTest.</p>
        </div>
    </div>

    <section class="panel content-panel">
        <h2>Bd ModelTest</h2>
        <p>
            Bd ModelTest is an online learning and exam practice platform built to help students prepare chapter-wise,
            subject-wise, and class-wise.
        </p>
        <p>
            Our goal is to make exam practice simple for students and easy for teachers, coaching centers, and admins to
            manage. Students can log in, choose an exam, submit answers, and view results quickly. Admin users can organize
            classes, subjects, chapters, questions, exams, and results from one focused dashboard.
        </p>

        <h2>Our Mission</h2>
        <p>
            We believe regular practice helps students build confidence. Bd ModelTest is designed to make that practice
            easier by keeping learning materials organized and making exams available whenever students are ready to attempt
            them.
        </p>
        <p>
            The platform supports a structured academic flow: first class, then subject, then chapter, then questions and
            exams. This helps students prepare in a targeted way instead of searching through scattered materials.
        </p>

        <h2>For Students</h2>
        <p>
            Students can browse available exams, start an exam after login, answer questions, submit their attempt, and see
            their score. The experience is kept clean and direct so students can focus on learning instead of complicated
            navigation.
        </p>

        <h2>For Admins and Teachers</h2>
        <p>
            Admin users can create the academic structure, add questions, build exams, and review submitted results. This
            makes it easier to maintain question banks and monitor student performance without relying on manual paper-based
            processes.
        </p>

        <h2>Why It Matters</h2>
        <p>
            Good exam preparation is not only about memorizing answers. It is about repeated practice, understanding weak
            areas, and improving step by step. Bd ModelTest aims to support that habit with a simple digital system for
            practice and assessment.
        </p>

        <h2>Our Commitment</h2>
        <p>
            We are committed to keeping the platform practical, accessible, and useful for everyday learning. As the project
            grows, the focus will remain the same: helping students learn better and helping educators manage exams more
            efficiently.
        </p>
    </section>
@endsection
