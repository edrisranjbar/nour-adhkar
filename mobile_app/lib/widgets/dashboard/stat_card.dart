import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';
import '../../utils/number_formatter.dart';

class StatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final int value;
  final Color color;
  final bool isDark;

  const StatCard({
    super.key,
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    // Create vibrant gradient colors based on the base color
    final iconBgColor = color.withOpacity(0.15);
    
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isDark
              ? [
                  color.withOpacity(0.1),
                  color.withOpacity(0.09),
                  AppTheme.darkBgTertiary,
                ]
              : [
                  color.withOpacity(0.12),
                  color.withOpacity(0.03),
                  Colors.white,
                ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: color.withOpacity(0.3),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.3),
            blurRadius: 12,
            spreadRadius: 0,
            offset: const Offset(0, 4),
          ),
          BoxShadow(
            color: Colors.black.withOpacity(isDark ? 0.3 : 0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Icon with glowing background
          Container(
            width: 56,
            height: 56,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  iconBgColor,
                  iconBgColor.withOpacity(0.5),
                ],
              ),
              boxShadow: [
                BoxShadow(
                  color: color.withOpacity(0.4),
                  blurRadius: 12,
                  spreadRadius: 2,
                ),
              ],
            ),
            child: Icon(
              icon,
              color: color,
              size: 28,
            ),
          ),
          const SizedBox(height: 10),
          // Value with gradient text effect
          ShaderMask(
            shaderCallback: (bounds) => LinearGradient(
              colors: [
                color,
                color.withOpacity(0.8),
              ],
            ).createShader(bounds),
            child: Text(
              NumberFormatter.formatNumber(value),
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.w800,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
                letterSpacing: -0.5,
              ),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: isDark
                  ? AppTheme.darkTextSecondary
                  : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

