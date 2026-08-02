@extends('layouts.app', ['title' => 'Blogs'])

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.min.css">
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar', ['activeNav' => 'blogs.index'])

        <div class="admin-content">
            <section class="panel">
                <div class="section-head">
                    <div>
                        <span class="eyebrow">Blog</span>
                        <h2>Blog List</h2>
                    </div>
                    <div class="row">
                        <span class="count-badge">{{ $blogs->count() }}</span>
                        <a class="button" href="{{ route('admin.blogs.create') }}">Add Blog</a>
                    </div>
                </div>

                @if ($blogs->isEmpty())
                    <p class="muted">No blog posts yet. <a href="{{ route('admin.blogs.create') }}">Create the first one</a>.</p>
                @else
                    <div class="table-wrap">
                        <table class="data-table" id="blogs-table">
                            <thead>
                                <tr>
                                    <th>Title (EN)</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Published</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($blogs as $blog)
                                    <tr>
                                        <td>{{ $blog->title_en }}</td>
                                        <td><code>{{ $blog->slug }}</code></td>
                                        <td>
                                            <span class="pill {{ $blog->status === 'published' ? 'published' : '' }}">
                                                {{ ucfirst($blog->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $blog->published_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                        <td>
                                            <div class="row">
                                                @if ($blog->status === 'published')
                                                    <a class="button secondary small" href="{{ localized_route('blog.show', [$blog], 'en') }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                <a class="button secondary small" href="{{ route('admin.blogs.edit', $blog) }}">Edit</a>
                                                <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this blog post?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="button secondary small" type="submit">Delete</button>
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
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>
    <script>
        document.querySelectorAll('.admin-menu-toggle').forEach((button) => {
            button.addEventListener('click', () => button.closest('.admin-menu-group')?.classList.toggle('is-open'));
        });
        if (document.getElementById('blogs-table')) {
            new DataTable('#blogs-table', { pageLength: 25, order: [] });
        }
    </script>
@endpush
