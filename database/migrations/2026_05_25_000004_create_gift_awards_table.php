<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('gift_title');
            $table->string('status')->default('pending');
            $table->timestamp('given_at')->nullable();
            $table->foreignId('given_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_awards');
    }
};
