import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import 'dhikr_separator.dart';

class DhikrText extends StatelessWidget {
  final Map<String, dynamic> dhikr;
  final bool isDark;

  const DhikrText({
    super.key,
    required this.dhikr,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final prefix = dhikr['prefix'] ?? '';
    final arabicText = dhikr['arabic_text'] ?? '';
    final translation = dhikr['translation'] ?? '';
    final brandColor = isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary;

    return Column(
      children: [
        if (prefix.isNotEmpty) ...[
          Directionality(
            textDirection: TextDirection.rtl,
            child: Text(
              prefix,
              style: TextStyle(
                fontSize: 18,
                color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                fontFamily: AppTheme.fontArabic,
                height: 2.0,
              ),
              textAlign: TextAlign.start,
              overflow: TextOverflow.visible,
              softWrap: true,
            ),
          ),
        ],
        if (arabicText.isNotEmpty)
          Directionality(
            textDirection: TextDirection.rtl,
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical:8),
              child: Text(
                arabicText,
                style: TextStyle(
                  fontSize: 20,
                  height: 1.9,
                  color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                  fontFamily: AppTheme.fontArabic,
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
                overflow: TextOverflow.visible,
                softWrap: true,
              ),
            ),
          ),
        DhikrSeparator(brandColor: brandColor),
        if (translation.isNotEmpty)
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            child: Text(
              translation,
              style: TextStyle(
                fontSize: 16,
                height: 1.9,
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
                fontWeight: FontWeight.w400,
              ),
              textAlign: TextAlign.center,
              overflow: TextOverflow.visible,
              softWrap: true,
            ),
          ),
      ],
    );
  }
}

