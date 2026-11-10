<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('calendar_event_id')->nullable()->constrained('calendar_events')->cascadeOnDelete();
            $table->string('title');
            $table->dateTime('remind_at');
            $table->string('remind_type')->default('custom'); // custom, before_10m, before_30m, before_1h, before_1d, overdue, daily_review, weekly_review
            $table->boolean('is_recurring')->default(false);
            $table->string('status')->default('pending'); // pending, sent, cancelled
            $table->dateTime('sent_at')->nullable();
            $table->string('channel')->default('in_app'); // in_app, browser, all
            $table->timestamps();

            $table->index(['user_id', 'status', 'remind_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
