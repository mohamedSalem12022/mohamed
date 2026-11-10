<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('daily_plan_items')) {
            Schema::table('daily_plan_items', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_plan_items', 'is_completed')) {
                    $table->boolean('is_completed')->default(false)->after('is_locked');
                }
                if (!Schema::hasColumn('daily_plan_items', 'type')) {
                    $table->string('type', 30)->default('task')->after('duration_minutes');
                }
                if (!Schema::hasColumn('daily_plan_items', 'priority')) {
                    $table->string('priority', 20)->default('medium')->after('type');
                }
                if (!Schema::hasColumn('daily_plan_items', 'notes')) {
                    $table->text('notes')->nullable()->after('priority');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('daily_plan_items')) {
            Schema::table('daily_plan_items', function (Blueprint $table) {
                $columns = ['is_completed', 'type', 'priority', 'notes'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('daily_plan_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
