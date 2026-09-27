@php($persian = strtr($code, ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']))
<!doctype html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="color-scheme" content="light">
  <title>کد تأیید اذکار نور</title>
</head>
<body style="margin:0;padding:0;background:#FBF7ED;direction:rtl;text-align:right;font-family:Tahoma,'Segoe UI',Arial,sans-serif;">
  <span style="display:none;max-height:0;overflow:hidden;opacity:0;">کد تأیید شما: {{ $code }}</span>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#FBF7ED;padding:32px 12px;">
    <tr>
      <td align="center">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#FFFDF7;border:1px solid #E8DFC9;border-radius:20px;overflow:hidden;">
          <tr>
            <td style="background:#0E4B38;background-image:linear-gradient(135deg,#0E4B38,#2E6B4E);padding:28px 24px;text-align:center;">
              <div style="font-size:26px;font-weight:bold;color:#ffffff;letter-spacing:0;">اذکار نور</div>
              <div style="font-size:13px;color:#F2E3AF;margin-top:6px;">همراهی روشن برای هر روز</div>
            </td>
          </tr>
          <tr>
            <td style="padding:28px 24px 8px 24px;color:#222A20;font-size:15px;line-height:1.9;">
              <p style="margin:0 0 12px 0;">سلام {{ $name ?: 'دوست عزیز' }}،</p>
              <p style="margin:0;">برای تأیید ایمیل و فعال شدن حساب اذکار نور، این کد را در برنامه وارد کنید:</p>
            </td>
          </tr>
          <tr>
            <td align="center" style="padding:20px 24px;">
              <div dir="ltr" style="display:inline-block;background:#F7F0DF;border:1px solid #D1A52B;border-radius:16px;padding:16px 28px;font-size:36px;font-weight:bold;letter-spacing:12px;color:#0E4B38;font-family:'Courier New',monospace;">{{ $code }}</div>
              <div style="font-size:13px;color:#746F63;margin-top:10px;">({{ $persian }})</div>
            </td>
          </tr>
          <tr>
            <td style="padding:4px 24px 24px 24px;color:#746F63;font-size:13px;line-height:1.9;">
              <p style="margin:0 0 8px 0;">این کد تا {{ $expiresInMinutes }} دقیقه معتبر است و فقط یک بار قابل استفاده است.</p>
              <p style="margin:0;">اگر شما در اذکار نور ثبت‌نام نکرده‌اید، این ایمیل را نادیده بگیرید؛ کسی بدون این کد نمی‌تواند حساب را فعال کند.</p>
            </td>
          </tr>
          <tr>
            <td style="background:#F7F0DF;padding:16px 24px;text-align:center;color:#746F63;font-size:12px;line-height:1.8;">
              اذکار نور · پروژه متن‌باز و رایگان · <a href="https://adhkar.ir" style="color:#246B3D;text-decoration:none;">adhkar.ir</a>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
