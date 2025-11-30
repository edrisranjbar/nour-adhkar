import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';
import 'stat_card.dart';

class StatsSection extends StatelessWidget {
  final Map<String, dynamic>? userStats;
  final int? heartScore;
  final int? streak;
  final bool isDark;

  const StatsSection({
    super.key,
    required this.userStats,
    required this.heartScore,
    required this.streak,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final totalDhikrs = userStats?['total_dhikrs'] ?? 0;
    final todayCount = userStats?['today_count'] ?? 0;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'آمار شما',
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
          Row(
            children: [
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.fire,
                  label: 'استریک',
                  value: streak ?? 0,
                  color: Colors.orange,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.heart,
                  label: 'امتیاز قلب',
                  value: heartScore ?? 0,
                  color: Colors.red,
                  isDark: isDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.book,
                  label: 'کل اذکار',
                  value: totalDhikrs,
                  color: AppTheme.brandPrimary,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.calendarDay,
                  label: 'امروز',
                  value: todayCount,
                  color: AppTheme.brandSecondary,
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

