<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schools') || ! Schema::hasColumn('schools', 'singleton_key')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['singleton_key']);
            $table->dropColumn('singleton_key');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('schools') || Schema::hasColumn('schools', 'singleton_key')) {
            return;
        }

        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedTinyInteger('singleton_key')->nullable()->unique()->after('id');
        });
    }
};
