@extends('layouts.app', ['title' => 'Oddhay / Chapter'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
    <style>
        .table-filters { display: flex; gap: 14px; align-items: flex-end; flex-wrap: wrap; margin-bottom: 14px; }
        .table-filter-field { min-width: 190px; }
        .table-filter-field select { width: 100%; min-height: 38px; padding: 6px 10px; }
    </style>
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

                <div class="table-filters">
                    <label class="table-filter-field">
                        Class
                        <select id="filter-chapters-class">
                            <option value="">All classes</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" data-class-name="{{ $class->name }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="table-filter-field">
                        Subject
                        <select id="filter-chapters-subject">
                            <option value="">All subjects</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->name }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </label>
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
                                        @if ($chapter->canBeManagedBy(auth()->user()))
<div class="table-actions">
                                            <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-chapter-{{ $chapter->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.chapters.destroy', $chapter) }}" onsubmit="return confirm('Delete this oddhay / chapter?')">
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
                            <select name="academic_class_id" data-class-subject-filter>
                                <option value="">Select class</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            Subject
                            <select name="subject_id" data-subject-options>
                                <option value="">Select subject</option>
                                @foreach ($subjects as $subject)
                                    <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}">{{ $subject->name }}</option>
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
                @continue(! $chapter->canBeManagedBy(auth()->user()))
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
                                <select name="academic_class_id" data-class-subject-filter>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}" @selected((int) $chapter->academic_class_id === (int) $class->id)>{{ $class->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                Subject
                                <select name="subject_id" data-subject-options>
                                    @foreach ($subjects as $subject)
                                        <option value="{{ $subject->id }}" data-class-ids="{{ $subject->academicClasses->pluck('id')->implode(',') }}" @selected((int) $chapter->subject_id === (int) $subject->id)>{{ $subject->name }}</option>
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

@push('scripts')
    <script>
        document.querySelectorAll('[data-class-subject-filter]').forEach((classSelect) => {
            const form = classSelect.closest('form');
            const subjectSelect = form?.querySelector('[data-subject-options]');

            if (!subjectSelect) {
                return;
            }

            const filterSubjects = () => {
                const selectedClass = classSelect.value;
                let selectedOptionVisible = false;

                subjectSelect.querySelectorAll('option').forEach((option) => {
                    if (!option.value) {
                        option.hidden = false;
                        return;
                    }

                    const optionClasses = (option.dataset.classIds || '').split(',').filter(Boolean);
                    const isVisible = optionClasses.includes(selectedClass);
                    option.hidden = !isVisible;

                    if (option.selected && isVisible) {
                        selectedOptionVisible = true;
                    }
                });

                if (!selectedOptionVisible) {
                    subjectSelect.value = '';
                }
            };

            classSelect.addEventListener('change', filterSubjects);
            filterSubjects();
        });
    </script>

    <script>
        $(function () {
            if (!$.fn.DataTable || !$('#chapters-table').length) {
                return;
            }

            const $table = $('#chapters-table').DataTable();
            const $classFilter = $('#filter-chapters-class');
            const $subjectFilter = $('#filter-chapters-subject');

            const filterSubjects = () => {
                const selectedClass = String($classFilter.val());
                let selectedVisible = false;

                $subjectFilter.find('option').each(function () {
                    const $option = $(this);
                    if (!$option.val()) {
                        $option.prop('hidden', false);
                        return;
                    }

                    const optionClassIds = ($option.attr('data-class-ids') || '').split(',').filter(Boolean);
                    const visible = !selectedClass || optionClassIds.includes(selectedClass);
                    $option.prop('hidden', !visible);

                    if ($option.is(':selected') && visible) {
                        selectedVisible = true;
                    }
                });

                if (!selectedVisible) {
                    $subjectFilter.val('');
                }
            };

            const applyFilters = () => {
                const className = $classFilter.find(':selected').attr('data-class-name') || '';
                $table.column(0).search(className).draw();
                $table.column(1).search($subjectFilter.val()).draw();
            };

            $classFilter.on('change', () => {
                filterSubjects();
                applyFilters();
            });
            $subjectFilter.on('change', applyFilters);

            filterSubjects();
        });
    </script>
@endpush
