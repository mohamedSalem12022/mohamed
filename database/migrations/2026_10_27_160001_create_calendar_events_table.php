<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('personal'); // personal, meeting, focus, deadline, work
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('color', 30)->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence')->default('none'); // none, daily, weekly, monthly
            $table->timestamps();

            $table->index(['user_id', 'start_time']);
            $table->index(['user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
