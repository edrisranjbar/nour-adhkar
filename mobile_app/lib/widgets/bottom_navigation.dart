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
    
    return Container(
      height: 70,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: isDark 
              ? AppTheme.uiShadow 
              : AppTheme.uiShadowLight,
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _buildNavItem(
            context,
            icon: FontAwesomeIcons.house,
            label: 'خانه',
            index: 0,
            isDark: isDark,
          ),
          _buildNavItem(
            context,
            icon: FontAwesomeIcons.handsPraying,
            label: 'تسبیح',
            index: 1,
            isDark: isDark,
          ),
          _buildNavItem(
            context,
            icon: FontAwesomeIcons.user,
            label: 'داشبورد',
            index: 2,
            isDark: isDark,
          ),
          _buildNavItem(
            context,
            icon: FontAwesomeIcons.gear,
            label: 'تنظیمات',
            index: 3,
            isDark: isDark,
          ),
        ],
      ),
    );
  }

  Widget _buildNavItem(
    BuildContext context, {
    required IconData icon,
    required String label,
    required int index,
    required bool isDark,
  }) {
    final isActive = currentIndex == index;
    final activeColor = isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary;
    final inactiveColor = isDark 
      ? const Color(0xFF888888) 
      : const Color(0xFF777777);

    return GestureDetector(
      onTap: () => onTap(index),
      child: Container(
        width: MediaQuery.of(context).size.width * 0.18,
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 250),
              transform: Matrix4.translationValues(
                0,
                isActive ? -3 : 0,
                0,
              ),
              child: Icon(
                icon,
                size: isActive ? 24 : 20,
                color: isActive ? activeColor : inactiveColor,
              ),
            ),
            const SizedBox(height: 6),
            AnimatedDefaultTextStyle(
              duration: const Duration(milliseconds: 250),
              style: TextStyle(
                fontSize: isActive ? 14 : 12,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w300,
                color: isActive ? activeColor : inactiveColor,
                fontFamily: AppTheme.fontPrimary,
              ),
              child: Text(label),
            ),
          ],
        ),
      ),
    );
  }
}

