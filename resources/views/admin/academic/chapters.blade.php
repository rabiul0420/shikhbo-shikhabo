@extends('layouts.app', ['title' => 'Oddhay / Chapter'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.academic.partials.sidebar', ['activeAcademic' => 'chapters'])

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Academic Setup</span>
                        <h2>Oddhay / Chapter</h2>
                    </div>
                    <button class="js-edit-academic" type="button" data-modal-target="add-chapter">Add Chapter</button>
                </div>
            </section>

            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">List</span>
                        <h2>Oddhay / Chapter List</h2>
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="chapters-table" class="display admin-data-table">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Chapter No</th>
                                <th>Chapter Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($chapters as $chapter)
                                <tr>
                                    <td>{{ $chapter->academicClass->name }}</td>
                                    <td>{{ $chapter->subject->name }}</td>
                                    <td>{{ $chapter->chapter_no ?: '-' }}</td>
                                    <td>{{ $chapter->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-chapter-{{ $chapter->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.chapters.destroy', $chapter) }}" onsubmit="return confirm('Delete this oddhay / chapter?')">
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

            <div class="modal-backdrop" id="add-chapter" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="add-chapter-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Add Oddhay / Chapter</span>
                            <h3 id="add-chapter-title">New Oddhay / Chapter</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="add-chapter">Close</button>
                    </div>

                    <form class="grid grid-2" method="POST" action="{{ route('admin.chapters.store') }}">
                        @csrf
                        <label>
                            Class
                            <select name="academic_class_id">
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subject
                            <select name="subject_id">
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Chapter No
                            <input name="chapter_no" placeholder="Lesson 1">
                        </label>
                        <label>
                            Chapter Name
                            <input name="name" placeholder="Introduction">
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-chapter">Cancel</button>
                            <button type="submit">Add chapter</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($chapters as $chapter)
                <div class="modal-backdrop" id="edit-chapter-{{ $chapter->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-chapter-{{ $chapter->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit Oddhay / Chapter</span>
                                <h3 id="edit-chapter-{{ $chapter->id }}-title">{{ $chapter->display_name }}</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-chapter-{{ $chapter->id }}">Close</button>
                        </div>

                        <form class="stack" method="POST" action="{{ route('admin.chapters.update', $chapter) }}">
                            @csrf
                            @method('PATCH')
                            <label>
                                Class
                                <select name="academic_class_id">
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected((int) $chapter->academic_class_id === (int) $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Subject
                                <select name="subject_id">
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" @selected((int) $chapter->subject_id === (int) $subject->id)>{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Chapter No
                                <input name="chapter_no" value="{{ $chapter->chapter_no }}">
                            </label>
                            <label>
                                Chapter Name
                                <input name="name" value="{{ $chapter->name }}">
                            </label>
                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-chapter-{{ $chapter->id }}">Cancel</button>
                                <button type="submit">Update chapter</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@include('admin.academic.partials.scripts', ['tableId' => 'chapters-table'])
