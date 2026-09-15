@extends('layouts.app', ['title' => 'Schools'])

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
                        <span class="eyebrow">Schools</span>
                        <h2>School List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $schools->count() }}</span>
                        <button class="js-edit-academic" type="button" data-modal-target="add-school">Add School</button>
                    </div>
                </div>

                @if ($schools->isEmpty())
                    <p class="muted">No schools added yet.</p>
                @else
                    <div class="table-wrap">
                        <table id="schools-table" class="display admin-data-table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th>Added</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($schools as $school)
                                    <tr>
                                        <td>{{ $school->title }}</td>
                                        <td>{{ $school->address }}</td>
                                        <td>
                                            <span class="pill {{ $school->status === 'active' ? 'published' : 'draft' }}">
                                                {{ ucfirst($school->status) }}
                                            </span>
                                        </td>
                                        <td>{{ optional($school->created_at)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="table-actions">
                                                <button class="secondary-action small js-edit-academic" type="button" data-modal-target="edit-school-{{ $school->id }}">Edit</button>
                                                <form method="POST" action="{{ route('admin.schools.destroy', $school) }}" onsubmit="return confirm('Delete this school?')">
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
                @endif
            </section>

            <div class="modal-backdrop" id="add-school" aria-hidden="true">
                <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="add-school-title">
                    <div class="modal-head">
                        <div>
                            <span class="eyebrow">Add School</span>
                            <h3 id="add-school-title">New School</h3>
                        </div>
                        <button class="button secondary small js-close-modal" type="button" data-modal-close="add-school">Close</button>
                    </div>

                    <form class="stack" method="POST" action="{{ route('admin.schools.store') }}">
                        @csrf
                        <label>
                            Title
                            <input name="title" value="{{ old('title') }}" placeholder="School title">
                        </label>
                        <label>
                            Address
                            <textarea name="address" placeholder="School address">{{ old('address') }}</textarea>
                        </label>
                        <label>
                            Status
                            <select name="status">
                                <option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option>
                                <option value="active" @selected(old('status') === 'active')>Active</option>
                            </select>
                        </label>
                        <div class="modal-actions">
                            <button class="button secondary js-close-modal" type="button" data-modal-close="add-school">Cancel</button>
                            <button type="submit">Add school</button>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($schools as $school)
                <div class="modal-backdrop" id="edit-school-{{ $school->id }}" aria-hidden="true">
                    <div class="modal-panel" role="dialog" aria-modal="true" aria-labelledby="edit-school-{{ $school->id }}-title">
                        <div class="modal-head">
                            <div>
                                <span class="eyebrow">Edit School</span>
                                <h3 id="edit-school-{{ $school->id }}-title">{{ $school->title }}</h3>
                            </div>
                            <button class="button secondary small js-close-modal" type="button" data-modal-close="edit-school-{{ $school->id }}">Close</button>
                        </div>

                        <form class="stack" method="POST" action="{{ route('admin.schools.update', $school) }}">
                            @csrf
                            @method('PATCH')
                            <label>
                                Title
                                <input name="title" value="{{ old('title', $school->title) }}" placeholder="School title">
                            </label>
                            <label>
                                Address
                                <textarea name="address" placeholder="School address">{{ old('address', $school->address) }}</textarea>
                            </label>
                            <label>
                                Status
                                <select name="status">
                                    <option value="pending" @selected(old('status', $school->status) === 'pending')>Pending</option>
                                    <option value="active" @selected(old('status', $school->status) === 'active')>Active</option>
                                </select>
                            </label>
                            <div class="modal-actions">
                                <button class="button secondary js-close-modal" type="button" data-modal-close="edit-school-{{ $school->id }}">Cancel</button>
                                <button type="submit">Update school</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@include('admin.academic.partials.scripts', ['tableId' => 'schools-table'])
