<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('chapters', 'chapter_no')) {
            return;
        }

        Schema::table('chapters', function (Blueprint $table) {
            $table->string('chapter_no')->nullable()->after('subject_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('chapters', 'chapter_no')) {
            return;
        }

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropColumn('chapter_no');
        });
    }
};
