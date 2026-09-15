@extends('layouts.app', ['title' => 'Subject'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
    <style>
        .class-picker {
            display: grid;
            gap: 10px;
            padding: 12px;
            border: 1px solid #d8dee6;
            background: #f8fafc;
        }

        .class-picker legend {
            padding: 0 6px;
            color: #343a40;
            font-size: 13px;
            font-weight: 800;
        }

        .class-picker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(148px, 1fr));
            gap: 8px;
        }

        .class-choice {
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            min-height: 42px;
            padding: 9px 10px;
            border: 1px solid #d8dee6;
            background: #ffffff;
            color: #1f2d3d;
            font-weight: 700;
            cursor: pointer;
            transition: border-color .14s ease, background .14s ease, box-shadow .14s ease;
        }

        .class-choice:hover {
            border-color: #93c5fd;
            background: #f0f7ff;
        }

        .class-choice input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: #2563eb;
        }

        .class-choice:has(input:checked) {
            border-color: #2563eb;
            background: #eff6ff;
            box-shadow: inset 3px 0 0 #2563eb;
        }

        .class-picker-note {
            color: #6c757d;
            font-size: 12px;
            font-weight: 700;
        }
    </style>
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
                                <th>Class</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->name }}</td>
                                    <td>{{ $subject->academicClasses->pluck('name')->implode(', ') ?: 'Unassigned' }}</td>
                                    <td>
                                        @if ($subject->canBeManagedBy(auth()->user()))
<div class="table-actions">
                                            <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-subject-{{ $subject->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" onsubmit="return confirm('Delete this subject?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="danger small" type="submit">Delete</button>
                                            </form>
                                        </div>
@else
<span class="muted">Read only</span>
@endif
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
                            <input name="name" value="{{ old('name') }}" placeholder="Mathematics">
                        </label>
                        <fieldset class="class-picker" data-class-picker>
                            <legend>Classes</legend>
                            <div class="class-picker-grid">
                                @foreach ($classes as $class)
                                    <label class="class-choice">
                                        <input
                                            type="checkbox"
                                            name="academic_class_ids[]"
                                            value="{{ $class->id }}"
                                            @checked(in_array((string) $class->id, old('academic_class_ids', []), true))
                                        >
                                        <span>{{ $class->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                            <span class="class-picker-note" data-class-picker-note>0 selected</span>
                        </fieldset>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-subject">Cancel</button>
                            <button type="submit">Add subject</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($subjects as $subject)
                @continue(! $subject->canBeManagedBy(auth()->user()))
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
                            @php
                                $selectedClassIds = old(
                                    'academic_class_ids',
                                    $subject->academicClasses->pluck('id')->map(fn ($id) => (string) $id)->all()
                                );
                            @endphp
                            <label>
                                Subject name
                                <input name="name" value="{{ old('name', $subject->name) }}">
                            </label>
                            <fieldset class="class-picker" data-class-picker>
                                <legend>Classes</legend>
                                <div class="class-picker-grid">
                                    @foreach ($classes as $class)
                                        <label class="class-choice">
                                            <input
                                                type="checkbox"
                                                name="academic_class_ids[]"
                                                value="{{ $class->id }}"
                                                @checked(in_array((string) $class->id, $selectedClassIds, true))
                                            >
                                            <span>{{ $class->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                                <span class="class-picker-note" data-class-picker-note>0 selected</span>
                            </fieldset>
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

@push('scripts')
    <script>
        document.querySelectorAll('[data-class-picker]').forEach((picker) => {
            const note = picker.querySelector('[data-class-picker-note]');
            const checkboxes = Array.from(picker.querySelectorAll('input[type="checkbox"]'));

            const updateNote = () => {
                const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
                note.textContent = `${selectedCount} ${selectedCount === 1 ? 'class' : 'classes'} selected`;
            };

            checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateNote));
            updateNote();
        });
    </script>
@endpush
