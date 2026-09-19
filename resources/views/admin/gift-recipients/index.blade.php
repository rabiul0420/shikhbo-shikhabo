@extends('layouts.app', ['title' => 'Gift Recipients'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar')

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
                        <table id="gift-recipients-table" class="display admin-data-table">
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
                                        $account = $recipient->attempt->user ?? $recipient->attempt->admin;
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
                                        <td>{{ $account?->name ?? 'Deleted account' }}</td>
                                        <td>{{ $account?->phone ?? '-' }}</td>
                                        <td>{{ $account?->academicClass?->name ?? $recipient->exam->academicClass?->name ?? '-' }}</td>
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>
    <script>
        if (window.jQuery) {
            $(function () {
                $('.admin-menu-toggle').on('click', function () {
                    const $group = $(this).closest('.admin-menu-group');
                    const $submenu = $group.children('.admin-submenu');

                    $group.toggleClass('is-open');
                    $submenu.stop(true, true).slideToggle(180);
                });

                if ($.fn.DataTable && $('#gift-recipients-table').length) {
                    $('#gift-recipients-table').DataTable({
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50],
                        order: [[5, 'asc'], [1, 'asc']],
                        columnDefs: [
                            { orderable: false, searchable: false, targets: -1 },
                        ],
                    });
                }
            });
        }
    </script>
@endpush
