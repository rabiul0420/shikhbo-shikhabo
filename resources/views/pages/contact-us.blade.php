@extends('layouts.app', [
    'title' => 'Contact Shikhbo Shikhabo',
    'description' => 'Contact Shikhbo Shikhabo for student exam support, account help, admin assistance, and online exam platform setup questions.',
    'canonical' => route('contact-us'),
])

@push('styles')
    <style>
        .contact-grid {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(320px, 1.1fr);
            gap: 18px;
            align-items: start;
        }

        .contact-card-list {
            display: grid;
            gap: 12px;
            margin-top: 16px;
        }

        .contact-card {
            padding: 14px;
            border: 1px solid #dfe5ec;
            background: #fbfcfd;
        }

        .contact-card span {
            display: block;
            color: #6c757d;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .contact-card a,
        .contact-card strong {
            color: #1f2d3d;
            font-size: 18px;
        }

        .contact-map {
            overflow: hidden;
            border: 1px solid #dfe5ec;
            background: #ffffff;
        }

        .contact-map iframe {
            display: block;
            width: 100%;
            min-height: 390px;
            border: 0;
        }

        .contact-note {
            padding: 12px 14px;
            border-top: 1px solid #dfe5ec;
            background: #f8fafc;
            margin: 0;
            font-size: 13px;
        }

        @media (max-width: 860px) {
            .contact-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section('content')
    <div class="page-head">
        <div>
            <h1>Contact Us</h1>
            <p class="muted">Reach out to the Shikhbo Shikhabo team for learning, exam, account, or admin support.</p>
        </div>
    </div>

    <div class="contact-grid">
        <section class="panel">
            <h2>Get In Touch</h2>
            <p>
                If you need help with exam access, result checking, question setup, class-wise exam management, or any
                technical issue, you can contact us directly. We try to respond as soon as possible.
            </p>

            <div class="contact-card-list">
                <div class="contact-card">
                    <span>Email</span>
                    <a href="mailto:rabiul0420@gmail.com">rabiul0420@gmail.com</a>
                </div>
                <div class="contact-card">
                    <span>Phone</span>
                    <a href="tel:+8801833683530">01833683530</a>
                </div>
                <div class="contact-card">
                    <span>Support Area</span>
                    <strong>Student exam support, admin help, and platform setup</strong>
                </div>
            </div>

            <h2>When To Contact</h2>
            <p>
                Contact us if you cannot log in, cannot start an exam, cannot see your result, find incorrect exam
                information, or need help creating class, subject, chapter, question, or exam data.
            </p>

            <h2>Response Time</h2>
            <p>
                We normally review messages during regular working hours. For urgent exam-related issues, calling the
                phone number is the fastest option.
            </p>
        </section>

        <section class="contact-map" aria-label="Google map location">
            <iframe
                title="Shikhbo Shikhabo location on Google Maps"
                loading="lazy"
                allowfullscreen
                referrerpolicy="no-referrer-when-downgrade"
                src="https://www.google.com/maps?q=Bangladesh&output=embed"
            ></iframe>
            <p class="contact-note muted">
                Map location is currently set by search. Share your exact address if you want this pinned to a specific place.
            </p>
        </section>
    </div>
@endsection
