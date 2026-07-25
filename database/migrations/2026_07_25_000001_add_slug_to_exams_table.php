<?php

use App\Models\Exam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('title');
        });

        Exam::query()->orderBy('id')->each(function (Exam $exam) {
            $exam->forceFill([
                'slug' => static::uniqueSlug($exam->title, $exam->id),
            ])->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    private static function uniqueSlug(string $title, int $ignoreId): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'exam';
        $slug = $base;
        $suffix = 2;

        while (
            Exam::query()
                ->where('slug', $slug)
                ->where('id', '!=', $ignoreId)
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
};
