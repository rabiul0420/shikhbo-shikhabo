@php
    /** @var \App\Models\Blog|null $blog */
    $blog = $blog ?? null;
@endphp

<div class="blog-form-grid">
    <section class="panel">
        <div class="section-head">
            <div>
                <span class="eyebrow">English</span>
                <h2>English content</h2>
            </div>
        </div>
        <div class="stack">
            <label>
                Title (EN)
                <input name="title_en" value="{{ old('title_en', $blog?->title_en) }}" required>
            </label>
            <label>
                Excerpt (EN)
                <textarea name="excerpt_en" rows="3">{{ old('excerpt_en', $blog?->excerpt_en) }}</textarea>
            </label>
            <label>
                Details (EN)
                <textarea class="js-blog-editor" name="body_en" rows="12">{{ old('body_en', $blog?->body_en) }}</textarea>
            </label>
            <label>
                Meta title (EN)
                <input name="meta_title_en" value="{{ old('meta_title_en', $blog?->meta_title_en) }}">
            </label>
            <label>
                Meta description (EN)
                <textarea name="meta_description_en" rows="3">{{ old('meta_description_en', $blog?->meta_description_en) }}</textarea>
            </label>
        </div>
    </section>

    <section class="panel">
        <div class="section-head">
            <div>
                <span class="eyebrow">বাংলা</span>
                <h2>Bangla content</h2>
            </div>
        </div>
        <div class="stack">
            <label>
                Title (BN)
                <input name="title_bn" value="{{ old('title_bn', $blog?->title_bn) }}" required lang="bn">
            </label>
            <label>
                Excerpt (BN)
                <textarea name="excerpt_bn" rows="3" lang="bn">{{ old('excerpt_bn', $blog?->excerpt_bn) }}</textarea>
            </label>
            <label>
                Details (BN)
                <textarea class="js-blog-editor" name="body_bn" rows="12" lang="bn">{{ old('body_bn', $blog?->body_bn) }}</textarea>
            </label>
            <label>
                Meta title (BN)
                <input name="meta_title_bn" value="{{ old('meta_title_bn', $blog?->meta_title_bn) }}" lang="bn">
            </label>
            <label>
                Meta description (BN)
                <textarea name="meta_description_bn" rows="3" lang="bn">{{ old('meta_description_bn', $blog?->meta_description_bn) }}</textarea>
            </label>
        </div>
    </section>
</div>

<section class="panel" style="margin-top: 18px;">
    <div class="section-head">
        <div>
            <span class="eyebrow">Settings</span>
            <h2>Publish & media</h2>
        </div>
    </div>
    <div class="stack">
        <label>
            Slug
            <input name="slug" value="{{ old('slug', $blog?->slug) }}" placeholder="auto-from-english-title">
            <span class="muted" style="font-size: 0.9rem;">Leave blank to auto-generate. Same slug for `/blog/...` and `/bn/blog/...`.</span>
        </label>
        <label>
            Status
            <select name="status" required>
                @foreach (\App\Models\Blog::STATUSES as $status)
                    <option value="{{ $status }}" @selected(old('status', $blog?->status ?? 'draft') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>
        <label>
            Published at
            <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($blog?->published_at)->format('Y-m-d\\TH:i')) }}">
        </label>
        <label>
            Hero image
            <input type="file" name="hero_image" accept="image/jpeg,image/png,image/webp">
        </label>
        @if ($blog?->hero_image)
            <div class="row" style="align-items: center; gap: 12px;">
                <img src="{{ asset($blog->hero_image) }}" alt="" style="width: 160px; height: 90px; object-fit: cover; border-radius: 10px;">
                <label style="display: flex; align-items: center; gap: 8px; margin: 0;">
                    <input type="checkbox" name="remove_hero_image" value="1" @checked(old('remove_hero_image'))>
                    Remove current image
                </label>
            </div>
        @endif
    </div>
</section>
