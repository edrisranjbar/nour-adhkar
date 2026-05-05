import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import 'dhikr_text.dart';
import 'completion_badge.dart';

class DhikrCard extends StatelessWidget {
  final Map<String, dynamic> dhikr;
  final bool isDark;
  final int currentCount;
  final VoidCallback onCopyTap;

  const DhikrCard({
    super.key,
    required this.dhikr,
    required this.isDark,
    required this.currentCount,
    required this.onCopyTap,
  });

  @override
  Widget build(BuildContext context) {
    final targetCount = dhikr['count'] ?? 33;
    final isCompleted = currentCount >= targetCount;
    final brandColor = isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgSecondary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 16,
            offset: const Offset(0, 8),
            spreadRadius: 0,
          ),
          BoxShadow(
            color: brandColor.withOpacity(0.1),
            blurRadius: 24,
            offset: const Offset(0, 4),
            spreadRadius: -5,
          ),
        ],
        border: Border.all(
          color: isCompleted ? brandColor.withOpacity(0.3) : Colors.transparent,
          width: 2,
        ),
      ),
      child: Column(
        children: [
          DhikrText(
            dhikr: dhikr,
            isDark: isDark,
          ),
          if (isCompleted) ...[
            const SizedBox(height: 20),
            CompletionBadge(isDark: isDark),
          ],
        ],
      ),
    );
  }
}

