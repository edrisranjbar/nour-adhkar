class DashboardService {
  /// Normalizes badges from API to ensure proper types
  static List<Map<String, dynamic>> normalizeBadges(
    List<Map<String, dynamic>> badges,
  ) {
    return badges.map((badge) {
      final unlocked = badge['unlocked'];
      bool unlockedBool = false;
      if (unlocked is bool) {
        unlockedBool = unlocked;
      } else if (unlocked is int) {
        unlockedBool = unlocked != 0;
      } else if (unlocked is String) {
        unlockedBool = unlocked == '1' || unlocked.toLowerCase() == 'true';
      }

      final progress = badge['progress'];
      double progressDouble = 0.0;
      if (progress is double) {
        progressDouble = progress;
      } else if (progress is int) {
        progressDouble = progress.toDouble();
      } else if (progress is String) {
        progressDouble = double.tryParse(progress) ?? 0.0;
      }

      return {
        ...badge,
        'unlocked': unlockedBool,
        'progress': progressDouble,
      };
    }).toList();
  }

  /// Gets default badges based on user stats
  static List<Map<String, dynamic>> getDefaultBadges({
    required Map<String, dynamic>? userStats,
    required int? streak,
  }) {
    // Helper function to safely get int from stats
    int getIntFromStats(String key) {
      final value = userStats?[key];
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      if (value is double) return value.toInt();
      return 0;
    }

    final totalDhikrs = getIntFromStats('total_dhikrs');
    final currentStreak = streak ?? 0;

    return [
      {
        'name': 'آغازگر',
        'description': 'اولین ذکر خود را تمام کنید',
        'icon': 'star',
        'rarity': 'bronze',
        'unlocked': totalDhikrs > 0,
        'progress': totalDhikrs >= 1 ? 1.0 : 0.0,
      },
      {
        'name': 'مداوم',
        'description': '۷ روز پشت سر هم ذکر بخوانید',
        'icon': 'fire',
        'rarity': 'silver',
        'unlocked': currentStreak >= 7,
        'progress': (currentStreak / 7).clamp(0.0, 1.0),
      },
      {
        'name': 'صبحگاه',
        'description': 'اذکار صبح را ۳۰ بار بخوانید',
        'icon': 'sun',
        'rarity': 'gold',
        'unlocked': false,
        'progress': 0.5,
      },
    ];
  }

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

  /// Gets Persian day name
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
    return days[weekday];
  }
}

