@extends('layouts.app', ['title' => 'Class'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.academic.partials.sidebar', ['activeAcademic' => 'classes'])

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Academic Setup</span>
                        <h2>Class</h2>
                    </div>
                    <button class="js-edit-academic" type="button" data-modal-target="add-class">Add Class</button>
                </div>
            </section>

            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">List</span>
                        <h2>Class List</h2>
                    </div>
                </div>

                <div class="table-wrap">
                    <table id="classes-table" class="display admin-data-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($classes as $class)
                                <tr>
                                    <td>{{ $class->name }}</td>
                                    <td>
                                        <div class="table-actions">
                                            <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-class-{{ $class->id }}">Edit</button>
                                            <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" onsubmit="return confirm('Delete this class?')">
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

            <div class="modal-backdrop" id="add-class" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="add-class-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Add Class</span>
                            <h3 id="add-class-title">New Class</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="add-class">Close</button>
                    </div>

                    <form class="stack" method="POST" action="{{ route('admin.classes.store') }}">
                        @csrf
                        <label>
                            Class name
                            <input name="name" placeholder="Class 9">
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-class">Cancel</button>
                            <button type="submit">Add class</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($classes as $class)
                <div class="modal-backdrop" id="edit-class-{{ $class->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-class-{{ $class->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit Class</span>
                                <h3 id="edit-class-{{ $class->id }}-title">{{ $class->name }}</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-class-{{ $class->id }}">Close</button>
                        </div>

                        <form class="stack" method="POST" action="{{ route('admin.classes.update', $class) }}">
                            @csrf
                            @method('PATCH')
                            <label>
                                Class name
                                <input name="name" value="{{ $class->name }}">
                            </label>
                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-class-{{ $class->id }}">Cancel</button>
                                <button type="submit">Update class</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@include('admin.academic.partials.scripts', ['tableId' => 'classes-table'])
