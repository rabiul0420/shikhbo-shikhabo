@extends('layouts.app', ['title' => 'Admin Users'])

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar')

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">User</span>
                        <h2>Admin User List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $users->count() }}</span>
                        <a class="button" href="{{ route('admin.users.create') }}">Add User</a>
                    </div>
                </div>

                @if ($users->isEmpty())
                    <p class="muted">No admin users found.</p>
                @else
                    <div class="table-wrap">
                        <table class="admin-data-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?: '-' }}</td>
                                        <td>
                                            @if ($user->is_super_admin)
                                                Super Admin
                                            @else
                                                <form method="POST" action="{{ route('admin.users.role.update', $user) }}" class="row">
                                                    @csrf
                                                    @method('PATCH')
                                                    <select name="admin_role" aria-label="Role for {{ $user->name }}" required>
                                                        @foreach (\App\Models\Admin::ADMIN_ROLES as $value => $label)
                                                            <option value="{{ $value }}" @selected($user->adminRole() === $value)>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit">Save role</button>
                                                </form>
                                            @endif
                                        </td>
                                        <td>{{ optional($user->created_at)->format('M d, Y') }}</td>
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
