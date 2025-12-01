import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:shamsi_date/shamsi_date.dart';
import '../../theme/app_theme.dart';
import '../../services/dashboard_service.dart';
import 'day_circle.dart';

class StreakCalendar extends StatelessWidget {
  final Map<String, dynamic>? userStats;
  final bool isDark;

  const StreakCalendar({
    super.key,
    required this.userStats,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final completedDates = userStats?['completed_dates'] as List? ?? [];
    final today = Jalali.now();

    // Get last 7 days
    final last7Days = List.generate(7, (index) {
      return today.addDays(-(6 - index));
    });

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                FontAwesomeIcons.calendarDays,
                size: 20,
                color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
              ),
              const SizedBox(width: 8),
              Text(
                '۷ روز گذشته',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: last7Days.map((jalaliDate) {
              final dateStr = '${jalaliDate.year}-${jalaliDate.month.toString().padLeft(2, '0')}-${jalaliDate.day.toString().padLeft(2, '0')}';
              final isCompleted = completedDates.contains(dateStr);
              final isToday = jalaliDate.day == today.day &&
                  jalaliDate.month == today.month &&
                  jalaliDate.year == today.year;
              
              // Get weekday (0 = Saturday, 6 = Friday)
              // shamsi_date returns 1-7, we need to convert to 0-6
              final weekday = (jalaliDate.weekDay - 1) % 7;
              final dayName = DashboardService.getPersianDayName(weekday);

              return DayCircle(
                day: jalaliDate.day,
                monthName: DashboardService.getJalaliMonthName(jalaliDate.month),
                dayName: dayName,
                isCompleted: isCompleted,
                isToday: isToday,
                isDark: isDark,
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}

