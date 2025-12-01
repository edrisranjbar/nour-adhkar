import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class DayCircle extends StatelessWidget {
  final int day;
  final String monthName;
  final String? dayName;
  final bool isCompleted;
  final bool isToday;
  final bool isDark;

  const DayCircle({
    super.key,
    required this.day,
    required this.monthName,
    this.dayName,
    required this.isCompleted,
    required this.isToday,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Day name label
        if (dayName != null)
          Text(
            dayName!,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w500,
              color: isDark
                  ? AppTheme.darkTextSecondary
                  : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
        if (dayName != null) const SizedBox(height: 6),
        Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: isCompleted
                ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                : (isDark ? AppTheme.darkBgSecondary : AppTheme.bgTertiary),
            shape: BoxShape.circle,
            border: isToday
                ? Border.all(
                    color: isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary,
                    width: 2,
                  )
                : null,
          ),
          child: Center(
            child: isCompleted
                ? const Icon(
                    Icons.check,
                    color: Colors.white,
                    size: 24,
                  )
                : Text(
                    NumberFormatter.formatNumber(day),
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: isDark
                          ? AppTheme.darkTextSecondary
                          : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
          ),
        ),
      ],
    );
  }
}

