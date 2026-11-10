<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: 'Segoe UI', Tahoma, Arial, sans-serif; direction: rtl; text-align: right;">
    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f1f5f9; padding: 40px 10px;">
        <tr>
            <td align="center">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05); overflow: hidden; border: 1px solid #e2e8f0;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0d9488, #14b8a6); padding: 30px; text-align: center; color: #ffffff;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">MO Habit Tracker</h1>
                            <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.9;">نظام إدارة المهام وتتبع العادات اليومية</p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 35px 30px; color: #334155; line-height: 1.8;">
                            <h2 style="margin: 0 0 15px; font-size: 20px; color: #0f172a; font-weight: 700;">
                                مرحباً {{ $user->name }} 👋
                            </h2>
                            <p style="margin: 0 0 20px; font-size: 14px; color: #475569;">
                                لقد تلقينا طلباً لاستعادة كلمة المرور الخاصة بحسابك على نظام <strong>MO Tracker</strong>. تم تعيين كلمة مرور جديدة لحسابك:
                            </p>

                            <!-- Password Box -->
                            <div style="background-color: #f0fdfa; border: 2px dashed #0d9488; border-radius: 12px; padding: 20px; text-align: center; margin: 25px 0;">
                                <span style="display: block; font-size: 12px; color: #0f766e; font-weight: 600; margin-bottom: 8px;">
                                    كلمة المرور الجديدة الخاصة بك:
                                </span>
                                <span style="font-size: 26px; font-weight: 900; font-family: monospace; color: #0d9488; letter-spacing: 2px;">
                                    {{ $newPassword }}
                                </span>
                            </div>

                            <p style="margin: 0 0 25px; font-size: 13px; color: #64748b;">
                                يمكنك استخدام كلمة المرور أعلاه لتسجيل الدخول فوراً، كما نوصي بتغييرها إلى كلمة المرور التي تناسبك من صفحة الإعدادات بعد تسجيل الدخول.
                            </p>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 30px 0 15px;">
                                <a href="{{ route('login') }}" style="display: inline-block; background-color: #0d9488; color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 12px; font-size: 14px; font-weight: bold; box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);">
                                    تسجيل الدخول إلى حسابك
                                </a>
                            </div>

                            <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 30px 0 20px;">

                            <p style="margin: 0; font-size: 11px; color: #94a3b8; text-align: center;">
                                إذا لم تكن أنت من طلب استعادة كلمة المرور، يرجى تسجيل الدخول وتغيير كلمة السر فوراً لحماية حسابك.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 18px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #e2e8f0;">
                            MO Habit Tracker & Task Manager &copy; 2026 — صُمم لتحقيق أعلى درجات الانضباط
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
