<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('goal')->nullable();
            $table->string('icon')->default('fa-solid fa-graduation-cap');
            $table->string('color', 20)->default('#0d9488');
            $table->string('image')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_date')->nullable();
            $table->string('status', 20)->default('in_progress'); // in_progress, completed, paused
            $table->timestamps();
        });

        Schema::create('sub_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('skill_id')->constrained('skills')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('not_started'); // not_started, in_progress, mastered
            $table->unsignedTinyInteger('mastery_level')->default(0); // 0 to 100%
            $table->integer('order')->default(0);
            $table->text('notes')->nullable();
            $table->text('resources')->nullable(); // links, courses, books
            $table->text('practical_tasks')->nullable(); // practical projects, action items
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_skills');
        Schema::dropIfExists('skills');
    }
};
