<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->json('stats')->nullable(); // completed_tasks, uncompleted_tasks, overdue_tasks, completion_rate, focus_minutes, etc.
            $table->json('answers')->nullable(); // reflection answers
            $table->text('ai_analysis')->nullable(); // Gemini weekly analysis
            $table->json('next_week_plan')->nullable(); // suggested plan
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_reviews');
    }
};
