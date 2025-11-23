<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>خوش آمدید به اذکار نور</title>
  <style>
    body { font-family: tahoma, Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 0; direction: rtl; text-align: right; }
    .container { max-width: 560px; margin: 24px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .header { background: #A79277; color: #fff; padding: 16px 20px; font-weight: 700; font-size: 18px; }
    .content { padding: 20px; color: #2C2A2A; line-height: 1.8; }
    .btn { display: inline-block; background: #9C8466; color: #fff !important; text-decoration: none; padding: 12px 18px; border-radius: 8px; margin-top: 12px; font-weight: bold; }
    .footer { padding: 16px 20px; font-size: 12px; color: #666; background: #fafafa; }
    .features { margin: 20px 0; }
    .features ul { list-style: none; padding: 0; }
    .features li { padding: 8px 0; padding-right: 20px; position: relative; }
    .features li:before { content: "✓"; position: absolute; right: 0; color: #9C8466; font-weight: bold; }
  </style>
  <!--[if mso]><style> body, table, td { font-family: Arial, sans-serif !important; } </style><![endif]-->
</head>
<body>
  <div class="container">
    <div class="header">خوش آمدید به اذکار نور</div>
    <div class="content">
      <p>سلام {{ $user->name ?? 'کاربر عزیز' }},</p>
      <p>از اینکه به خانواده اذکار نور پیوستید، بسیار خوشحالیم!</p>
      <p>حساب کاربری شما با موفقیت ایجاد شد و اکنون می‌توانید از تمامی ویژگی‌های برنامه استفاده کنید:</p>
      
      <div class="features">
        <ul>
          <li>دسترسی به مجموعه‌های کامل اذکار و ادعیه</li>
          <li>شمارشگر ذکر با قابلیت تنظیم هدف</li>
          <li>داشبورد شخصی و آمار پیشرفت</li>
          <li>لیست علاقه‌مندی‌ها</li>
          <li>نشان‌ها و دستاوردها</li>
          <li>حالت تاریک و روشن</li>
        </ul>
      </div>

      <p>برای شروع، وارد حساب کاربری خود شوید و از اذکار و ادعیه اسلامی بهره‌مند شوید.</p>
      
      <p style="text-align:center;">
        <a class="btn" href="{{ config('app.frontend_url', config('app.url')) }}" target="_blank" rel="noopener">ورود به برنامه</a>
      </p>

      <p>اگر سوالی دارید یا به کمک نیاز دارید، می‌توانید با پاسخ دادن به این ایمیل با ما در ارتباط باشید.</p>
    </div>
    <div class="footer">
      با تشکر از شما،<br>
      تیم اذکار نور
    </div>
  </div>
</body>
</html>

