<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_plan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('start_time', 10); // e.g. "08:30"
            $table->string('end_time', 10);   // e.g. "09:15"
            $table->unsignedSmallInteger('duration_minutes')->default(30);
            $table->boolean('is_break')->default(false);
            $table->boolean('is_buffer')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['daily_plan_id', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_plan_items');
    }
};
