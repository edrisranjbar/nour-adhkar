import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import 'dhikr_action_buttons.dart';

class DhikrCardHeader extends StatelessWidget {
  final String title;
  final bool isDark;
  final VoidCallback onCopyTap;

  const DhikrCardHeader({
    super.key,
    required this.title,
    required this.isDark,
    required this.onCopyTap,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (title.isNotEmpty)
          Expanded(
            child: Text(
              title,
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w700,
                color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                fontFamily: AppTheme.fontPrimary,
              ),
              overflow: TextOverflow.ellipsis,
              maxLines: 2,
              softWrap: true,
            ),
          )
        else
          const Spacer(),
        DhikrActionButtons(
          isDark: isDark,
          onCopyTap: onCopyTap,
        ),
      ],
    );
  }
}

