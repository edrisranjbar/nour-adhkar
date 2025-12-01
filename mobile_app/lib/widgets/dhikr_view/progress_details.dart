import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class ProgressDetails extends StatelessWidget {
  final Map<String, dynamic> dhikr;
  final int currentCount;

  const ProgressDetails({
    super.key,
    required this.dhikr,
    required this.currentCount,
  });

  @override
  Widget build(BuildContext context) {
    final targetCount = dhikr['count'] ?? 33;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'پیشرفت',
          style: TextStyle(
            fontSize: 11,
            color: Colors.white.withOpacity(0.7),
            fontFamily: AppTheme.fontPrimary,
          ),
          overflow: TextOverflow.visible,
          softWrap: true,
        ),
        const SizedBox(height: 2),
        Text(
          '${NumberFormatter.formatNumber(currentCount)}/${NumberFormatter.formatNumber(targetCount)}',
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: Colors.white,
            fontFamily: AppTheme.fontPrimary,
          ),
          overflow: TextOverflow.visible,
          softWrap: true,
        ),
      ],
    );
  }
}
