<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add default currency to user_settings if not exists
        if (Schema::hasTable('user_settings') && !Schema::hasColumn('user_settings', 'currency')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->string('currency', 10)->default('USD')->after('accent_color');
            });
        }

        // 2. Financial Categories
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->enum('type', ['expense', 'income', 'both'])->default('expense');
            $table->string('icon', 50)->default('fa-wallet');
            $table->string('color', 20)->default('#0d9488');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });

        // 3. Financial Transactions
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('financial_categories')->nullOnDelete();
            $table->enum('type', ['expense', 'income'])->default('expense');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->date('date');
            $table->string('description', 255);
            $table->string('payment_method', 50)->nullable(); // كاش, بطاقة, تحويل بنكي, محفظة
            $table->string('project_name', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['user_id', 'currency']);
        });

        // 4. Financial Budgets (Monthly overall or per category)
        Schema::create('financial_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('financial_categories')->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1 to 12
            $table->smallInteger('year'); // e.g. 2026
            $table->decimal('budget_amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'month', 'year', 'currency'], 'user_budget_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_budgets');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('financial_categories');

        if (Schema::hasTable('user_settings') && Schema::hasColumn('user_settings', 'currency')) {
            Schema::table('user_settings', function (Blueprint $table) {
                $table->dropColumn('currency');
            });
        }
    }
};
