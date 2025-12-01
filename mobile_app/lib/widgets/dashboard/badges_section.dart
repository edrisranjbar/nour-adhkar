import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';
import '../../services/dashboard_service.dart';
import 'achievement_badge.dart';

class BadgesSection extends StatefulWidget {
  final List<Map<String, dynamic>> badges;
  final Map<String, dynamic>? userStats;
  final int? streak;
  final bool isDark;

  const BadgesSection({
    super.key,
    required this.badges,
    required this.userStats,
    required this.streak,
    required this.isDark,
  });

  @override
  State<BadgesSection> createState() => _BadgesSectionState();
}

class _BadgesSectionState extends State<BadgesSection> {
  @override
  void initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    // If no badges from API, show default achievement badges
    final displayBadges = widget.badges.isNotEmpty
        ? widget.badges
        : DashboardService.getDefaultBadges(
            userStats: widget.userStats,
            streak: widget.streak,
          );

    return Container(
      padding: const EdgeInsets.all(20),
      color: widget.isDark ? AppTheme.darkBgPrimary : Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(
                    FontAwesomeIcons.trophy,
                    size: 20,
                    color: widget.isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'دستاوردها',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      color: widget.isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 3,
              crossAxisSpacing: 12,
              mainAxisSpacing: 16,
              childAspectRatio: 0.85,
            ),
            itemCount: displayBadges.length,
            itemBuilder: (context, index) {
              final badge = displayBadges[index];
              return AnimatedBadge(
                badge: badge,
                isDark: widget.isDark,
                delay: Duration(milliseconds: index * 100),
              );
            },
          ),
        ],
      ),
    );
  }
}

