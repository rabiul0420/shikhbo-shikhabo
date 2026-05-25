@extends('layouts.app', ['title' => 'Gift Recipients'])

@section('content')
    <div class="admin-layout">
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

                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.students.index') }}">Student List</a>
                <a class="admin-nav-link admin-menu-direct" href="{{ route('admin.results.index') }}">Result</a>
                <a class="admin-nav-link admin-menu-direct is-active" href="{{ route('admin.gift-recipients.index') }}">Gift List</a>
            </nav>
        </aside>

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Gift</span>
                        <h2>Gift Recipient List</h2>
                    </div>
                    <span class="count-badge">{{ $giftRecipients->count() }}</span>
                </div>

                @if ($giftRecipients->isEmpty())
                    <p class="muted">No gift recipient found yet.</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Status</th>
                                    <th>Position</th>
                                    <th>Student</th>
                                    <th>Phone</th>
                                    <th>Class</th>
                                    <th>Exam</th>
                                    <th>Gift</th>
                                    <th>Score</th>
                                    <th>Submitted</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($giftRecipients as $recipient)
                                    @php
                                        $isGiven = optional($recipient->award)->status === 'given';
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="pill {{ $isGiven ? 'published' : 'draft' }}">
                                                {{ $isGiven ? 'Given' : 'Pending' }}
                                            </span>
                                            @if ($isGiven)
                                                <br>
                                                <span class="muted">{{ optional($recipient->award->given_at)->format('M d, Y h:i A') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $recipient->position }}</td>
                                        <td>{{ $recipient->attempt->user->name }}</td>
                                        <td>{{ $recipient->attempt->user->phone ?? '-' }}</td>
                                        <td>{{ $recipient->attempt->user->academicClass->name ?? $recipient->exam->academicClass->name }}</td>
                                        <td>{{ $recipient->exam->title }}</td>
                                        <td><strong>{{ $recipient->gift_title }}</strong></td>
                                        <td>{{ $recipient->attempt->score }} / {{ $recipient->attempt->total_marks }}</td>
                                        <td>{{ optional($recipient->attempt->submitted_at)->format('M d, Y h:i A') }}</td>
                                        <td>
                                            @if ($isGiven)
                                                <span class="muted">Completed</span>
                                            @else
                                                <form method="POST" action="{{ route('admin.gift-recipients.given', $recipient->attempt) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="small" type="submit">Mark given</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        if (window.jQuery) {
            $(function () {
                $('.admin-menu-toggle').on('click', function () {
                    const $group = $(this).closest('.admin-menu-group');
                    const $submenu = $group.children('.admin-submenu');

                    $group.toggleClass('is-open');
                    $submenu.stop(true, true).slideToggle(180);
                });
            });
        }
    </script>
@endpush
