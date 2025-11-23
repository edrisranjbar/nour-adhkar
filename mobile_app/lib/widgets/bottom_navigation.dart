import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';

class BottomNavigation extends StatelessWidget {
  final int currentIndex;
  final Function(int) onTap;

  const BottomNavigation({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: onTap,
      type: BottomNavigationBarType.fixed,
      backgroundColor: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
      selectedItemColor: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
      unselectedItemColor: isDark ? const Color(0xFF888888) : const Color(0xFF777777),
      selectedFontSize: 14,
      unselectedFontSize: 12,
      selectedLabelStyle: const TextStyle(
        fontFamily: AppTheme.fontPrimary,
        fontWeight: FontWeight.w600,
      ),
      unselectedLabelStyle: const TextStyle(
        fontFamily: AppTheme.fontPrimary,
        fontWeight: FontWeight.w300,
      ),
      items: const [
        BottomNavigationBarItem(
          icon: FaIcon(FontAwesomeIcons.house, size: 20),
          activeIcon: FaIcon(FontAwesomeIcons.house, size: 24),
          label: 'خانه',
        ),
        BottomNavigationBarItem(
          icon: FaIcon(FontAwesomeIcons.handsPraying, size: 20),
          activeIcon: FaIcon(FontAwesomeIcons.handsPraying, size: 24),
          label: 'ذکرشمار',
        ),
        BottomNavigationBarItem(
          icon: FaIcon(FontAwesomeIcons.user, size: 20),
          activeIcon: FaIcon(FontAwesomeIcons.user, size: 24),
          label: 'داشبورد',
        ),
      ],
    );
  }
}

