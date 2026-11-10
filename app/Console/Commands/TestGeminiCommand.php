<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AiService;

class TestGeminiCommand extends Command
{
    protected $signature = 'ai:test-gemini {--key= : Optional API key to test directly}';
    protected $description = 'Test the Google Gemini API connection and verify configuration';

    public function handle(AiService $aiService): int
    {
        $this->info('فحص الاتصال بنموذج Google Gemini...');

        $customKey = $this->option('key');
        $key = $customKey ?: (config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');

        if (empty($key)) {
            $this->error('لم يتم العثور على مفتاح GEMINI_API_KEY في ملف .env!');
            $this->line('يرجى وضع المفتاح في ملف .env:');
            $this->line('GEMINI_API_KEY=your_key_here');
            return Command::FAILURE;
        }

        $maskedKey = substr($key, 0, 6) . '...' . substr($key, -4);
        $this->line("المفتاح المستخدم: {$maskedKey}");
        $this->line("النموذج المستهدف: {$model}");

        $result = $aiService->testGeminiConnection($key, $model);

        if ($result['success']) {
            $this->info('✓ ' . $result['message']);
            $this->line('رد النموذج: ' . $result['reply']);
            return Command::SUCCESS;
        }

        $this->error('✗ ' . $result['message']);
        return Command::FAILURE;
    }
}
