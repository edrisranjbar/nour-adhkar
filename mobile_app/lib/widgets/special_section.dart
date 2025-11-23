import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../utils/number_formatter.dart';

class SpecialSection extends StatelessWidget {
  final String title;
  final int morningCount;
  final int nightCount;
  final VoidCallback? onMorningTap;
  final VoidCallback? onNightTap;

  const SpecialSection({
    super.key,
    this.title = 'اذکار ویژه روزانه',
    this.morningCount = 0,
    this.nightCount = 0,
    this.onMorningTap,
    this.onNightTap,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section Title
          _SectionTitle(text: title),
          const SizedBox(height: 16),
          // Special Cards Row
          Row(
            children: [
              Expanded(
                child: _SpecialCard(
                  title: 'اذکار صبحگاه',
                  icon: FontAwesomeIcons.sun,
                  count: morningCount,
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFFFFA751), Color(0xFFFFD54F)],
                  ),
                  textColor: Colors.black87,
                  onTap: onMorningTap,
                ),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: _SpecialCard(
                  title: 'اذکار شامگاه',
                  icon: FontAwesomeIcons.moon,
                  count: nightCount,
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF141E30), Color(0xFF243B55)],
                  ),
                  textColor: Colors.white,
                  onTap: onNightTap,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  final String text;

  const _SectionTitle({required this.text});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return Row(
      children: [
        Container(
          width: 4,
          height: 24,
          decoration: BoxDecoration(
            color: AppTheme.brandSecondary,
            borderRadius: BorderRadius.circular(2),
          ),
        ),
        const SizedBox(width: 12),
        Text(
          text,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w500,
            color: isDark ? Colors.white : const Color(0xFF333333),
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
      ],
    );
  }
}

class _SpecialCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final int count;
  final Gradient gradient;
  final Color textColor;
  final VoidCallback? onTap;

  const _SpecialCard({
    required this.title,
    required this.icon,
    required this.count,
    required this.gradient,
    required this.textColor,
    this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: 180,
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.1),
              blurRadius: 12,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: Stack(
          children: [
            // Badge
            Positioned(
              top: 16,
              left: 16,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${NumberFormatter.formatNumber(count)} ذکر',
                  style: TextStyle(
                    color: textColor,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
              ),
            ),
            // Content
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    icon,
                    size: 32,
                    color: textColor,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.w600,
                      color: textColor,
                      fontFamily: AppTheme.fontPrimary,
                    ),
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

