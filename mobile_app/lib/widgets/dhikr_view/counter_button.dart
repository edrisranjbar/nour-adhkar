import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class CounterButton extends StatelessWidget {
  final Map<String, dynamic> dhikr;
  final int currentCount;
  final bool isDark;
  final AnimationController bumpController;
  final bool bumping;
  final VoidCallback onIncrement;

  const CounterButton({
    super.key,
    required this.dhikr,
    required this.currentCount,
    required this.isDark,
    required this.bumpController,
    required this.bumping,
    required this.onIncrement,
  });

  @override
  Widget build(BuildContext context) {
    final targetCount = dhikr['count'] ?? 33;
    final progress = targetCount > 0 ? (currentCount / targetCount).clamp(0.0, 1.0) : 0.0;
    final isCompleted = currentCount >= targetCount;
    final brandColor = isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary;

    return GestureDetector(
      onTap: onIncrement,
      child: AnimatedBuilder(
        animation: bumpController,
        builder: (context, child) {
          final scale = bumping ? 1.12 : 1.0;
          return Transform.scale(scale: scale, child: child);
        },
        child: Stack(
          alignment: Alignment.center,
          children: [
            SizedBox(
              width: 80,
              height: 80,
              child: CircularProgressIndicator(
                value: progress,
                strokeWidth: 4,
                backgroundColor: Colors.white.withOpacity(0.2),
                valueColor: AlwaysStoppedAnimation<Color>(brandColor),
                strokeCap: StrokeCap.round,
              ),
            ),
            Container(
              width: 70,
              height: 70,
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: isCompleted
                      ? [brandColor.withOpacity(0.3), brandColor.withOpacity(0.2)]
                      : [Colors.white, Colors.white.withOpacity(0.95)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                shape: BoxShape.circle,
                border: Border.all(
                  color: isCompleted ? brandColor.withOpacity(0.5) : Colors.white.withOpacity(0.3),
                  width: 2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: brandColor.withOpacity(0.3),
                    blurRadius: 15,
                    offset: const Offset(0, 4),
                    spreadRadius: -2,
                  ),
                ],
              ),
              child: Center(
                child: Text(
                  NumberFormatter.formatNumber(currentCount),
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.w800,
                    color: brandColor,
                    fontFamily: AppTheme.fontPrimary,
                    letterSpacing: -1,
                  ),
                  overflow: TextOverflow.visible,
                  softWrap: false,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

