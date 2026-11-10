<?php

namespace App\Http\Controllers;

use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\FinancialBudget;
use App\Services\AiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinancialController extends Controller
{
    public function __construct(
        protected AiService $aiService
    ) {}

    /**
     * Financial Dashboard & Management
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userSettings = $user->settings;
        $defaultCurrency = $userSettings?->currency ?: 'USD';

        // Extensible ISO 4217 currencies
        $currencies = config('currencies', []);

        // Dynamic Years: from user's oldest transaction year (or current year) to current year + 1
        $currentYear = Carbon::now()->year;
        $oldestTxDate = $user->financialTransactions()->min('date');
        $oldestYear = $oldestTxDate ? Carbon::parse($oldestTxDate)->year : $currentYear;
        $startYear = min($oldestYear, $currentYear);
        $endYear = $currentYear + 1;
        $years = range($startYear, $endYear);

        // Filters: Year & Month (supports specific month or 'all' for full year)
        $selectedYear = (int) $request->input('year', $currentYear);
        $year = $selectedYear;

        $monthInput = $request->input('month', Carbon::now()->month);
        $isAnnual = ($monthInput === 'all');
        $month = $isAnnual ? 'all' : (int) $monthInput;

        $currency = $request->input('currency', $defaultCurrency);
        if (empty($currency)) {
            $currency = $defaultCurrency;
        }

        // Ensure user categories are seeded
        FinancialCategory::seedDefaultsForUser($user->id);
        $categories = $user->financialCategories()->get();

        // Available currencies used by user
        $usedCurrencies = $user->financialTransactions()
            ->distinct()
            ->pluck('currency')
            ->toArray();
        if (!in_array($defaultCurrency, $usedCurrencies)) {
            $usedCurrencies[] = $defaultCurrency;
        }
        if (!in_array('USD', $usedCurrencies)) {
            $usedCurrencies[] = 'USD';
        }
        $usedCurrencies = array_values(array_unique($usedCurrencies));

        // Start and end dates based on month or full year
        if ($isAnnual) {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
        }

        // 1. Transactions for current period & currency
        $periodTransactionsQuery = $user->financialTransactions()
            ->with('category')
            ->where('currency', $currency)
            ->whereBetween('date', [$startDate, $endDate]);

        $incomeTotal = (float) (clone $periodTransactionsQuery)->where('type', 'income')->sum('amount');
        $expenseTotal = (float) (clone $periodTransactionsQuery)->where('type', 'expense')->sum('amount');
        $netBalance = $incomeTotal - $expenseTotal;
        $savingsRate = $incomeTotal > 0 ? round(($netBalance / $incomeTotal) * 100, 1) : 0;

        // 2. Budget for period & currency
        if ($isAnnual) {
            $budgetAmount = (float) $user->financialBudgets()
                ->whereNull('category_id')
                ->where('year', $year)
                ->where('currency', $currency)
                ->sum('budget_amount');
        } else {
            $overallBudget = $user->financialBudgets()
                ->whereNull('category_id')
                ->where('month', $month)
                ->where('year', $year)
                ->where('currency', $currency)
                ->first();
            $budgetAmount = $overallBudget ? (float) $overallBudget->budget_amount : 0.0;
        }

        $budgetRemaining = $budgetAmount > 0 ? ($budgetAmount - $expenseTotal) : 0.0;
        $budgetUsedPercent = $budgetAmount > 0 ? min(100, round(($expenseTotal / $budgetAmount) * 100, 1)) : 0;

        // 3. Category Expenses Breakdown
        $categoryExpenses = (clone $periodTransactionsQuery)
            ->where('type', 'expense')
            ->selectRaw('category_id, sum(amount) as total_amount')
            ->groupBy('category_id')
            ->get();

        // Category budgets for period
        if ($isAnnual) {
            $categoryBudgets = $user->financialBudgets()
                ->whereNotNull('category_id')
                ->where('year', $year)
                ->where('currency', $currency)
                ->selectRaw('category_id, sum(budget_amount) as total_budget')
                ->groupBy('category_id')
                ->pluck('total_budget', 'category_id');
        } else {
            $categoryBudgets = $user->financialBudgets()
                ->whereNotNull('category_id')
                ->where('month', $month)
                ->where('year', $year)
                ->where('currency', $currency)
                ->pluck('budget_amount', 'category_id');
        }

        $categoriesBreakdown = [];
        foreach ($categoryExpenses as $ce) {
            $cat = $categories->find($ce->category_id);
            $cBudgetAmount = isset($categoryBudgets[$ce->category_id]) ? (float) $categoryBudgets[$ce->category_id] : 0.0;
            $spent = (float) $ce->total_amount;
            $percentOfTotal = $expenseTotal > 0 ? round(($spent / $expenseTotal) * 100, 1) : 0;

            $categoriesBreakdown[] = [
                'id' => $cat?->id,
                'name' => $cat?->name ?? 'أخرى / بدون تصنيف',
                'icon' => $cat?->icon ?? 'fa-asterisk',
                'color' => $cat?->color ?? '#0d9488',
                'spent' => $spent,
                'budget' => $cBudgetAmount,
                'percent_of_total' => $percentOfTotal,
                'is_over_budget' => ($cBudgetAmount > 0 && $spent > $cBudgetAmount),
            ];
        }

        // Sort descending by spent amount
        usort($categoriesBreakdown, fn($a, $b) => $b['spent'] <=> $a['spent']);

        // 4. Comparison with previous period
        if ($isAnnual) {
            $prevStartDate = Carbon::createFromDate($year - 1, 1, 1)->startOfYear()->toDateString();
            $prevEndDate = Carbon::createFromDate($year - 1, 12, 31)->endOfYear()->toDateString();
        } else {
            $prevMonthDate = Carbon::createFromDate($year, $month, 1)->subMonth();
            $prevStartDate = $prevMonthDate->copy()->startOfMonth()->toDateString();
            $prevEndDate = $prevMonthDate->copy()->endOfMonth()->toDateString();
        }

        $prevIncome = (float) $user->financialTransactions()
            ->where('currency', $currency)
            ->where('type', 'income')
            ->whereBetween('date', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $prevExpense = (float) $user->financialTransactions()
            ->where('currency', $currency)
            ->where('type', 'expense')
            ->whereBetween('date', [$prevStartDate, $prevEndDate])
            ->sum('amount');

        $expenseChangePercent = $prevExpense > 0 ? round((($expenseTotal - $prevExpense) / $prevExpense) * 100, 1) : 0;
        $incomeChangePercent = $prevIncome > 0 ? round((($incomeTotal - $prevIncome) / $prevIncome) * 100, 1) : 0;

        // 5. Paginated or listed transactions with search & filter
        $filterType = $request->input('type');
        $filterCategory = $request->input('category_id');
        $search = trim($request->input('search', ''));

        $txQuery = $user->financialTransactions()
            ->with('category')
            ->where('currency', $currency)
            ->whereBetween('date', [$startDate, $endDate]);

        if (!empty($filterType)) {
            $txQuery->where('type', $filterType);
        }
        if (!empty($filterCategory)) {
            $txQuery->where('category_id', $filterCategory);
        }
        if (!empty($search)) {
            $txQuery->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $transactions = $txQuery->orderBy('date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        // 6. Multi-currency summary (all currencies in current period, isolated)
        $allCurrenciesSummary = $user->financialTransactions()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('currency, type, sum(amount) as total')
            ->groupBy('currency', 'type')
            ->get();

        $multiCurrencyBalances = [];
        foreach ($allCurrenciesSummary as $row) {
            $c = $row->currency;
            if (!isset($multiCurrencyBalances[$c])) {
                $multiCurrencyBalances[$c] = [
                    'name' => $currencies[$c] ?? $c,
                    'income' => 0.0,
                    'expense' => 0.0,
                    'net' => 0.0,
                ];
            }
            if ($row->type === 'income') {
                $multiCurrencyBalances[$c]['income'] = (float) $row->total;
            } else {
                $multiCurrencyBalances[$c]['expense'] = (float) $row->total;
            }
            $multiCurrencyBalances[$c]['net'] = $multiCurrencyBalances[$c]['income'] - $multiCurrencyBalances[$c]['expense'];
        }

        return view('finances.index', compact(
            'month',
            'year',
            'years',
            'selectedYear',
            'isAnnual',
            'currency',
            'currencies',
            'defaultCurrency',
            'usedCurrencies',
            'categories',
            'incomeTotal',
            'expenseTotal',
            'netBalance',
            'savingsRate',
            'budgetAmount',
            'budgetRemaining',
            'budgetUsedPercent',
            'categoriesBreakdown',
            'transactions',
            'prevIncome',
            'prevExpense',
            'expenseChangePercent',
            'incomeChangePercent',
            'multiCurrencyBalances'
        ));
    }

    /**
     * Store a new financial transaction
     */
    public function storeTransaction(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'currency' => ['required', 'string', 'max:10'],
            'category_id' => ['nullable', 'exists:financial_categories,id'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'project_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Security check: verify category belongs to user if provided
        if (!empty($validated['category_id'])) {
            $category = $user->financialCategories()->find($validated['category_id']);
            if (!$category) {
                return back()->withErrors(['category_id' => 'التصنيف المختار غير صالح.']);
            }
        }

        $user->financialTransactions()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل المعاملة المالية بنجاح! ✓',
            ]);
        }

        return redirect()->back()->with('success', 'تم تسجيل المعاملة المالية بنجاح! ✓');
    }

    /**
     * Update an existing transaction
     */
    public function updateTransaction(Request $request, FinancialTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:99999999.99'],
            'currency' => ['required', 'string', 'max:10'],
            'category_id' => ['nullable', 'exists:financial_categories,id'],
            'date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'project_name' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($validated['category_id'])) {
            $category = $user->financialCategories()->find($validated['category_id']);
            if (!$category) {
                return back()->withErrors(['category_id' => 'التصنيف المختار غير صالح.']);
            }
        }

        $transaction->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث المعاملة بنجاح! ✓',
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث المعاملة بنجاح! ✓');
    }

    /**
     * Delete a transaction
     */
    public function destroyTransaction(FinancialTransaction $transaction)
    {
        $user = Auth::user();
        if ($transaction->user_id !== $user->id) {
            abort(403);
        }

        $transaction->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'تم حذف المعاملة المالية بنجاح.',
            ]);
        }

        return redirect()->back()->with('success', 'تم حذف المعاملة المالية بنجاح.');
    }

    /**
     * Set or update monthly budget
     */
    public function setBudget(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:2020', 'max:2050'],
            'currency' => ['required', 'string', 'max:10'],
            'budget_amount' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'category_id' => ['nullable', 'exists:financial_categories,id'],
        ]);

        FinancialBudget::updateOrCreate(
            [
                'user_id' => $user->id,
                'category_id' => $validated['category_id'] ?? null,
                'month' => $validated['month'],
                'year' => $validated['year'],
                'currency' => $validated['currency'],
            ],
            [
                'budget_amount' => $validated['budget_amount'],
            ]
        );

        return redirect()->back()->with('success', 'تم حفظ الميزانية الشهرية بنجاح! ✓');
    }

    /**
     * Create custom category
     */
    public function storeCategory(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:expense,income,both'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $user->financialCategories()->create([
            'name' => trim($validated['name']),
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?: 'fa-tag',
            'color' => $validated['color'] ?: '#0d9488',
            'is_default' => false,
        ]);

        return redirect()->back()->with('success', 'تمت إضافة التصنيف المالي بنجاح! ✓');
    }

    /**
     * Export Transactions as CSV with UTF-8 BOM
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $user = Auth::user();

        $monthInput = $request->input('month', Carbon::now()->month);
        $isAnnual = ($monthInput === 'all');
        $year = (int) $request->input('year', Carbon::now()->year);
        $currency = $request->input('currency');
        $type = $request->input('type');

        $query = $user->financialTransactions()->with('category');

        if ($isAnnual) {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
            $query->whereBetween('date', [$startDate, $endDate]);
            $fileName = "mo_finances_{$year}_full.csv";
        } elseif ($monthInput && $year) {
            $month = (int) $monthInput;
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
            $query->whereBetween('date', [$startDate, $endDate]);
            $fileName = "mo_finances_{$year}_{$month}.csv";
        } else {
            $fileName = "mo_finances_{$year}.csv";
        }

        if (!empty($currency)) {
            $query->where('currency', $currency);
        }
        if (!empty($type)) {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('date', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            // Write UTF-8 BOM for Microsoft Excel Arabic support
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // CSV Header Row
            fputcsv($handle, [
                'المعرف',
                'التاريخ',
                'النوع',
                'المبلغ',
                'العملة',
                'التصنيف',
                'الوصف',
                'طريقة الدفع',
                'المشروع',
                'ملاحظات',
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->id,
                    $t->date ? $t->date->format('Y-m-d') : '',
                    $t->type === 'income' ? 'دخل' : 'مصروف',
                    number_format($t->amount, 2, '.', ''),
                    $t->currency,
                    $t->category?->name ?? 'بدون تصنيف',
                    $t->description,
                    $t->payment_method ?? '',
                    $t->project_name ?? '',
                    $t->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Export Transactions as PDF using mPDF with Arabic RTL support & Tajawal/Cairo fonts
     */
    public function exportPdf(Request $request)
    {
        $user = Auth::user();
        $userSettings = $user->settings;
        $defaultCurrency = $userSettings?->currency ?: 'USD';

        $currencies = config('currencies', []);
        $currentYear = Carbon::now()->year;
        $year = (int) $request->input('year', $currentYear);
        $monthInput = $request->input('month', Carbon::now()->month);
        $isAnnual = ($monthInput === 'all');
        $currency = $request->input('currency', $defaultCurrency) ?: $defaultCurrency;
        $currencyName = $currencies[$currency] ?? $currency;

        if ($isAnnual) {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
            $periodLabel = "كامل عام {$year}";
            $fileName = "المالية-والمصروفات-{$year}-كاملة.pdf";
            $budgetAmount = (float) $user->financialBudgets()
                ->whereNull('category_id')
                ->where('year', $year)
                ->where('currency', $currency)
                ->sum('budget_amount');
        } else {
            $month = (int) $monthInput;
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
            $monthName = Carbon::create($year, $month, 1)->locale('ar')->translatedFormat('F');
            $periodLabel = "{$monthName} {$year}";
            $fileName = "المالية-والمصروفات-{$year}-{$month}.pdf";
            $overallBudget = $user->financialBudgets()
                ->whereNull('category_id')
                ->where('month', $month)
                ->where('year', $year)
                ->where('currency', $currency)
                ->first();
            $budgetAmount = $overallBudget ? (float) $overallBudget->budget_amount : 0.0;
        }

        // Transactions in this currency
        $transactions = $user->financialTransactions()
            ->with('category')
            ->where('currency', $currency)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $incomeTotal = (float) $transactions->where('type', 'income')->sum('amount');
        $expenseTotal = (float) $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $incomeTotal - $expenseTotal;

        // Multi-currency isolated breakdown for same period
        $allCurrenciesSummary = $user->financialTransactions()
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('currency, type, sum(amount) as total')
            ->groupBy('currency', 'type')
            ->get();

        $multiCurrencyBalances = [];
        foreach ($allCurrenciesSummary as $row) {
            $c = $row->currency;
            if (!isset($multiCurrencyBalances[$c])) {
                $multiCurrencyBalances[$c] = [
                    'name' => $currencies[$c] ?? $c,
                    'income' => 0.0,
                    'expense' => 0.0,
                    'net' => 0.0,
                ];
            }
            if ($row->type === 'income') {
                $multiCurrencyBalances[$c]['income'] = (float) $row->total;
            } else {
                $multiCurrencyBalances[$c]['expense'] = (float) $row->total;
            }
            $multiCurrencyBalances[$c]['net'] = $multiCurrencyBalances[$c]['income'] - $multiCurrencyBalances[$c]['expense'];
        }

        // Render Blade template to HTML
        $html = view('finances.pdf', compact(
            'user',
            'periodLabel',
            'year',
            'currency',
            'currencyName',
            'incomeTotal',
            'expenseTotal',
            'netBalance',
            'budgetAmount',
            'multiCurrencyBalances',
            'transactions'
        ))->render();

        // Ensure temp folder exists
        $tempDir = storage_path('app/mpdf');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        // Configure mPDF
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'tempDir' => $tempDir,
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'tajawal' => [
                    'R' => 'Tajawal-Regular.ttf',
                    'B' => 'Tajawal-Bold.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
                'cairo' => [
                    'R' => 'Cairo.ttf',
                    'B' => 'Cairo.ttf',
                    'useOTL' => 0xFF,
                    'useKashida' => 75,
                ],
            ],
            'default_font' => 'tajawal',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $mpdf->SetDirectionality('rtl');
        $mpdf->WriteHTML($html);

        $pdfContent = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        $asciiFileName = "finances_{$year}.pdf";

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$asciiFileName}\"; filename*=UTF-8''" . rawurlencode($fileName),
            'Cache-Control' => 'private, no-transform, no-store, must-revalidate',
        ]);
    }

    /**
     * AI Financial Insights generated via Gemini with deterministic figures
     */
    public function getAiInsights(Request $request)
    {
        $user = Auth::user();
        $monthInput = $request->input('month', Carbon::now()->month);
        $isAnnual = ($monthInput === 'all');
        $year = (int) $request->input('year', Carbon::now()->year);
        $currency = $request->input('currency', 'USD');

        if ($isAnnual) {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();
            $periodLabel = "كامل عام {$year}";
            $periodContext = "لعام {$year}";
        } else {
            $month = (int) $monthInput;
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();
            $periodLabel = "شهر {$month}/{$year}";
            $periodContext = "لشهر {$month}/{$year}";
        }

        $txQuery = $user->financialTransactions()
            ->with('category')
            ->where('currency', $currency)
            ->whereBetween('date', [$startDate, $endDate]);

        $income = (float) (clone $txQuery)->where('type', 'income')->sum('amount');
        $expense = (float) (clone $txQuery)->where('type', 'expense')->sum('amount');
        $net = $income - $expense;

        // Top categories
        $categoryBreakdown = (clone $txQuery)
            ->where('type', 'expense')
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $topCatsText = "";
        foreach ($categoryBreakdown as $row) {
            $catName = $user->financialCategories->find($row->category_id)?->name ?? 'أخرى';
            $topCatsText .= "- {$catName}: " . number_format($row->total, 2) . " {$currency}\n";
        }

        // Budget
        if ($isAnnual) {
            $budgetAmount = (float) $user->financialBudgets()
                ->whereNull('category_id')
                ->where('year', $year)
                ->where('currency', $currency)
                ->sum('budget_amount');
        } else {
            $overallBudget = $user->financialBudgets()
                ->whereNull('category_id')
                ->where('month', (int)$monthInput)
                ->where('year', $year)
                ->where('currency', $currency)
                ->first();
            $budgetAmount = $overallBudget ? (float) $overallBudget->budget_amount : 0.0;
        }
        $budgetStatus = $budgetAmount > 0 ? "الميزانية المحددة: {$budgetAmount} {$currency} (المصروف منها: {$expense} {$currency})" : "لم يتم تحديد ميزانية لهذه الفترة.";

        $prompt = <<<EOT
أنت المستشار المالي الذكي لنظام «MO Life OS».
المطلوب تقديم تحليل مالي احترافي وموجز {$periodContext} للمستخدم ({$user->name}).

الأرقام المالية الفعلية من قاعدة البيانات (حسابات مؤكدة):
- العملة: {$currency}
- إجمالي الدخل: {$income} {$currency}
- إجمالي المصروفات: {$expense} {$currency}
- الصافي المالي: {$net} {$currency}
- حالة الميزانية: {$budgetStatus}
- أعلى التصنيفات إنفاقاً:
{$topCatsText}

المطلوب:
1. 📊 **تقييم الوضع المالي للفترة:** قراءة واقعية وموجزة للأرقام.
2. ⚠️ **ملاحظات الإنفاق والميزانية:** تنبيهات حول أعلى التصنيفات أو اقتراب تجاوز الميزانية إن وجد.
3. 💡 **3 نصائح عملية ومحددة لترشيد الإنفاق وزيادة الادخار.**

قواعد:
- لا تخترع أي أرقام أو معاملات خارج المذكورة أعلاه.
- قدّم الإجابة باللغة العربية بأسلوب راقٍ، عملي ومشجع.
EOT;

        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');
        $model = config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-1.5-flash');

        $insights = null;
        if (!empty($geminiKey)) {
            $geminiRes = $this->aiService->testGeminiConnection($geminiKey, $model);
            if ($geminiRes['success']) {
                // Call Gemini via prompt
                $rawRes = $this->callGeminiInsights($geminiKey, $model, $prompt);
                if ($rawRes) {
                    $insights = $rawRes;
                }
            }
        }

        if (!$insights) {
            // Smart local deterministic fallback advice
            $topCatSummary = !empty($topCatsText) ? $topCatsText : "- لا توجد مصروفات مسجلة لهذا الشهر.\n";
            $statusSentence = $net >= 0
                ? "وضعك المالي إيجابي بفائض قدره " . number_format($net, 2) . " {$currency}."
                : "المصروفات تجاوزت الدخل بعجز قدره " . number_format(abs($net), 2) . " {$currency}.";

            $insights = "### 📊 تقييم الوضع المالي للشهر\n" .
                "إجمالي دخلك المسجل هو " . number_format($income, 2) . " {$currency} مقابل مصروفات بقيمة " . number_format($expense, 2) . " {$currency}. {$statusSentence}\n\n" .
                "### ⚠️ أعلى التصنيفات إنفاقاً:\n{$topCatSummary}\n" .
                "### 💡 3 نصائح عملية لترشيد الإنفاق:\n" .
                "1. راجع أعلى تصنيف إنفاقاً وحدد نسبة تخفيض واقعية (10-15%) للأسبوع القادم.\n" .
                "2. ضع ميزانية أسبوعية محددة للمصروفات المتغيرة (مثل الطعام والمواصلات).\n" .
                "3. سجّل أي مصروف فور حدوثه باستخدام المساعد الصوتي أو MO AI لتفادي تراكم المصاريف دون رقابة.";
        }

        return response()->json([
            'success' => true,
            'insights' => $insights,
            'numbers' => [
                'income' => $income,
                'expense' => $expense,
                'net' => $net,
                'currency' => $currency,
            ]
        ]);
    }

    /**
     * Direct helper to call Gemini for financial insights
     */
    protected function callGeminiInsights(string $apiKey, string $model, string $prompt): ?string
    {
        try {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent";
            $res = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->withHeaders(['x-goog-api-key' => $apiKey, 'Content-Type' => 'application/json'])
                ->timeout(12)
                ->post($url, [
                    'contents' => [['role' => 'user', 'parts' => [['text' => $prompt]]]],
                    'generationConfig' => ['temperature' => 0.7, 'maxOutputTokens' => 1000]
                ]);

            if ($res->successful()) {
                return $res->json('candidates.0.content.parts.0.text');
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gemini Insights error: ' . $e->getMessage());
        }
        return null;
    }
}
