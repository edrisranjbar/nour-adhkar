import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class CompletionBadge extends StatelessWidget {
  final bool isDark;

  const CompletionBadge({
    super.key,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final brandColor = isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
      decoration: BoxDecoration(
        color: brandColor.withOpacity(0.15),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: brandColor, width: 1.5),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(FontAwesomeIcons.checkCircle, color: brandColor, size: 18),
          const SizedBox(width: 8),
          Text(
            'تکمیل شد',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: brandColor,
              fontFamily: AppTheme.fontPrimary,
            ),
            overflow: TextOverflow.visible,
            softWrap: true,
          ),
        ],
      ),
    );
  }
}

