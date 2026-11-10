<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('weekly_reviews') && !Schema::hasColumn('weekly_reviews', 'rating')) {
            Schema::table('weekly_reviews', function (Blueprint $table) {
                $table->unsignedTinyInteger('rating')->nullable()->after('is_completed');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('weekly_reviews') && Schema::hasColumn('weekly_reviews', 'rating')) {
            Schema::table('weekly_reviews', function (Blueprint $table) {
                $table->dropColumn('rating');
            });
        }
    }
};
