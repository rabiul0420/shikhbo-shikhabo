<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['academic_classes', 'subjects', 'chapters'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (['academic_classes', 'subjects', 'chapters'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('created_by');
            });
        }
    }
};
