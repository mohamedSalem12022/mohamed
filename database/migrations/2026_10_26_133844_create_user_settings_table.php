<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('theme', 20)->default('light'); // 'light', 'dark'
            $table->string('language', 10)->default('ar'); // 'ar', 'en'
            $table->string('primary_color', 20)->default('#0d9488'); // Default teal/turquoise
            $table->string('accent_color', 20)->default('#f97316'); // Default orange
            $table->unsignedTinyInteger('week_starts_on')->default(6); // 6 = Saturday
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
