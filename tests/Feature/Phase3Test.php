<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSettings;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\FinancialBudget;
use App\Models\DailyReflection;
use App\Models\Task;
use App\Models\Habit;
use App\Models\ExcuseLog;
use App\Services\PersonalKnowledgeSearchService;
use App\Services\WebSearchService;
use App\Services\AiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class Phase3Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'phase3_user_' . uniqid() . '@example.com',
        ]);
        UserSettings::create([
            'user_id' => $this->user->id,
            'currency' => 'USD',
        ]);
        FinancialCategory::seedDefaultsForUser($this->user->id);

        $this->otherUser = User::factory()->create([
            'email' => 'other_user_' . uniqid() . '@example.com',
        ]);
        UserSettings::create([
            'user_id' => $this->otherUser->id,
            'currency' => 'EUR',
        ]);
        FinancialCategory::seedDefaultsForUser($this->otherUser->id);
    }

    // ==========================================
    // 1. Personal Finance Tests
    // ==========================================

    public function test_finances_page_loads_successfully()
    {
        $response = $this->actingAs($this->user)->get(route('finances.index'));
        $response->assertStatus(200);
        $response->assertSee('المالية والمصروفات');
        $response->assertSee('المرحلة الثالثة');
    }

    public function test_store_transaction_and_calculate_totals()
    {
        $cat = $this->user->financialCategories()->where('type', 'expense')->first();

        // 1. Store Expense
        $res1 = $this->actingAs($this->user)->post(route('finances.transactions.store'), [
            'type' => 'expense',
            'amount' => 45.50,
            'currency' => 'USD',
            'category_id' => $cat->id,
            'date' => Carbon::today()->toDateString(),
            'description' => 'وجبة غداء عمل',
            'payment_method' => 'كاش',
        ]);
        $res1->assertSessionHas('success');

        // 2. Store Income
        $res2 = $this->actingAs($this->user)->post(route('finances.transactions.store'), [
            'type' => 'income',
            'amount' => 200.00,
            'currency' => 'USD',
            'date' => Carbon::today()->toDateString(),
            'description' => 'دفعة مشروع برمجة',
        ]);
        $res2->assertSessionHas('success');

        // Verify Database
        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'amount' => 45.50,
            'type' => 'expense',
            'description' => 'وجبة غداء عمل',
        ]);

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'amount' => 200.00,
            'type' => 'income',
            'description' => 'دفعة مشروع برمجة',
        ]);
    }

    public function test_multi_currency_isolation_does_not_mix_currencies()
    {
        // Add USD transaction
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 100.00,
            'currency' => 'USD',
            'date' => Carbon::today()->toDateString(),
            'description' => 'USD expense',
        ]);

        // Add ILS transaction
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 350.00,
            'currency' => 'ILS',
            'date' => Carbon::today()->toDateString(),
            'description' => 'ILS expense',
        ]);

        // Request finances page for USD
        $resUSD = $this->actingAs($this->user)->get(route('finances.index', ['currency' => 'USD']));
        $resUSD->assertStatus(200);
        $resUSD->assertViewHas('expenseTotal', 100.00);

        // Request finances page for ILS
        $resILS = $this->actingAs($this->user)->get(route('finances.index', ['currency' => 'ILS']));
        $resILS->assertStatus(200);
        $resILS->assertViewHas('expenseTotal', 350.00);
    }

    public function test_budget_setting_and_tracking()
    {
        $today = Carbon::today();

        // Set monthly overall budget
        $res = $this->actingAs($this->user)->post(route('finances.budgets.store'), [
            'month' => $today->month,
            'year' => $today->year,
            'currency' => 'USD',
            'budget_amount' => 500.00,
        ]);
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('financial_budgets', [
            'user_id' => $this->user->id,
            'month' => $today->month,
            'year' => $today->year,
            'currency' => 'USD',
            'budget_amount' => 500.00,
        ]);
    }

    public function test_financial_csv_export_has_utf8_bom()
    {
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 75.00,
            'currency' => 'USD',
            'date' => Carbon::today()->toDateString(),
            'description' => 'شراء كتب ومراجع',
        ]);

        $res = $this->actingAs($this->user)->get(route('finances.export-csv'));
        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $content = $res->streamedContent();
        // Check for UTF-8 BOM bytes (\xEF\xBB\xBF)
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('شراء كتب ومراجع', $content);
    }

    public function test_dynamic_years_and_currencies_in_finances_view()
    {
        $currentYear = Carbon::now()->year;
        // Insert a transaction in an earlier year
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 50.00,
            'currency' => 'USD',
            'date' => '2022-05-15',
            'description' => 'معاملة قديمة',
        ]);

        $res = $this->actingAs($this->user)->get(route('finances.index'));
        $res->assertStatus(200);

        // Assert dynamic years
        $years = $res->viewData('years');
        $this->assertIsArray($years);
        $this->assertEquals(2022, $years[0]);
        $this->assertEquals($currentYear + 1, end($years));

        // Assert currencies configuration
        $currencies = $res->viewData('currencies');
        $this->assertIsArray($currencies);
        $this->assertArrayHasKey('USD', $currencies);
        $this->assertArrayHasKey('ILS', $currencies);
        $this->assertArrayHasKey('SAR', $currencies);
        $this->assertArrayHasKey('JOD', $currencies);
        $this->assertArrayHasKey('EGP', $currencies);

        // Assert presence of PDF export button in HTML
        $res->assertSee('تصدير PDF');
        $res->assertSee('كامل السنة');
    }

    public function test_annual_view_mode_aggregates_entire_year()
    {
        $year = Carbon::now()->year;

        // Jan transaction
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1000.00,
            'currency' => 'USD',
            'date' => "{$year}-01-10",
            'description' => 'راتب يناير',
        ]);

        // Nov transaction
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 1200.00,
            'currency' => 'USD',
            'date' => "{$year}-11-10",
            'description' => 'راتب نوفمبر',
        ]);

        $res = $this->actingAs($this->user)->get(route('finances.index', ['month' => 'all', 'year' => $year, 'currency' => 'USD']));
        $res->assertStatus(200);
        $res->assertViewHas('isAnnual', true);
        $res->assertViewHas('incomeTotal', 2200.00);
    }

    public function test_financial_pdf_export_generates_valid_arabic_pdf()
    {
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 125.50,
            'currency' => 'USD',
            'date' => Carbon::today()->toDateString(),
            'description' => 'تجديد اشتراك استضافة وسيرفر',
            'payment_method' => 'بطاقة ائتمانية',
        ]);

        $res = $this->actingAs($this->user)->get(route('finances.export-pdf', [
            'year' => Carbon::now()->year,
            'month' => Carbon::now()->month,
            'currency' => 'USD',
        ]));

        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/pdf');

        $content = $res->getContent();
        // PDF magic bytes
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_financial_pdf_export_annual_mode()
    {
        FinancialTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 3000.00,
            'currency' => 'USD',
            'date' => Carbon::today()->toDateString(),
            'description' => 'أرباح استشارات سنوية',
        ]);

        $res = $this->actingAs($this->user)->get(route('finances.export-pdf', [
            'year' => Carbon::now()->year,
            'month' => 'all',
            'currency' => 'USD',
        ]));

        $res->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/pdf');

        $content = $res->getContent();
        $this->assertStringStartsWith('%PDF-', $content);
    }

    public function test_recording_transaction_with_various_world_currencies()
    {
        $currenciesToTest = ['ILS', 'EUR', 'SAR', 'JOD', 'KWD'];

        foreach ($currenciesToTest as $curr) {
            $res = $this->actingAs($this->user)->post(route('finances.transactions.store'), [
                'type' => 'income',
                'amount' => 500.00,
                'currency' => $curr,
                'date' => Carbon::today()->toDateString(),
                'description' => "معاملة بعملة {$curr}",
            ]);
            $res->assertSessionHas('success');

            $this->assertDatabaseHas('financial_transactions', [
                'user_id' => $this->user->id,
                'currency' => $curr,
                'amount' => 500.00,
            ]);
        }
    }

    public function test_security_user_cannot_delete_other_user_transaction()
    {
        $otherTx = FinancialTransaction::create([
            'user_id' => $this->otherUser->id,
            'type' => 'expense',
            'amount' => 99.00,
            'currency' => 'EUR',
            'date' => Carbon::today()->toDateString(),
            'description' => 'Other secret expense',
        ]);

        // Attempting to delete someone else's transaction must abort with 403
        $res = $this->actingAs($this->user)->delete(route('finances.transactions.destroy', $otherTx));
        $res->assertStatus(403);
        $this->assertDatabaseHas('financial_transactions', ['id' => $otherTx->id]);
    }

    // ==========================================
    // 2. Personal Knowledge Search Tests
    // ==========================================

    public function test_personal_knowledge_search_finds_notes_and_tasks()
    {
        // Create user's reflection note
        DailyReflection::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'notes' => 'أفكار حول مشروع إدارة الملاعب وتطويره باستخدام Laravel و Livewire',
            'rating' => 5,
        ]);

        // Create user's task
        Task::create([
            'user_id' => $this->user->id,
            'title' => 'بناء واجهة لوحة تحكم الملاعب',
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $service = app(PersonalKnowledgeSearchService::class);
        $results = $service->search($this->user, 'الملاعب');

        $this->assertNotEmpty($results);
        $this->assertGreaterThanOrEqual(2, count($results));

        $foundReflection = false;
        $foundTask = false;
        foreach ($results as $r) {
            if ($r['type'] === 'reflection') $foundReflection = true;
            if ($r['type'] === 'task') $foundTask = true;
            $this->assertNotEmpty($r['url']);
            $this->assertNotEmpty($r['snippet']);
        }

        $this->assertTrue($foundReflection);
        $this->assertTrue($foundTask);
    }

    public function test_personal_knowledge_search_user_data_isolation()
    {
        // Note belongs ONLY to otherUser
        DailyReflection::create([
            'user_id' => $this->otherUser->id,
            'date' => Carbon::today(),
            'notes' => 'معلومات سرية جداً تخص مشروع الفضاء الخاص بمحمد',
            'rating' => 5,
        ]);

        $service = app(PersonalKnowledgeSearchService::class);
        $results = $service->search($this->user, 'مشروع الفضاء');

        // User must NOT see other user's note
        $this->assertEmpty($results);
    }

    // ==========================================
    // 3. Web Search Service Tests
    // ==========================================

    public function test_web_search_service_returns_structured_results()
    {
        $service = app(WebSearchService::class);
        $results = $service->search('Laravel', 2);

        $this->assertIsArray($results);
        // Live web or wikipedia fallback should return items
        if (!empty($results)) {
            $first = $results[0];
            $this->assertArrayHasKey('title', $first);
            $this->assertArrayHasKey('url', $first);
            $this->assertArrayHasKey('snippet', $first);
            $this->assertArrayHasKey('source', $first);
            $this->assertNotEmpty($first['url']);
        }
    }

    // ==========================================
    // 4. MO AI Central Router & Intent Tests
    // ==========================================

    public function test_ai_finance_intent_generates_confirmation_card()
    {
        $service = app(AiService::class);
        $response = $service->respond($this->user, 'سجل مصروف 25 دولاراً للمواصلات اليوم');

        $this->assertEquals('confirm_transaction', $response['action_type']);
        $this->assertNotNull($response['action_data']);
        $this->assertEquals(25.0, (float) $response['action_data']['amount']);
        $this->assertEquals('USD', $response['action_data']['currency']);
        $this->assertEquals('expense', $response['action_data']['type']);

        // Must NOT save to database yet!
        $this->assertDatabaseMissing('financial_transactions', [
            'user_id' => $this->user->id,
            'amount' => 25.00,
        ]);
    }

    public function test_ai_confirm_transaction_endpoint_saves_to_database()
    {
        $cat = $this->user->financialCategories()->first();

        $res = $this->actingAs($this->user)->postJson(route('ai.confirm-transaction'), [
            'type' => 'expense',
            'amount' => 30.00,
            'currency' => 'USD',
            'category_id' => $cat->id,
            'description' => 'مواصلات تاكسي',
            'date' => Carbon::today()->toDateString(),
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $this->assertDatabaseHas('financial_transactions', [
            'user_id' => $this->user->id,
            'amount' => 30.00,
            'currency' => 'USD',
            'description' => 'مواصلات تاكسي',
        ]);
    }

    public function test_ai_knowledge_search_intent_routing()
    {
        DailyReflection::create([
            'user_id' => $this->user->id,
            'date' => Carbon::today(),
            'notes' => 'خطة ميزانية الشهر القادم مع تقليل نفقات الترفيه',
            'rating' => 4,
        ]);

        $service = app(AiService::class);
        $response = $service->respond($this->user, 'ابحث في ملاحظاتي عن ميزانية');

        $this->assertEquals('knowledge_search', $response['action_type']);
        $this->assertNotEmpty($response['action_data']['results']);
    }
}
