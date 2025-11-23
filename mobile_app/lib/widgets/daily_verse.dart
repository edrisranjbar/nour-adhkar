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
        'reference': 'ذاریات: 56',
      },
      {
        'arabic': 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الْآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
        'translation': 'پروردگارا، به ما در دنیا نیکی و در آخرت نیکی عطا کن و ما را از عذاب آتش نگاه دار',
        'reference': 'بقره: 201',
      },
      {
        'arabic': 'وَمَا تَوْفِيقِي إِلَّا بِاللَّهِ',
        'translation': 'و توفیق من جز به خدا نیست',
        'reference': 'هود: 88',
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
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppTheme.brandSecondary, AppTheme.brandPrimary],
        ),
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: AppTheme.brandSecondary.withOpacity(0.2),
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
              style: const TextStyle(
                fontSize: 22,
                height: 2.0,
                color: Colors.white,
                fontFamily: AppTheme.fontArabic,
              ),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 16),
            // Translation
            Text(
              _dailyVerse['translation'] ?? '',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                height: 1.8,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
              textDirection: TextDirection.rtl,
            ),
            const SizedBox(height: 16),
            // Reference
            Container(
              padding: const EdgeInsets.only(top: 12),
              decoration: BoxDecoration(
                border: Border(
                  top: BorderSide(
                    color: Colors.white.withOpacity(0.2),
                    width: 1,
                  ),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Text(
                    _dailyVerse['reference'] ?? '',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.white.withOpacity(0.9),
                      fontFamily: AppTheme.fontPrimary,
                    ),
                    textDirection: TextDirection.ltr,
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

