<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type', // 'expense', 'income', 'both'
        'icon',
        'color',
        'is_default',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'category_id');
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(FinancialBudget::class, 'category_id');
    }

    /**
     * Seed initial default categories for a given user if not already present.
     */
    public static function seedDefaultsForUser(int $userId): void
    {
        $existing = self::where('user_id', $userId)->count();
        if ($existing > 0) {
            return;
        }

        $defaults = [
            ['name' => 'طعام ومشروبات', 'type' => 'expense', 'icon' => 'fa-utensils', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'مواصلات ونقل', 'type' => 'expense', 'icon' => 'fa-car', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'المنزل والسكن', 'type' => 'expense', 'icon' => 'fa-house', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'فواتير والتزامات', 'type' => 'expense', 'icon' => 'fa-file-invoice-dollar', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'تعليم ودورات', 'type' => 'expense', 'icon' => 'fa-graduation-cap', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'صحة وعلاج', 'type' => 'expense', 'icon' => 'fa-heart-pulse', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'جيم ورياضة', 'type' => 'expense', 'icon' => 'fa-dumbbell', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'عمل ومشاريع', 'type' => 'both', 'icon' => 'fa-briefcase', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'ترفيه وتسوّق', 'type' => 'expense', 'icon' => 'fa-gamepad', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'راتب ودخل أساسي', 'type' => 'income', 'icon' => 'fa-money-bill-wave', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'استثمارات وأرباح', 'type' => 'income', 'icon' => 'fa-arrow-trend-up', 'color' => '#0d9488', 'is_default' => true],
            ['name' => 'أخرى', 'type' => 'both', 'icon' => 'fa-asterisk', 'color' => '#0d9488', 'is_default' => true],
        ];

        foreach ($defaults as $cat) {
            self::create(array_merge($cat, ['user_id' => $userId]));
        }
    }
}
