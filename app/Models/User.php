<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }

    public function settings(): HasOne
    {
        return $this->hasOne(UserSettings::class);
    }

    public function habits(): HasMany
    {
        return $this->hasMany(Habit::class)->orderBy('order', 'asc')->orderBy('id', 'asc');
    }

    public function habitLogs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function dailyReflections(): HasMany
    {
        return $this->hasMany(DailyReflection::class);
    }

    public function excuseLogs(): HasMany
    {
        return $this->hasMany(ExcuseLog::class)->orderBy('date', 'desc');
    }

    public function aiMessages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at', 'asc');
    }

    public function dailyPlans(): HasMany
    {
        return $this->hasMany(DailyPlan::class)->orderBy('plan_date', 'desc');
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class)->orderBy('start_time', 'asc');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class)->orderBy('remind_at', 'asc');
    }

    public function inAppNotifications(): HasMany
    {
        return $this->hasMany(InAppNotification::class)->orderBy('created_at', 'desc');
    }

    public function weeklyReviews(): HasMany
    {
        return $this->hasMany(WeeklyReview::class)->orderBy('week_start_date', 'desc');
    }

    public function motivations(): HasMany
    {
        return $this->hasMany(Motivation::class)->orderByDesc('is_pinned')->latest();
    }

    public function financialCategories(): HasMany
    {
        return $this->hasMany(FinancialCategory::class)->orderBy('name', 'asc');
    }

    public function financialTransactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    public function financialBudgets(): HasMany
    {
        return $this->hasMany(FinancialBudget::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class)->orderBy('created_at', 'desc');
    }
}
