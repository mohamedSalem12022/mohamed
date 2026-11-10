<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserSettings;
use App\Models\Habit;
use App\Models\HabitLog;
use App\Models\Category;
use App\Models\Task;
use App\Models\DailyReflection;
use App\Models\ExcuseLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Primary User (Mohamed Salem)
        $user = User::firstOrCreate(
            ['email' => 'mohamedsalim809010@gmail.com'],
            [
                'name' => 'Mohamed Salem',
                'password' => '$2y$12$4X2vRpmhlbAT1rQiiyt7Q.fYFXN2ZM/b.GPeAnKx.sfwXYPbgYBda',
                'is_admin' => true,
            ]
        );

        // Secondary User
        User::firstOrCreate(
            ['email' => 'mo.design12025@gmail.com'],
            [
                'name' => 'mohamed salem',
                'password' => '$2y$12$rQ/Rkxwy44YpGGKcHL/2sOH4T9ekZZC3DVT2JTqonjkB.2OCmJ4ym',
                'is_admin' => false,
            ]
        );

        // Fallback Demo Account
        User::firstOrCreate(
            ['email' => 'mo@tracker.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        // 2. User Settings
        UserSettings::updateOrCreate(
            ['user_id' => $user->id],
            [
                'theme' => 'light',
                'language' => 'ar',
                'primary_color' => '#0d9488',
                'accent_color' => '#14b8a6',
                'week_starts_on' => 6,
            ]
        );

        // 3. Categories
        $categories = [
            ['name' => 'عادات وروتين', 'color' => '#0d9488', 'icon' => 'fa-clock'],
            ['name' => 'صحة ولياقة', 'color' => '#10b981', 'icon' => 'fa-heartbeat'],
            ['name' => 'عمل ومشاريع', 'color' => '#3b82f6', 'icon' => 'fa-briefcase'],
            ['name' => 'تطوير شخصي', 'color' => '#8b5cf6', 'icon' => 'fa-brain'],
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = Category::firstOrCreate(
                ['user_id' => $user->id, 'name' => $cat['name']],
                ['color' => $cat['color'], 'icon' => $cat['icon']]
            );
        }

        // 4. Habits (Inspired directly by user's uploaded tracker image)
        $habitsData = [
            [
                'name' => 'الاستيقاظ 6:00 الصبح',
                'name_en' => 'Wake up 6:00',
                'description' => 'الاستيقاظ المبكر لبداية يوم مليء بالنشاط والتركيز',
                'color' => '#0d9488',
                'icon' => 'fa-sun',
                'target_days_per_week' => 7,
                'order' => 1,
            ],
            [
                'name' => 'صلاة الفجر',
                'name_en' => 'Pray Fajr',
                'description' => 'أداء صلاة الفجر في وقتها لبداية روحانية مباركة',
                'color' => '#06b6d4',
                'icon' => 'fa-mosque',
                'target_days_per_week' => 7,
                'order' => 2,
            ],
            [
                'name' => 'الأكل الصحي',
                'name_en' => 'Eat healthy',
                'description' => 'تجنب السكريات والأطعمة المصنعة والالتزام بوجبات متوازنة',
                'color' => '#10b981',
                'icon' => 'fa-apple-whole',
                'target_days_per_week' => 7,
                'order' => 3,
            ],
            [
                'name' => 'شرب 2 لتر ماء',
                'name_en' => 'Drink 2L water',
                'description' => 'الحفاظ على رطوبة الجسم والطاقة طوال اليوم',
                'color' => '#3b82f6',
                'icon' => 'fa-glass-water',
                'target_days_per_week' => 7,
                'order' => 4,
            ],
            [
                'name' => 'ساعة رياضة',
                'name_en' => '1hr Training',
                'description' => 'تمارين مقاومة وكارديو لبناء الانضباط واللياقة',
                'color' => '#f59e0b',
                'icon' => 'fa-dumbbell',
                'target_days_per_week' => 6,
                'order' => 5,
            ],
            [
                'name' => 'ساعة بدون سوشيال ميديا',
                'name_en' => '1hr No Scroll',
                'description' => 'فترة تركيز وهدوء ذهني عميق بدون تشتت الهواتف',
                'color' => '#8b5cf6',
                'icon' => 'fa-ban',
                'target_days_per_week' => 7,
                'order' => 6,
            ],
            [
                'name' => 'النوم قبل 11:00',
                'name_en' => 'Sleep before 11:00',
                'description' => 'النوم الكافي المبكر لراحة الجسد والاستيقاظ بنشاط',
                'color' => '#ec4899',
                'icon' => 'fa-moon',
                'target_days_per_week' => 7,
                'order' => 7,
            ],
        ];

        $createdHabits = [];
        foreach ($habitsData as $data) {
            $createdHabits[] = Habit::firstOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                $data
            );
        }

        // 5. Seed Habit Logs for Current Month
        $now = Carbon::now();
        $daysToSeed = min($now->day, 26);

        foreach ($createdHabits as $idx => $habit) {
            for ($day = 1; $day <= $daysToSeed; $day++) {
                $date = Carbon::createFromDate($now->year, $now->month, $day)->toDateString();
                
                // Emulate reference image: mostly completed (turquoise), occasional missed (orange)
                // e.g. habit 1 missed on day 4, habit 3 missed on day 10, habit 5 missed on day 15, etc.
                $isMissed = (($idx == 0 && $day == 4) || ($idx == 2 && $day == 10) || ($idx == 4 && $day == 16) || ($idx == 6 && $day == 24));
                $status = $isMissed ? 'missed' : 'completed';

                HabitLog::updateOrCreate(
                    ['habit_id' => $habit->id, 'log_date' => $date],
                    [
                        'user_id' => $user->id,
                        'status' => $status,
                        'note' => $isMissed ? 'شعرت بالإرهاق بعد يوم عمل طويل' : 'تم الإنجاز بحمد الله',
                        'excuse_reason' => $isMissed ? 'التعب والإرهاق' : null,
                        'completed_at' => $isMissed ? null : Carbon::parse($date)->setHour(8 + $idx)->setMinute(15),
                    ]
                );
            }
        }

        // 6. Tasks
        $tasksData = [
            [
                'title' => 'مراجعة أهداف الشهر والتخطيط للأسبوع القادم',
                'description' => 'جلسة تقييم أسبوعية لمراجعة دائرة العادات ونسبة الالتزام',
                'category_id' => $createdCategories[0]->id,
                'due_date' => $now->toDateString(),
                'due_time' => '18:00:00',
                'priority' => 'high',
                'status' => 'pending',
                'recurrence' => 'weekly',
            ],
            [
                'title' => 'جلسة تمرين أرجل وكارديو لمدة 45 دقيقة',
                'description' => 'التركيز على تمارين السكوات والإطالة',
                'category_id' => $createdCategories[1]->id,
                'due_date' => $now->toDateString(),
                'due_time' => '17:00:00',
                'priority' => 'medium',
                'status' => 'completed',
                'recurrence' => 'none',
                'completed_at' => Carbon::now()->subHours(2),
            ],
            [
                'title' => 'قراءة 20 صفحة من كتاب بناء العادات الذرية',
                'description' => 'فصل: كيف تجعل العادة جذابة وسهلة التنفيذ',
                'category_id' => $createdCategories[3]->id,
                'due_date' => $now->toDateString(),
                'due_time' => '21:00:00',
                'priority' => 'medium',
                'status' => 'pending',
                'recurrence' => 'daily',
            ],
            [
                'title' => 'شرب 2 لتر ماء وتوزيعها على مدار اليوم',
                'description' => 'زجاجة ماء بجوار المكتب طوال فترات العمل',
                'category_id' => $createdCategories[1]->id,
                'due_date' => $now->toDateString(),
                'due_time' => '20:00:00',
                'priority' => 'high',
                'status' => 'completed',
                'recurrence' => 'daily',
                'completed_at' => Carbon::now()->subMinutes(45),
            ],
            [
                'title' => 'إنهاء تقرير المشروع الأسبوعي وتسليمه',
                'description' => 'مراجعة الأرقام النهائية وإرسال التقرير للمدير',
                'category_id' => $createdCategories[2]->id,
                'due_date' => $now->copy()->addDay()->toDateString(),
                'due_time' => '14:00:00',
                'priority' => 'high',
                'status' => 'pending',
                'recurrence' => 'none',
            ],
        ];

        foreach ($tasksData as $tData) {
            Task::create(array_merge(['user_id' => $user->id], $tData));
        }

        // 7. Daily Reflections (matching the reference image right column)
        DailyReflection::updateOrCreate(
            ['user_id' => $user->id, 'date' => $now->toDateString()],
            [
                'notes' => "I Finally quit scrolling at night! 80% discipline score today.\nشعرت بطاقة عالية جداً بعد الالتزام برياضة الصباح والأكل الصحي.",
                'best_win' => 'الاستيقاظ في الموعد 6:00 صباحاً بدون أي تأجيل للمنبه',
                'lessons_learned' => 'الانضباط هو فعل ما يجب فعله حتى عندما لا تشعر بالرغبة في ذلك',
                'rating' => 5,
                'tomorrow_improvements' => 'تحضير ملابس الرياضة وزجاجة الماء من المساء قبل النوم',
            ]
        );

        // 8. Excuse Logs (matching "Excuses I overcome" from reference image)
        $excuses = [
            [
                'habit_id' => $createdHabits[0]->id,
                'date' => $now->copy()->subDays(4)->toDateString(),
                'excuse_type' => 'fatigue',
                'excuse_details' => "I need time / أحتاج وقتاً إضافياً للنوم بسبب السهر",
                'how_to_overcome' => 'النوم قبل الساعة 11 مساءً ووضع الهاتف خارج غرفة النوم',
                'overcame' => true,
            ],
            [
                'habit_id' => $createdHabits[4]->id,
                'date' => $now->copy()->subDays(2)->toDateString(),
                'excuse_type' => 'no_motivation',
                'excuse_details' => "I don't feel like it / لا أشعر بالرغبة في أداء التمرين اليوم",
                'how_to_overcome' => 'تطبيق قاعدة الـ 5 دقائق: البدء بـ 5 دقائق فقط وبعدها يستمر النشاط تلقائياً',
                'overcame' => true,
            ],
            [
                'habit_id' => $createdHabits[5]->id,
                'date' => $now->copy()->subDays(1)->toDateString(),
                'excuse_type' => 'distraction',
                'excuse_details' => "I can't / لا أستطيع التوقف عن متابعة الإشعارات",
                'how_to_overcome' => 'تفعيل وضع عدم الإزعاج وتحديد ساعات محددة فقط لتصفح السوشيال ميديا',
                'overcame' => false,
            ],
        ];

        foreach ($excuses as $excuse) {
            ExcuseLog::create(array_merge(['user_id' => $user->id], $excuse));
        }

        // 9. Financial Categories (Phase 3)
        \App\Models\FinancialCategory::seedDefaultsForUser($user->id);
    }
}
