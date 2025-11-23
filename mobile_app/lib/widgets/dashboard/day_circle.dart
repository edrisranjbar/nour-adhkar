import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class DayCircle extends StatelessWidget {
  final int day;
  final String monthName;
  final bool isCompleted;
  final bool isToday;
  final bool isDark;

  const DayCircle({
    super.key,
    required this.day,
    required this.monthName,
    required this.isCompleted,
    required this.isToday,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
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
        const SizedBox(height: 4),
        Text(
          monthName,
          style: TextStyle(
            fontSize: 10,
            color: isDark
                ? AppTheme.darkTextSecondary
                : AppTheme.textSecondary,
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
      ],
    );
  }
}

