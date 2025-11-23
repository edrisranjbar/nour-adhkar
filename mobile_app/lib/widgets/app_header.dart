import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../utils/number_formatter.dart';

class AppHeader extends StatelessWidget {
  final String title;
  final String description;
  final bool isAuthenticated;
  final int? heartScore;
  final int? streak;
  final VoidCallback? onLoginTap;
  final VoidCallback? onLogoutTap;
  final VoidCallback? onDashboardTap;
  final VoidCallback? onMenuTap;

  const AppHeader({
    super.key,
    required this.title,
    required this.description,
    this.isAuthenticated = false,
    this.heartScore,
    this.streak,
    this.onLoginTap,
    this.onLogoutTap,
    this.onDashboardTap,
    this.onMenuTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF262626) : AppTheme.brandSecondary,
        border: Border(
          bottom: BorderSide(
            color: Colors.white.withOpacity(0.1),
            width: 1,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: SafeArea(
        child: Column(
          children: [
            Directionality(
              textDirection: TextDirection.rtl,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Menu Button
                  IconButton(
                    icon: const Icon(
                      Icons.menu,
                      color: Colors.white,
                      size: 28,
                    ),
                    onPressed: onMenuTap ?? () {
                      Scaffold.of(context).openDrawer();
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                  
                  // Logo and Title Section
                  Expanded(
                    child: Row(
                      children: [
                        // Logo
                        Container(
                          width: 50,
                          height: 50,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.1),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.asset(
                              'assets/icons/logo.png',
                              width: 50,
                              height: 50,
                              fit: BoxFit.cover,
                              errorBuilder: (context, error, stackTrace) {
                                // Fallback to icon if image fails to load
                                return const Icon(
                                  Icons.mosque,
                                  color: AppTheme.brandSecondary,
                                  size: 28,
                                );
                              },
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                      ],
                    ),
                  ),
                
                // Right Section - Auth buttons or user stats
                if (isAuthenticated) ...[
                  // User Stats
                  Row(
                    children: [
                      _StatItem(
                        icon: FontAwesomeIcons.heart,
                        value: heartScore ?? 0,
                        isDark: isDark,
                      ),
                      const SizedBox(width: 12),
                      _StatItem(
                        icon: FontAwesomeIcons.fire,
                        value: streak ?? 0,
                        isDark: isDark,
                      ),
                      const SizedBox(width: 12),
                      // Dashboard Button
                      if (onDashboardTap != null)
                        _HeaderButton(
                          icon: FontAwesomeIcons.user,
                          label: 'داشبورد',
                          onTap: onDashboardTap!,
                          isDark: isDark,
                        ),
                    ],
                  ),
                ] else if (onLoginTap != null) ...[
                  _HeaderButton(
                    icon: FontAwesomeIcons.rightToBracket,
                    label: 'ورود',
                    onTap: onLoginTap!,
                    isDark: isDark,
                  ),
                ],
              ],
            ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatItem extends StatelessWidget {
  final IconData icon;
  final int value;
  final bool isDark;

  const _StatItem({
    required this.icon,
    required this.value,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF333333) : const Color(0xFF8A7559),
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.2),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            color: Colors.white,
            size: 16,
          ),
          const SizedBox(width: 6),
          Text(
            NumberFormatter.formatNumber(value),
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w600,
              fontSize: 14,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
        ],
      ),
    );
  }
}

class _HeaderButton extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool isDark;

  const _HeaderButton({
    required this.icon,
    required this.label,
    required this.onTap,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF333333) : const Color(0xFF8A7559),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              color: Colors.white,
              size: 16,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: const TextStyle(
                color: Colors.white,
                fontWeight: FontWeight.w500,
                fontSize: 14,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ],
        ),
      ),
    );
    }
}

