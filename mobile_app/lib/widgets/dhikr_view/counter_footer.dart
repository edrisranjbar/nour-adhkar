import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import 'counter_button.dart';
import 'dhikr_position.dart';
import 'progress_details.dart';

class CounterFooter extends StatelessWidget {
  final Map<String, dynamic>? dhikr;
  final int currentIndex;
  final int totalCount;
  final int currentCount;
  final bool isDark;
  final AnimationController bumpController;
  final bool bumping;
  final VoidCallback onIncrement;

  const CounterFooter({
    super.key,
    required this.dhikr,
    required this.currentIndex,
    required this.totalCount,
    required this.currentCount,
    required this.isDark,
    required this.bumpController,
    required this.bumping,
    required this.onIncrement,
  });

  @override
  Widget build(BuildContext context) {
    if (dhikr == null) return const SizedBox.shrink();

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: isDark
                ? [const Color(0xFF1a1a1a), const Color(0xFF262626)]
                : [AppTheme.brandSecondary, AppTheme.brandSecondary.withOpacity(0.9)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
          border: Border(
            top: BorderSide(color: Colors.white.withOpacity(0.1), width: 1),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 10,
              offset: const Offset(0, -2),
            ),
          ],
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            DhikrPosition(
              currentIndex: currentIndex,
              totalCount: totalCount,
            ),
            CounterButton(
              dhikr: dhikr!,
              currentCount: currentCount,
              isDark: isDark,
              bumpController: bumpController,
              bumping: bumping,
              onIncrement: onIncrement,
            ),
            ProgressDetails(
              dhikr: dhikr!,
              currentCount: currentCount,
            ),
          ],
        ),
      ),
    );
  }
}

