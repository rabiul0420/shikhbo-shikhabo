<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->string('first_prize')->nullable()->after('duration_minutes');
            $table->string('second_prize')->nullable()->after('first_prize');
            $table->string('third_prize')->nullable()->after('second_prize');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['first_prize', 'second_prize', 'third_prize']);
        });
    }
};
