@php
    $totalHabits = count($habits);
    $totalDays = $daysInMonth ?? 31;
    $cx = 400;
    $cy = 400;
    $rInner = 135;
    $rOuter = 355;
    $ringThickness = $totalHabits > 0 ? ($rOuter - $rInner) / $totalHabits : 30;
    $totalArcDegrees = 315; // C-shape arc from -90 deg (12 o'clock) clockwise to +225 deg (bottom-left)
    $startAngle = -90;
    $stepAngle = $totalArcDegrees / $totalDays;
@endphp

<div class="relative w-full max-w-[760px] mx-auto select-none habit-wheel-wrapper" id="habitWheelContainer">
    <!-- SVG Radial Grid -->
    <svg viewBox="0 0 800 800" class="w-full h-auto drop-shadow-md overflow-visible" id="habitWheelSvg">
        <defs>
            <filter id="cellGlow" x="-20%" y="-20%" width="140%" height="140%">
                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity="0.15" />
            </filter>
        </defs>

        <!-- Outer Boundary Arc Guide -->
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $rOuter + 2 }}" fill="none" class="stroke-slate-200 dark:stroke-slate-700/60" stroke-width="1.5" />
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $rInner }}" fill="none" class="stroke-slate-200 dark:stroke-slate-700/60" stroke-width="1.5" />

        <!-- Radial Cells -->
        <g id="wheelCellsGroup">
            @for ($day = 1; $day <= $totalDays; $day++)
                @php
                    $a1 = $startAngle + ($day - 1) * $stepAngle;
                    $a2 = $startAngle + $day * $stepAngle;
                    $rad1 = deg2rad($a1);
                    $rad2 = deg2rad($a2);
                    $midRad = deg2rad(($a1 + $a2) / 2);

                    $isToday = ($todayDay ?? null) === $day;
                    $isFuture = ($year == date('Y') && $month == date('n') && $day > date('j'));
                    $labelR = $rOuter + 20;
                    $lblX = $cx + $labelR * cos($midRad);
                    $lblY = $cy + $labelR * sin($midRad);
                @endphp

                <!-- Day Number on Outer Perimeter -->
                <g class="day-label-group cursor-pointer" onclick="handleDayClick({{ $day }})">
                    @if($isToday)
                        <circle cx="{{ $lblX }}" cy="{{ $lblY }}" r="11" class="fill-brand-600 dark:fill-brand-500 shadow-sm" />
                        <text x="{{ $lblX }}" y="{{ $lblY }}" text-anchor="middle" dominant-baseline="central" class="fill-white text-[10px] font-black">
                            {{ $day }}
                        </text>
                    @else
                        <text x="{{ $lblX }}" y="{{ $lblY }}" text-anchor="middle" dominant-baseline="central" class="text-[11px] font-bold {{ $isFuture ? 'fill-slate-300 dark:fill-slate-600' : 'fill-slate-600 dark:fill-slate-300' }} hover:fill-brand-600 dark:hover:fill-brand-400">
                            {{ $day }}
                        </text>
                    @endif
                </g>

                <!-- Ring Sectors for each Habit -->
                @foreach ($habits as $hIndex => $habit)
                    @php
                        $ro = $rOuter - ($hIndex * $ringThickness);
                        $ri = $ro - $ringThickness;

                        $x1 = $cx + $ro * cos($rad1);
                        $y1 = $cy + $ro * sin($rad1);

                        $x2 = $cx + $ro * cos($rad2);
                        $y2 = $cy + $ro * sin($rad2);

                        $x3 = $cx + $ri * cos($rad2);
                        $y3 = $cy + $ri * sin($rad2);

                        $x4 = $cx + $ri * cos($rad1);
                        $y4 = $cy + $ri * sin($rad1);

                        $logData = $logsMatrix[$habit->id][$day] ?? null;
                        $status = $logData['status'] ?? 'empty';
                        $note = $logData['note'] ?? '';
                        $excuse = $logData['excuse_reason'] ?? '';

                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                        $hDisplayName = (($currentLocale ?? 'ar') === 'en' && $habit->name_en) ? $habit->name_en : $habit->name;
                    @endphp

                    <path
                        d="M {{ round($x1, 2) }} {{ round($y1, 2) }} A {{ round($ro, 2) }} {{ round($ro, 2) }} 0 0 1 {{ round($x2, 2) }} {{ round($y2, 2) }} L {{ round($x3, 2) }} {{ round($y3, 2) }} A {{ round($ri, 2) }} {{ round($ri, 2) }} 0 0 0 {{ round($x4, 2) }} {{ round($y4, 2) }} Z"
                        class="wheel-cell status-{{ $status }}"
                        data-habit-id="{{ $habit->id }}"
                        data-habit-name="{{ $hDisplayName }}"
                        data-habit-color="{{ $habit->color }}"
                        data-day="{{ $day }}"
                        data-date="{{ $dateStr }}"
                        data-status="{{ $status }}"
                        data-note="{{ htmlspecialchars($note) }}"
                        data-excuse="{{ htmlspecialchars($excuse) }}"
                        data-future="{{ $isFuture ? 'true' : 'false' }}"
                        onclick="onCellClick(this, event)"
                        onmouseenter="onCellHover(this, event)"
                        onmouseleave="onCellLeave()"
                    />
                @endforeach
            @endfor
        </g>

        <!-- Start & End Cutoff Lines (Lined edge of C-shape) -->
        @php
            $startRad = deg2rad($startAngle);
            $sx1 = $cx + $rInner * cos($startRad);
            $sy1 = $cy + $rInner * sin($startRad);
            $sx2 = $cx + $rOuter * cos($startRad);
            $sy2 = $cy + $rOuter * sin($startRad);

            $endRad = deg2rad($startAngle + $totalArcDegrees);
            $ex1 = $cx + $rInner * cos($endRad);
            $ey1 = $cy + $rInner * sin($endRad);
            $ex2 = $cx + $rOuter * cos($endRad);
            $ey2 = $cy + $rOuter * sin($endRad);
        @endphp
        <line x1="{{ $sx1 }}" y1="{{ $sy1 }}" x2="{{ $sx2 }}" y2="{{ $sy2 }}" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="2" />
        <line x1="{{ $ex1 }}" y1="{{ $ey1 }}" x2="{{ $ex2 }}" y2="{{ $ey2 }}" class="stroke-slate-400 dark:stroke-slate-500" stroke-width="2" />

        <!-- Center Hub of the Wheel -->
        <circle cx="{{ $cx }}" cy="{{ $cy }}" r="{{ $rInner - 8 }}" class="fill-white dark:fill-slate-800 stroke-slate-200 dark:stroke-slate-700 shadow-inner" stroke-width="2" />

        <!-- Inner Content: Month / Year & Navigator (matching the sheet) -->
        <g text-anchor="middle">
            <text x="{{ $cx }}" y="{{ $cy - 48 }}" class="fill-slate-500 dark:fill-slate-400 text-[13px] font-black uppercase tracking-widest font-sans">
                MONTH / YEAR
            </text>

            <line x1="{{ $cx - 65 }}" y1="{{ $cy - 38 }}" x2="{{ $cx + 65 }}" y2="{{ $cy - 38 }}" class="stroke-slate-300 dark:stroke-slate-600" stroke-width="1.5" />

            <text x="{{ $cx }}" y="{{ $cy - 12 }}" class="fill-slate-900 dark:fill-white text-2xl font-black font-sans">
                {{ ($currentLocale ?? 'ar') === 'en' ? ($monthNameEn ?? 'October') : ($monthNameAr ?? 'أكتوبر') }}
            </text>

            <text x="{{ $cx }}" y="{{ $cy + 20 }}" class="fill-brand-600 dark:fill-brand-400 text-xl font-bold font-mono">
                {{ $year }}
            </text>

            <line x1="{{ $cx - 65 }}" y1="{{ $cy + 30 }}" x2="{{ $cx + 65 }}" y2="{{ $cy + 30 }}" class="stroke-slate-300 dark:stroke-slate-600" stroke-width="1.5" />

            <!-- Progress Badge -->
            <g transform="translate({{ $cx - 45 }}, {{ $cy + 42 }})">
                <rect width="90" height="24" rx="12" class="fill-brand-50 dark:fill-brand-950/80 stroke-brand-300 dark:stroke-brand-700" stroke-width="1" />
                <text x="45" y="16" text-anchor="middle" class="fill-brand-700 dark:fill-brand-300 text-xs font-bold font-sans">
                    <tspan id="wheelProgressText">{{ $monthRate ?? 0 }}%</tspan> {{ ($currentLocale ?? 'ar') === 'en' ? 'Done' : 'إنجاز' }}
                </text>
            </g>
        </g>
    </svg>

    <!-- Floating Popover / Action Menu for clicked cell -->
    <div id="cellActionPopover" class="hidden absolute z-50 bg-white dark:bg-slate-800 rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-700 p-4 w-72 backdrop-blur-md transition-all duration-200">
        <div class="flex items-center justify-between pb-2 mb-3 border-b border-slate-100 dark:border-slate-700">
            <div>
                <p id="popoverHabitName" class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[190px]"></p>
                <p id="popoverDate" class="text-xs text-slate-500 dark:text-slate-400 font-mono"></p>
            </div>
            <button onclick="closePopover()" class="w-6 h-6 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-2 mb-3">
            <button type="button" onclick="submitCellStatus('completed')" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-teal-50 hover:bg-teal-100 dark:bg-teal-950/60 dark:hover:bg-teal-900/60 border border-teal-200 dark:border-teal-700 text-teal-700 dark:text-teal-300 text-xs font-bold transition">
                <i class="fa-solid fa-check"></i>
                <span>{{ __('mark_done') }}</span>
            </button>
            <button type="button" onclick="submitCellStatus('missed')" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-accent-50 hover:bg-accent-100 dark:bg-accent-950/60 dark:hover:bg-accent-900/60 border border-accent-200 dark:border-accent-700 text-accent-600 dark:text-accent-300 text-xs font-bold transition">
                <i class="fa-solid fa-xmark"></i>
                <span>{{ __('mark_missed') }}</span>
            </button>
            <button type="button" onclick="submitCellStatus('skipped')" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-700/60 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-300 text-xs font-bold transition">
                <i class="fa-solid fa-forward-step"></i>
                <span>{{ __('mark_skipped') }}</span>
            </button>
            <button type="button" onclick="submitCellStatus('clear')" class="flex items-center justify-center gap-1.5 px-3 py-2 rounded-xl bg-slate-50 hover:bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-slate-700 text-xs font-semibold transition">
                <i class="fa-solid fa-eraser"></i>
                <span>{{ __('clear_status') }}</span>
            </button>
        </div>

        <!-- Optional Note / Excuse field -->
        <div class="space-y-2">
            <div>
                <label class="block text-[11px] font-semibold text-slate-500 dark:text-slate-400 mb-1">ملاحظة / Note:</label>
                <input type="text" id="popoverNoteInput" placeholder="..." class="w-full px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-brand-500">
            </div>
            <div id="saveFeedback" class="text-[10px] text-center font-medium text-slate-400 h-3"></div>
        </div>
    </div>

    <!-- Live Tooltip on Hover -->
    <div id="wheelTooltip" class="hidden absolute pointer-events-none z-40 bg-slate-900/90 text-white rounded-xl px-3 py-2 text-xs shadow-xl backdrop-blur-sm max-w-xs transition-opacity duration-150">
        <div class="font-bold text-white flex items-center gap-1.5" id="ttHabit"></div>
        <div class="text-[11px] text-slate-300" id="ttDate"></div>
        <div class="text-[11px] font-semibold mt-1 flex items-center gap-1" id="ttStatus"></div>
        <div class="text-[10px] text-slate-300 italic mt-0.5" id="ttNote"></div>
    </div>
</div>

@push('scripts')
<script>
    let activeCell = null;

    function onCellHover(cell, event) {
        const tooltip = document.getElementById('wheelTooltip');
        const habitName = cell.getAttribute('data-habit-name');
        const date = cell.getAttribute('data-date');
        const day = cell.getAttribute('data-day');
        const status = cell.getAttribute('data-status');
        const note = cell.getAttribute('data-note');
        const excuse = cell.getAttribute('data-excuse');

        document.getElementById('ttHabit').textContent = habitName;
        document.getElementById('ttDate').textContent = `Day ${day} — ${date}`;

        let statusText = '⚪ لم يُسجل بعد';
        let statusClass = 'text-slate-300';
        if (status === 'completed') {
            statusText = '✓ تم الإنجاز';
            statusClass = 'text-teal-300';
        } else if (status === 'missed') {
            statusText = '✗ لم يتم';
            statusClass = 'text-accent-300';
        } else if (status === 'skipped') {
            statusText = '⏭ تخطي';
            statusClass = 'text-amber-300';
        }

        const ttStatus = document.getElementById('ttStatus');
        ttStatus.textContent = statusText;
        ttStatus.className = `text-[11px] font-bold mt-1 flex items-center gap-1 ${statusClass}`;

        const ttNote = document.getElementById('ttNote');
        if (note || excuse) {
            ttNote.textContent = note ? `Note: ${note}` : `Reason: ${excuse}`;
            ttNote.classList.remove('hidden');
        } else {
            ttNote.classList.add('hidden');
        }

        const wrapper = document.getElementById('habitWheelContainer').getBoundingClientRect();
        const mouseX = event.clientX - wrapper.left;
        const mouseY = event.clientY - wrapper.top;

        tooltip.style.left = `${mouseX + 15}px`;
        tooltip.style.top = `${mouseY + 15}px`;
        tooltip.classList.remove('hidden');
    }

    function onCellLeave() {
        document.getElementById('wheelTooltip').classList.add('hidden');
    }

    function onCellClick(cell, event) {
        event.stopPropagation();
        activeCell = cell;

        const habitName = cell.getAttribute('data-habit-name');
        const date = cell.getAttribute('data-date');
        const note = cell.getAttribute('data-note') || '';
        const excuse = cell.getAttribute('data-excuse') || '';

        document.getElementById('popoverHabitName').textContent = habitName;
        document.getElementById('popoverDate').textContent = date;
        document.getElementById('popoverNoteInput').value = note || excuse;
        document.getElementById('saveFeedback').textContent = '';

        const popover = document.getElementById('cellActionPopover');
        const wrapper = document.getElementById('habitWheelContainer').getBoundingClientRect();
        
        let x = event.clientX - wrapper.left;
        let y = event.clientY - wrapper.top;

        if (x + 290 > wrapper.width) {
            x = wrapper.width - 300;
        }
        if (x < 10) x = 10;
        if (y + 200 > wrapper.height) {
            y = wrapper.height - 210;
        }

        popover.style.left = `${x}px`;
        popover.style.top = `${y}px`;
        popover.classList.remove('hidden');
    }

    function closePopover() {
        const popover = document.getElementById('cellActionPopover');
        popover.classList.add('hidden');
        activeCell = null;
    }

    document.addEventListener('click', (e) => {
        const popover = document.getElementById('cellActionPopover');
        if (popover && !popover.contains(e.target)) {
            closePopover();
        }
    });

    function submitCellStatus(newStatus) {
        if (!activeCell) return;

        const habitId = activeCell.getAttribute('data-habit-id');
        const date = activeCell.getAttribute('data-date');
        const note = document.getElementById('popoverNoteInput').value;

        const feedback = document.getElementById('saveFeedback');
        feedback.textContent = 'Saving...';

        fetch('{{ route("habits.log-status") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                habit_id: habitId,
                date: date,
                status: newStatus,
                note: note,
                excuse_reason: (newStatus === 'missed') ? note : null
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                activeCell.classList.remove('status-empty', 'status-completed', 'status-missed', 'status-skipped');
                activeCell.classList.add(`status-${data.status}`);
                activeCell.setAttribute('data-status', data.status);
                activeCell.setAttribute('data-note', note);

                feedback.textContent = '✓ Saved!';
                feedback.className = 'text-[10px] text-center font-bold text-teal-500';

                const streakEl = document.getElementById(`streak-habit-${habitId}`);
                if (streakEl && data.streak !== undefined) {
                    streakEl.textContent = `${data.streak}d`;
                }

                const rateEl = document.getElementById(`rate-habit-${habitId}`);
                if (rateEl && data.monthly_rate !== undefined) {
                    rateEl.textContent = `${data.monthly_rate}%`;
                }

                showToast(`✓ (${date})`, 'success');

                setTimeout(() => {
                    closePopover();
                }, 400);
            }
        })
        .catch(err => {
            feedback.textContent = 'Error saving';
            feedback.className = 'text-[10px] text-center font-bold text-rose-500';
            showToast('Error', 'error');
        });
    }

    function handleDayClick(day) {
        const cells = document.querySelectorAll(`[data-day="${day}"]`);
        cells.forEach(c => {
            c.style.stroke = '#0d9488';
            c.style.strokeWidth = '3px';
            setTimeout(() => {
                c.style.stroke = '';
                c.style.strokeWidth = '';
            }, 1200);
        });
    }
</script>
@endpush
