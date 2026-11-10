<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>التقرير المالي - {{ $periodLabel }}</title>
    <style>
        body {
            font-family: 'tajawal', 'cairo', sans-serif;
            direction: rtl;
            text-align: right;
            color: #1e293b;
            font-size: 10.5px;
            line-height: 1.5;
            padding: 0;
            margin: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 2.5px solid #0d9488;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #0f766e;
            margin: 0;
        }

        .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 3px;
        }

        .meta-info {
            text-align: left;
            font-size: 9.5px;
            color: #475569;
            line-height: 1.6;
        }

        /* Summary KPI Cards Table */
        .summary-table {
            width: 100%;
            margin-bottom: 16px;
            border-collapse: collapse;
        }

        .summary-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 9px;
            text-align: center;
        }

        .card-label {
            font-size: 9px;
            color: #64748b;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .card-value {
            font-size: 12.5px;
            font-weight: bold;
        }

        .income-text {
            color: #0d9488;
        }

        .expense-text {
            color: #e11d48;
        }

        .net-positive {
            color: #059669;
        }

        .net-negative {
            color: #dc2626;
        }

        .budget-text {
            color: #0284c7;
        }

        /* Section Title */
        .section-title {
            font-size: 11.5px;
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 8px;
            border-right: 3px solid #0d9488;
            padding-right: 6px;
        }

        /* Multi-Currency Balances Box */
        .multi-currencies-box {
            background-color: #f0fdfa;
            border: 1px solid #99f6e4;
            border-radius: 8px;
            padding: 9px 12px;
            margin-bottom: 16px;
            font-size: 9.5px;
        }

        .multi-currencies-title {
            font-weight: bold;
            color: #0f766e;
            margin-bottom: 4px;
        }

        /* Transactions Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9.5px;
        }

        .data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            padding: 6px 7px;
            text-align: right;
            border: 1px solid #0f766e;
        }

        .data-table td {
            padding: 6px 7px;
            border: 1px solid #e2e8f0;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-income {
            background-color: #ccfbf1;
            color: #0f766e;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 4px;
            display: inline-block;
        }

        .badge-expense {
            background-color: #ffe4e6;
            color: #be123c;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 4px;
            display: inline-block;
        }

        .footer {
            margin-top: 25px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 58%; vertical-align: middle;">
                <h1 class="title">التقرير المالي والمصروفات — MO Life OS</h1>
                <p class="subtitle">سجل الدخل والمصروفات والميزانية والتحليل المالي</p>
            </td>
            <td class="meta-info" style="width: 42%; vertical-align: middle;">
                <p><strong>المستخدم:</strong> {{ $user->name }}</p>
                <p><strong>الفترة المحددة:</strong> {{ $periodLabel }}</p>
                <p><strong>العملة الأساسية:</strong> {{ $currency }} ({{ $currencyName }})</p>
                <p><strong>تاريخ التصدير:</strong> {{ now()->format('Y-m-d H:i') }}</p>
            </td>
        </tr>
    </table>

    <!-- Summary KPI Cards -->
    <table class="summary-table">
        <tr>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-card">
                    <div class="card-label">إجمالي الدخل</div>
                    <div class="card-value income-text">{{ number_format($incomeTotal, 2) }} {{ $currency }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-card">
                    <div class="card-label">إجمالي المصروفات</div>
                    <div class="card-value expense-text">{{ number_format($expenseTotal, 2) }} {{ $currency }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-card">
                    <div class="card-label">صافي الرصيد</div>
                    <div class="card-value {{ $netBalance >= 0 ? 'net-positive' : 'net-negative' }}">
                        {{ ($netBalance >= 0 ? '+' : '') . number_format($netBalance, 2) }} {{ $currency }}
                    </div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-card">
                    <div class="card-label">سقف الميزانية</div>
                    <div class="card-value budget-text">
                        {{ $budgetAmount > 0 ? number_format($budgetAmount, 2) . ' ' . $currency : 'غير محددة' }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Multi-Currency Isolation Notice if other currencies exist -->
    @if(count($multiCurrencyBalances) > 1)
    <div class="multi-currencies-box">
        <div class="multi-currencies-title">الأرصدة المسجلة بالعملات الأخرى خلال نفس الفترة (عزل العملات دون دمج):</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                @foreach($multiCurrencyBalances as $cCode => $cBal)
                    <td style="padding: 3px 6px;">
                        <strong>{{ $cCode }} ({{ $cBal['name'] }}):</strong>
                        <span style="font-weight: bold; color: {{ $cBal['net'] >= 0 ? '#0f766e' : '#be123c' }};">
                            {{ ($cBal['net'] >= 0 ? '+' : '') . number_format($cBal['net'], 2) }}
                        </span>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>
    @endif

    <!-- Transactions List -->
    <div class="section-title">
        سجل المعاملات المالية ({{ $transactions->count() }} معاملة بعملة {{ $currency }})
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 11%;">التاريخ</th>
                <th style="width: 9%; text-align: center;">النوع</th>
                <th style="width: 15%; text-align: left;">المبلغ</th>
                <th style="width: 8%; text-align: center;">العملة</th>
                <th style="width: 17%;">التصنيف</th>
                <th style="width: 23%;">الوصف</th>
                <th style="width: 12%;">طريقة الدفع</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $t)
            <tr>
                <td style="text-align: center; color: #64748b;">{{ $index + 1 }}</td>
                <td>{{ $t->date ? $t->date->format('Y-m-d') : '' }}</td>
                <td style="text-align: center;">
                    @if($t->type === 'income')
                        <span class="badge-income">دخل</span>
                    @else
                        <span class="badge-expense">مصروف</span>
                    @endif
                </td>
                <td style="text-align: left; font-weight: bold; direction: ltr;">
                    {{ number_format($t->amount, 2) }}
                </td>
                <td style="text-align: center; font-weight: bold; color: #0f766e;">
                    {{ $t->currency }}
                </td>
                <td>{{ $t->category?->name ?? 'أخرى / بدون تصنيف' }}</td>
                <td>
                    <strong>{{ $t->description }}</strong>
                    @if(!empty($t->project_name))
                        <br><span style="font-size: 8px; color: #64748b;">مشروع: {{ $t->project_name }}</span>
                    @endif
                    @if(!empty($t->notes))
                        <br><span style="font-size: 8px; color: #94a3b8;">{{ $t->notes }}</span>
                    @endif
                </td>
                <td>{{ $t->payment_method ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 16px; color: #94a3b8;">
                    لا توجد معاملات مسجلة لهذه الفترة بالعملة المحددة ({{ $currency }}).
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        تم إنشاء هذا التقرير تلقائياً بواسطة نظام MO Life OS &bull; صفحة {PAGENO} من {nbpg}
    </div>

</body>
</html>
