class DashboardService {
  /// Formats date for display
  static String formatDate(dynamic date) {
    if (date is String) {
      try {
        final dateTime = DateTime.parse(date);
        final now = DateTime.now();
        final difference = now.difference(dateTime);

        if (difference.inDays == 0) {
          return 'امروز';
        } else if (difference.inDays == 1) {
          return 'دیروز';
        } else if (difference.inDays < 7) {
          return '${difference.inDays} روز پیش';
        } else {
          return '${dateTime.year}/${dateTime.month}/${dateTime.day}';
        }
      } catch (e) {
        return date;
      }
    }
    return date.toString();
  }

  /// Gets Jalali month name
  static String getJalaliMonthName(int month) {
    const months = [
      'فرو', 'ارد', 'خرد', 'تیر', 'مرد', 'شهر',
      'مهر', 'آبا', 'آذر', 'دی', 'بهم', 'اسف'
    ];
    return months[month - 1];
  }

  /// weekday should be 0-6 (0 = Saturday, 6 = Friday)
  static String getPersianDayName(int weekday) {
    const days = [
      'شنبه',
      'یکشنبه',
      'دوشنبه',
      'سه‌شنبه',
      'چهارشنبه',
      'پنج‌شنبه',
      'جمعه',
    ];
    final normalizedWeekday = (weekday % 7);
    return days[normalizedWeekday];
  }
}
