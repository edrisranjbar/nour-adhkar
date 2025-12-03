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
    final totalDhikrs = userStats?['total_adhkar_completed'] ?? 0;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
      color: isDark ? AppTheme.darkBgPrimary : Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
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
              const SizedBox(width: 8),
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.heart,
                  label: 'امتیاز قلب',
                  value: heartScore ?? 0,
                  color: Colors.red,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: StatCard(
                  icon: FontAwesomeIcons.book,
                  label: 'کل اذکار',
                  value: totalDhikrs,
                  color: AppTheme.brandPrimary,
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

