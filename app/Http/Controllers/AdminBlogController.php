<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminBlogController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::query()
            ->latest('published_at')
            ->latest()
            ->get();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function create(): View
    {
        return view('admin.blogs.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('hero_image')) {
            $data['hero_image'] = $this->storeHeroImage($request);
        }

        $blog = Blog::create($data);

        return redirect()
            ->route('admin.blogs.edit', $blog)
            ->with('status', 'Blog post created.');
    }

    public function edit(Blog $blog): View
    {
        return view('admin.blogs.edit', compact('blog'));
    }

    public function update(Request $request, Blog $blog): RedirectResponse
    {
        $data = $this->validated($request, $blog);

        if ($request->boolean('remove_hero_image') && $blog->hero_image) {
            $this->deleteHeroImage($blog->hero_image);
            $data['hero_image'] = null;
        }

        if ($request->hasFile('hero_image')) {
            if ($blog->hero_image) {
                $this->deleteHeroImage($blog->hero_image);
            }
            $data['hero_image'] = $this->storeHeroImage($request);
        }

        $blog->update($data);

        return redirect()
            ->route('admin.blogs.edit', $blog)
            ->with('status', 'Blog post updated.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        if ($blog->hero_image) {
            $this->deleteHeroImage($blog->hero_image);
        }

        $blog->delete();

        return redirect()
            ->route('admin.blogs.index')
            ->with('status', 'Blog post deleted.');
    }

    private function validated(Request $request, ?Blog $blog = null): array
    {
        $data = $request->validate([
            'title_en' => ['required', 'string', 'max:255'],
            'title_bn' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('blogs', 'slug')->ignore($blog?->id),
            ],
            'excerpt_en' => ['nullable', 'string', 'max:2000'],
            'excerpt_bn' => ['nullable', 'string', 'max:2000'],
            'body_en' => ['required', 'string'],
            'body_bn' => ['required', 'string'],
            'meta_title_en' => ['nullable', 'string', 'max:255'],
            'meta_title_bn' => ['nullable', 'string', 'max:255'],
            'meta_description_en' => ['nullable', 'string', 'max:500'],
            'meta_description_bn' => ['nullable', 'string', 'max:500'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_hero_image' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(Blog::STATUSES)],
            'published_at' => ['nullable', 'date'],
        ]);

        if (blank($data['slug'] ?? null)) {
            unset($data['slug']);
        }

        if (($data['status'] ?? null) === 'draft') {
            $data['published_at'] = $data['published_at'] ?? null;
        } elseif (blank($data['published_at'] ?? null)) {
            $data['published_at'] = now();
        }

        unset($data['hero_image'], $data['remove_hero_image']);

        return $data;
    }

    private function storeHeroImage(Request $request): string
    {
        $file = $request->file('hero_image');
        $directory = $this->webRootPath('uploads/blog');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $file->move($directory, $filename);

        return 'uploads/blog/'.$filename;
    }

    private function deleteHeroImage(string $path): void
    {
        if (! str_starts_with($path, 'uploads/blog/')) {
            return;
        }

        $fullPath = $this->webRootPath($path);

        if (is_file($fullPath)) {
            unlink($fullPath);
        }
    }

    private function webRootPath(string $path = ''): string
    {
        $documentRoot = request()->server('DOCUMENT_ROOT');
        $root = is_string($documentRoot) && is_dir($documentRoot)
            ? $documentRoot
            : public_path();

        return rtrim($root, DIRECTORY_SEPARATOR).($path ? DIRECTORY_SEPARATOR.str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path) : '');
    }
}
