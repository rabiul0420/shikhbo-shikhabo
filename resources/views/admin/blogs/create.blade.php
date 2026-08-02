@extends('layouts.app', ['title' => 'Add Blog'])

@push('styles')
    <style>
        .blog-form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }
        @media (max-width: 980px) {
            .blog-form-grid { grid-template-columns: 1fr; }
        }
        .tox-tinymce { border-radius: 12px !important; }
    </style>
@endpush

@section('content')
    <div class="admin-layout">
        @include('admin.partials.sidebar', ['activeNav' => 'blogs.create'])

        <div class="admin-content">
            <div class="section-head" style="margin-bottom: 14px;">
                <div>
                    <span class="eyebrow">Blog</span>
                    <h2>Add Blog</h2>
                </div>
                <a class="button secondary" href="{{ route('admin.blogs.index') }}">Blog List</a>
            </div>

            <form class="stack" method="POST" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.blogs._form')
                <div class="row" style="margin-top: 16px;">
                    <button type="submit">Create blog</button>
                    <a class="button secondary" href="{{ route('admin.blogs.index') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@7.6.1/tinymce.min.js"></script>
    <script>
        document.querySelectorAll('.admin-menu-toggle').forEach((button) => {
            button.addEventListener('click', () => button.closest('.admin-menu-group')?.classList.toggle('is-open'));
        });

        if (window.tinymce) {
            tinymce.init({
                selector: 'textarea.js-blog-editor',
                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7.6.1',
                suffix: '.min',
                height: 360,
                menubar: false,
                plugins: 'lists link image table code autoresize',
                toolbar: 'undo redo | styles | bold italic underline | bullist numlist | link image table | removeformat | code',
                branding: false,
                promotion: false,
                convert_urls: false,
                setup: (editor) => {
                    editor.on('change', () => editor.save());
                },
            });
        }
    </script>
@endpush
