import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class DhikrActionButtons extends StatelessWidget {
  final bool isDark;
  final VoidCallback onCopyTap;

  const DhikrActionButtons({
    super.key,
    required this.isDark,
    required this.onCopyTap,
  });

  @override
  Widget build(BuildContext context) {
    return _ActionButton(
      isDark: isDark,
      icon: FontAwesomeIcons.copy,
      color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
      onPressed: onCopyTap,
      size: 18,
    );
  }
}

class _ActionButton extends StatelessWidget {
  final bool isDark;
  final IconData icon;
  final Color color;
  final VoidCallback onPressed;
  final double size;

  const _ActionButton({
    required this.isDark,
    required this.icon,
    required this.color,
    required this.onPressed,
    required this.size,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: (isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary).withOpacity(0.2),
        borderRadius: BorderRadius.circular(10),
      ),
      child: IconButton(
        icon: Icon(icon, color: color, size: size),
        onPressed: onPressed,
        padding: const EdgeInsets.all(10),
        constraints: const BoxConstraints(),
      ),
    );
  }
}

