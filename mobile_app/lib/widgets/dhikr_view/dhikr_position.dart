import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class DhikrPosition extends StatelessWidget {
  final int currentIndex;
  final int totalCount;

  const DhikrPosition({
    super.key,
    required this.currentIndex,
    required this.totalCount,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.end,
      children: [
        Text(
          'ذکر ${NumberFormatter.formatNumber(currentIndex + 1)} از ${NumberFormatter.formatNumber(totalCount)}',
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
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

