<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>بازیابی رمز عبور</title>
  <style>
    body { font-family: tahoma, Arial, sans-serif; background: #f7f7f7; margin: 0; padding: 0; direction: rtl; text-align: right; }
    .container { max-width: 560px; margin: 24px auto; background: #ffffff; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); overflow: hidden; }
    .header { background: #A79277; color: #fff; padding: 16px 20px; font-weight: 700; font-size: 18px; }
    .content { padding: 20px; color: #2C2A2A; line-height: 1.8; }
    .btn { display: inline-block; background: #9C8466; color: #fff !important; text-decoration: none; padding: 12px 18px; border-radius: 8px; margin-top: 12px; font-weight: bold; }
    .footer { padding: 16px 20px; font-size: 12px; color: #666; background: #fafafa; }
  </style>
  <!--[if mso]><style> body, table, td { font-family: Arial, sans-serif !important; } </style><![endif]-->
  <!-- Use web-safe fonts for email clients -->
</head>
<body>
  <div class="container">
    <div class="header">درخواست بازیابی رمز عبور</div>
    <div class="content">
      <p>سلام {{ $user->name ?? 'کاربر عزیز' }},</p>
      <p>برای بازیابی رمز عبور خود، روی دکمه زیر کلیک کنید. اگر شما این درخواست را ارسال نکرده‌اید، این ایمیل را نادیده بگیرید.</p>
      <p style="text-align:center;">
        <a class="btn" href="{{ $resetUrl }}" target="_blank" rel="noopener">بازیابی رمز عبور</a>
      </p>
      <p>اگر دکمه بالا کار نکرد، می‌توانید از لینک زیر استفاده کنید:</p>
      <p style="direction:ltr; word-break: break-all;">{{ $resetUrl }}</p>
    </div>
    <div class="footer">
      این لینک تا ۶۰ دقیقه معتبر است. اگر مشکلی داشتید، می‌توانید با پاسخ دادن به این ایمیل با ما در ارتباط باشید.
    </div>
  </div>
</body>
</html>


