<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $blogs = Blog::query()
            ->published()
            ->latest('published_at')
            ->paginate(9);

        return view('blog.index', compact('blogs'));
    }

    public function show(Blog $blog): View
    {
        abort_unless(
            $blog->status === 'published'
            && $blog->published_at
            && $blog->published_at->lte(now()),
            404
        );

        $related = Blog::query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('blog', 'related'));
    }
}
