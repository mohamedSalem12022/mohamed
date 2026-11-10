# دليل النشر الرسمي — MO Life OS (Production Deployment Guide)

يوثق هذا الدليل طرق نشر مشروع **MO Life OS**:
1. **النشر السحابي الفوري بنقرة واحدة (Railway / Render - موصى به ⭐)**.
2. **النشر التقليدي على سيرفر خاص (VPS / Ubuntu / Nginx)**.

---

## ⚡ الطريقة الأولى: النشر السحابي الفوري عبر Railway و Render (موصى بها)

تم تجهيز المشروع بملفات النشر التلقائية:
- [`railway.json`](file:///C:/xampp/htdocs/mo-life-os-main/railway.json) لمنصة **Railway**.
- [`render.yaml`](file:///C:/xampp/htdocs/mo-life-os-main/render.yaml) لمنصة **Render**.
- [`Dockerfile`](file:///C:/xampp/htdocs/mo-life-os-main/Dockerfile) مهيأ بـ PHP 8.3 و SQLite وكامل امتدادات ومكتبات المعالجة.
- [`.dockerignore`](file:///C:/xampp/htdocs/mo-life-os-main/.dockerignore) لتسريع الرفع وعزل الملفات المحلية.

### أ) خطوات النشر على Railway (الأسرع):
1. ارفع الكود إلى حسابك في **GitHub**.
2. توجّه إلى [Railway.app](https://railway.app) وسجل الدخول بحساب GitHub.
3. اضغط **New Project** -> **Deploy from GitHub repo** -> اختر مستودع المشروع.
4. يتعرف Railway تلقائياً على `railway.json` و `Dockerfile` ويبدأ البناء الفوري.
5. في إعدادات الخدمة (**Settings** -> **Networking**)، اضغط **Generate Domain** لتحصل على رابط موقعك المباشر.

### ب) خطوات النشر على Render:
1. ارفع الكود إلى **GitHub**.
2. توجّه إلى [Render.com](https://render.com) وسجل الدخول.
3. اضغط **New +** -> **Blueprint** -> حدد المستودع (سيقرأ `render.yaml` تلقائياً) -> اضغط **Apply**.
4. ستحصل على رابط مباشر مجاني مثل `https://mo-life-os.onrender.com`.

---

## 🖥️ الطريقة الثانية: النشر على سيرفر خاص VPS (Ubuntu / Nginx)

- **نظام التشغيل:** Linux (Ubuntu 22.04 LTS أو 24.04 LTS موصى به).
- **إصدار PHP:** **PHP 8.3** (أو أعلى) مع الامتدادات التالية:
  ```bash
  sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath php8.3-zip php8.3-intl php8.3-gd php8.3-cli
  ```
- **خادم الويب:** Nginx أو Apache مع تفعيل `mod_rewrite`.
- **قاعدة البيانات:** MySQL 8.0+ أو MariaDB 10.6+.
- **أدوات إضافية:** Composer 2.x, Git, Certbot (لتوليد شهادات SSL مجانية).

---

## 2. خطوات النشر خطوة بخطوة (Step-by-Step)

### الخطوة 1: سحب المشروع إلى السيرفر
```bash
cd /var/www
git clone <YOUR_PRIVATE_REPO_URL> mo-life-os
cd mo-life-os
```

### الخطوة 2: تثبيت حزم الاعتماديات الإنتاجية
```bash
composer install --no-dev --optimize-autoloader
```

### الخطوة 3: إعداد ملف البيئة `.env`
انسخ ملف الإعداد الإنتاجي:
```bash
cp .env.production.example .env
```
افتح الملف وعدّل المتغيرات:
```bash
nano .env
```
تأكد من ضبط القيم التالية:
```dotenv
APP_NAME="MO Life OS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_SECURE_COOKIE=true

GEMINI_API_KEY=your_gemini_api_key_here
```

ثم قم بتوليد مفتاح التشفير:
```bash
php artisan key:generate
```

### الخطوة 4: تهيئة قاعدة البيانات
اختر إحدى الطريقتين:

- **الطريقة (أ) - استيراد قاعدة البيانات الحالية بكافة البيانات:**
  ```bash
  mysql -u your_db_user -p your_production_db < database/mo_life_os_backup.sql
  ```
- **الطريقة (ب) - تشغيل التهجير من الصفر مع البذر الأولي:**
  ```bash
  php artisan migrate --force
  php artisan db:seed --force
  ```

### الخطوة 5: إنشاء رابط التخزين وضبط الصلاحيات
```bash
php artisan storage:link

sudo chown -R www-data:www-data /var/www/mo-life-os/storage /var/www/mo-life-os/bootstrap/cache
sudo chmod -R 775 /var/www/mo-life-os/storage /var/www/mo-life-os/bootstrap/cache
```

### الخطوة 6: تفعيل التخزين المؤقت وتسريع الأداء (Optimization)
```bash
php artisan optimize
php artisan view:cache
```

### الخطوة 7: تفعيل مجدول المهام التلقائي (Laravel Scheduler Cron)
لمعالجة التذكيرات والإشعارات المؤقتة بدقة، افتح الـ Crontab:
```bash
crontab -e
```
وأضف السطر التالي في نهاية الملف:
```bash
* * * * * cd /var/www/mo-life-os && php artisan schedule:run >> /dev/null 2>&1
```

---

## 3. إعداد خادم الويب (Nginx Configuration)

أنشئ ملف إعداد للموقع `/etc/nginx/sites-available/mo-life-os`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/mo-life-os/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

قم بتفعيل الموقع وإعادة تشغيل Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/mo-life-os /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4. تفعيل شهادة SSL المجانية عبر Let's Encrypt (إلزامية لميزة الصوت)

المتصفحات تتطلب بروتوكول HTTPS لتفعيل ميكروفون الأوامر الصوتية:
```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 5. سكربت التحديث التلقائي المستقبلي (Deploy Script)

عند رفع أي تحديثات برمجية جديدة مستقبلاً، يمكنك تشغيل هذا السكربت السريع:
```bash
#!/bin/bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize
php artisan view:cache
echo "MO Life OS deployed successfully!"
```
