<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    public const STATUSES = ['draft', 'published'];

    protected $fillable = [
        'slug',
        'title_en',
        'title_bn',
        'excerpt_en',
        'excerpt_bn',
        'body_en',
        'body_bn',
        'meta_title_en',
        'meta_title_bn',
        'meta_description_en',
        'meta_description_bn',
        'hero_image',
        'custom_css',
        'json_schema',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Blog $blog) {
            if (blank($blog->slug)) {
                $blog->slug = static::uniqueSlugFor($blog->title_en ?: $blog->title_bn, $blog->id);
            }

            if ($blog->status === 'published' && blank($blog->published_at)) {
                $blog->published_at = now();
            }
        });
    }

    public static function uniqueSlugFor(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'blog';
        $slug = $base;
        $suffix = 2;

        while (
            static::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function title(?string $locale = null): string
    {
        return $this->localized('title', $locale);
    }

    public function excerpt(?string $locale = null): ?string
    {
        return $this->localized('excerpt', $locale) ?: null;
    }

    public function body(?string $locale = null): string
    {
        return $this->localized('body', $locale);
    }

    public function metaTitle(?string $locale = null): string
    {
        $meta = $this->localized('meta_title', $locale);

        return $meta !== '' ? $meta : $this->title($locale);
    }

    public function sanitizedCustomCss(): ?string
    {
        if (blank($this->custom_css)) {
            return null;
        }

        $css = (string) $this->custom_css;
        $css = preg_replace('/<\/style/i', '', $css) ?? $css;
        $css = preg_replace('/<script/i', '', $css) ?? $css;
        $css = trim($css);

        return $css !== '' ? $css : null;
    }

    public function decodedJsonSchema(): ?array
    {
        if (blank($this->json_schema)) {
            return null;
        }

        $decoded = json_decode($this->json_schema, true);

        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        if (array_is_list($decoded)) {
            return [
                '@context' => 'https://schema.org',
                '@graph' => $decoded,
            ];
        }

        if (! isset($decoded['@context'])) {
            $decoded = ['@context' => 'https://schema.org'] + $decoded;
        }

        return $decoded;
    }

    public function metaDescription(?string $locale = null): string
    {
        $meta = $this->localized('meta_description', $locale);

        if ($meta !== '') {
            return $meta;
        }

        $excerpt = $this->excerpt($locale);

        return $excerpt ? Str::limit(strip_tags($excerpt), 160) : Str::limit(strip_tags($this->body($locale)), 160);
    }

    protected function localized(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();
        $preferred = $field.'_'.($locale === 'bn' ? 'bn' : 'en');
        $fallback = $field.'_'.($locale === 'bn' ? 'en' : 'bn');

        $value = trim((string) ($this->{$preferred} ?? ''));

        if ($value !== '') {
            return $value;
        }

        return trim((string) ($this->{$fallback} ?? ''));
    }
}
