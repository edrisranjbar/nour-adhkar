import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';
import '../../services/dashboard_service.dart';

class RecentActivitiesSection extends StatelessWidget {
  final List<Map<String, dynamic>> activities;
  final bool isDark;

  const RecentActivitiesSection({
    super.key,
    required this.activities,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      color: isDark ? AppTheme.darkBgPrimary : Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'فعالیت‌های اخیر',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: isDark
                  ? AppTheme.darkBrandPrimary
                  : AppTheme.brandPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 12),
          ...activities.take(5).map((activity) {
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkBgTertiary : Colors.grey[50],
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isDark ? AppTheme.darkBgSecondary : Colors.grey[200]!,
                  width: 1,
                ),
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppTheme.darkBrandSecondary
                          : AppTheme.brandSecondary,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      activity['type'] == 'dhikr'
                          ? FontAwesomeIcons.book
                          : activity['type'] == 'contribution'
                              ? FontAwesomeIcons.handHoldingHeart
                              : FontAwesomeIcons.gift,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          activity['description'] ?? 'فعالیت',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: isDark
                                ? AppTheme.darkTextPrimary
                                : AppTheme.textPrimary,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                        if (activity['date'] != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            DashboardService.formatDate(activity['date']),
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark
                                  ? AppTheme.darkTextSecondary
                                  : AppTheme.textSecondary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
        ],
      ),
    );
  }
}

