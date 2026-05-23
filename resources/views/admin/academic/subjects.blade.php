@extends('layouts.app', ['title' => 'Subject'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.academic.partials.sidebar', ['activeAcademic' => 'subjects'])

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Academic Setup</span>
                        <h2>Subject</h2>
                    </div>
                    <button class="js-edit-academic" type="button" data-modal-target="add-subject">Add Subject</button>
                </div>
            </section>

            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">List</span>
                        <h2>Subject List</h2>
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="subjects-table" class="display admin-data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-subject-{{ $subject->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="danger small" type="submit">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div class="modal-backdrop" id="add-subject" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="add-subject-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Add Subject</span>
                            <h3 id="add-subject-title">New Subject</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="add-subject">Close</button>
                    </div>

                    <form class="stack" method="POST" action="{{ route('admin.subjects.store') }}">
                        @csrf
                        <label>
                            Subject name
                            <input name="name" placeholder="Mathematics">
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-subject">Cancel</button>
                            <button type="submit">Add subject</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($subjects as $subject)
                <div class="modal-backdrop" id="edit-subject-{{ $subject->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-subject-{{ $subject->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit Subject</span>
                                <h3 id="edit-subject-{{ $subject->id }}-title">{{ $subject->name }}</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-subject-{{ $subject->id }}">Close</button>
                        </div>

                        <form class="stack" method="POST" action="{{ route('admin.subjects.update', $subject) }}">
                            @csrf
                            @method('PATCH')
                            <label>
                                Subject name
                                <input name="name" value="{{ $subject->name }}">
                            </label>
                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-subject-{{ $subject->id }}">Cancel</button>
                                <button type="submit">Update subject</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@include('admin.academic.partials.scripts', ['tableId' => 'subjects-table'])
