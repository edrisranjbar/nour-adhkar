import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class BadgesSection extends StatelessWidget {
  final bool isDark;

  const BadgesSection({
    super.key,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      color: isDark ? AppTheme.darkBgPrimary : Colors.white,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(
                    FontAwesomeIcons.trophy,
                    size: 20,
                    color: isDark
                        ? AppTheme.darkBrandPrimary.withOpacity(0.5)
                        : AppTheme.brandPrimary.withOpacity(0.5),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'دستاوردها',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      color: isDark
                          ? AppTheme.darkTextPrimary.withOpacity(0.5)
                          : AppTheme.textPrimary.withOpacity(0.5),
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 16),
          // Coming Soon Overlay
          Container(
            height: 200, // Approximate height of the original grid
            decoration: BoxDecoration(
              color: isDark
                  ? AppTheme.darkBgSecondary.withOpacity(0.3)
                  : Colors.grey.shade100.withOpacity(0.5),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isDark
                    ? Colors.white.withOpacity(0.1)
                    : Colors.grey.shade300.withOpacity(0.5),
                width: 1,
              ),
            ),
            child: Stack(
              children: [
                // Subtle background pattern
                Positioned.fill(
                  child: Opacity(
                    opacity: 0.1,
                    child: GridView.builder(
                      physics: const NeverScrollableScrollPhysics(),
                      gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 3,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 16,
                      ),
                      itemCount: 6,
                      itemBuilder: (context, index) {
                        return Container(
                          decoration: BoxDecoration(
                            color: isDark
                                ? Colors.white.withOpacity(0.05)
                                : Colors.grey.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                          ),
                        );
                      },
                    ),
                  ),
                ),
                // Coming Soon Content
                Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        FontAwesomeIcons.tools,
                        size: 32,
                        color: isDark
                            ? Colors.white.withOpacity(0.4)
                            : Colors.grey.shade400,
                      ),
                      const SizedBox(height: 12),
                      Text(
                        'در حال توسعه',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w600,
                          color: isDark
                              ? Colors.white.withOpacity(0.6)
                              : Colors.grey.shade500,
                          fontFamily: AppTheme.fontPrimary,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        'به زودی قابل دسترسی خواهد بود',
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? Colors.white.withOpacity(0.4)
                              : Colors.grey.shade400,
                          fontFamily: AppTheme.fontPrimary,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

