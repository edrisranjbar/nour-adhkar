import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class DailyVerse extends StatefulWidget {
  final String title;

  const DailyVerse({
    super.key,
    this.title = 'آیه روز',
  });

  @override
  State<DailyVerse> createState() => _DailyVerseState();
}

class _DailyVerseState extends State<DailyVerse> {
  late Map<String, String> _dailyVerse;

  @override
  void initState() {
    super.initState();
    _selectDailyVerse();
  }

  void _selectDailyVerse() {
    // Sample verses - in production, you'd load from API or local data
    final verses = [
      {
        'arabic': 'وَمَا خَلَقْتُ الْجِنَّ وَالْإِنسَ إِلَّا لِيَعْبُدُونِ',
        'translation': 'و من جن و انس را جز برای عبادت نیافریدم',
        'reference': 'ذاریات: آیه ۵۶',
      },
      {
        'arabic': 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
        'translation': 'پروردگارا، به ما در دنیا نیکی و در آخرت نیکی عطا کن و ما را از عذاب آتش نگاه دار',
        'reference': 'بقره: آیه ۲۰۱',
      },
      {
        'arabic': 'وَمَا تَوْفِيقِي إِلَّا بِاللَّهِ',
        'translation': 'و توفیق من جز به خدا نیست',
        'reference': 'هود: آیه ۸۸',
      },
    ];

    // Get today's date as seed for consistent daily verse
    final now = DateTime.now();
    final seed = now.year * 10000 + now.month * 100 + now.day;
    final index = seed % verses.length;

    setState(() {
      _dailyVerse = Map<String, String>.from(verses[index]);
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    // Gradient colors based on theme
    final gradientColors = isDark
        ? [AppTheme.darkBgSecondary, AppTheme.darkBgSecondary] // Solid color for dark theme
        : [AppTheme.brandSecondary, AppTheme.brandPrimary];
    
    // Text colors based on theme
    final textColor = isDark ? AppTheme.darkTextPrimary : Colors.white;
    final borderColor = isDark 
        ? AppTheme.darkTextPrimary.withOpacity(0.2)
        : Colors.white.withOpacity(0.2);
    
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: gradientColors,
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: (isDark ? AppTheme.brandSecondary : AppTheme.brandSecondary)
                .withOpacity(0.2),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Container(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Arabic text
            Text(
              _dailyVerse['arabic'] ?? '',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 16,
                height: 2.0,
                color: textColor,
                fontFamily: AppTheme.fontArabic,
              ),
              textDirection: TextDirection.rtl,
              overflow: TextOverflow.visible,
              softWrap: true,
            ),
            const SizedBox(height: 16),
            // Translation
            Text(
              _dailyVerse['translation'] ?? '',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 14,
                height: 1.8,
                color: textColor,
                fontFamily: AppTheme.fontPrimary,
              ),
              textDirection: TextDirection.rtl,
              overflow: TextOverflow.visible,
              softWrap: true,
            ),
            const SizedBox(height: 16),
            // Reference
            Container(
              padding: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(
                    color: borderColor,
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    _dailyVerse['reference'] ?? '',
                    style: TextStyle(
                      fontSize: 14,
                      color: textColor.withOpacity(0.9),
                      fontFamily: AppTheme.fontPrimary,
                    ),
                    textDirection: TextDirection.ltr,
                    overflow: TextOverflow.visible,
                    softWrap: true,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

