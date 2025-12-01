import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../utils/number_formatter.dart';
import '../config.dart';

class AppDrawer extends StatelessWidget {
  final bool isAuthenticated;
  final String? userName;
  final String? profilePhotoUrl;
  final int? heartScore;
  final int? streak;
  final int currentIndex;
  final Function(int) onItemTap;
  final VoidCallback? onLoginTap;

  const AppDrawer({
    super.key,
    this.isAuthenticated = false,
    this.userName,
    this.profilePhotoUrl,
    this.heartScore,
    this.streak,
    required this.currentIndex,
    required this.onItemTap,
    this.onLoginTap,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Drawer(
      backgroundColor: isDark ? AppTheme.darkBgSecondary : AppTheme.bgSecondary,
      child: SafeArea(
        child: Column(
          children: [
            // Drawer Header
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                color: isDark ? AppTheme.darkBgTertiary : AppTheme.brandSecondary,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Profile Photo
                  CircleAvatar(
                    radius: 40,
                    backgroundColor: Colors.white,
                    backgroundImage: profilePhotoUrl != null && profilePhotoUrl!.isNotEmpty
                        ? NetworkImage(profilePhotoUrl!)
                        : null,
                    child: profilePhotoUrl == null || profilePhotoUrl!.isEmpty
                        ? Icon(
                            FontAwesomeIcons.user,
                            color: AppTheme.brandSecondary,
                            size: 32,
                          )
                        : null,
                  ),
                  const SizedBox(height: 16),
                  // User Name
                  Text(
                    isAuthenticated ? (userName ?? 'کاربر') : 'اذکار نور',
                    textDirection: TextDirection.rtl,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                  const SizedBox(height: 8),
                  // Heart Score and Streak
                  if (isAuthenticated && (heartScore != null || streak != null))
                    Row(
                      children: [
                        if (heartScore != null) ...[
                          Icon(
                            FontAwesomeIcons.heart,
                            color: Colors.white.withOpacity(0.9),
                            size: 16,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            NumberFormatter.formatNumber(heartScore ?? 0),
                            textDirection: TextDirection.rtl,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.white.withOpacity(0.9),
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                          if (streak != null) const SizedBox(width: 16),
                        ],
                        if (streak != null) ...[
                          Icon(
                            FontAwesomeIcons.fire,
                            color: Colors.white.withOpacity(0.9),
                            size: 16,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            NumberFormatter.formatNumber(streak ?? 0),
                            textDirection: TextDirection.rtl,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.white.withOpacity(0.9),
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                        ],
                      ],
                    )
                  else if (!isAuthenticated)
                    Text(
                      'پلتفرم فارسی اذکار و ادعیه اسلامی',
                      textDirection: TextDirection.rtl,
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.white.withOpacity(0.9),
                        fontFamily: AppTheme.fontPrimary,
                      ),
                    ),
                ],
              ),
            ),

            // Navigation Items
            Expanded(
              child: ListView(
                padding: EdgeInsets.zero,
                children: [
                  _DrawerItem(
                    icon: FontAwesomeIcons.house,
                    title: 'خانه',
                    index: 0,
                    currentIndex: currentIndex,
                    onTap: () {
                      Navigator.pop(context);
                      onItemTap(0);
                    },
                    isDark: isDark,
                  ),
                  _DrawerItem(
                    icon: FontAwesomeIcons.handsPraying,
                    title: 'ذکرشمار',
                    index: 1,
                    currentIndex: currentIndex,
                    onTap: () {
                      Navigator.pop(context);
                      onItemTap(1);
                    },
                    isDark: isDark,
                  ),
                  if (isAuthenticated)
                    _DrawerItem(
                      icon: FontAwesomeIcons.user,
                      title: 'داشبورد',
                      index: 2,
                      currentIndex: currentIndex,
                      onTap: () {
                        Navigator.pop(context);
                        onItemTap(2);
                      },
                      isDark: isDark,
                    ),
                  _DrawerItem(
                    icon: FontAwesomeIcons.gear,
                    title: 'تنظیمات',
                    index: isAuthenticated ? 3 : 2,
                    currentIndex: currentIndex,
                    onTap: () {
                      Navigator.pop(context);
                      onItemTap(isAuthenticated ? 3 : 2);
                    },
                    isDark: isDark,
                  ),
                ],
              ),
            ),

            // Divider
            Divider(
              height: 1,
              thickness: 1,
              color: isDark ? Colors.grey[800] : Colors.grey[200],
            ),

            // Login Button (only shown when not authenticated)
            if (!isAuthenticated && onLoginTap != null)
              _DrawerItem(
                icon: FontAwesomeIcons.signInAlt,
                title: 'ورود',
                index: -1,
                currentIndex: -1,
                onTap: () {
                  Navigator.pop(context);
                  onLoginTap!();
                },
                isDark: isDark,
              ),

            // Version Number (bottom center)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 16),
              child: Text(
                'نسخه ${AppConfig.appVersion}',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 11,
                  color: isDark 
                      ? AppTheme.darkTextSecondary 
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                  fontWeight: FontWeight.w300,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _DrawerItem extends StatelessWidget {
  final IconData icon;
  final String title;
  final int index;
  final int currentIndex;
  final VoidCallback onTap;
  final bool isDark;

  const _DrawerItem({
    required this.icon,
    required this.title,
    required this.index,
    required this.currentIndex,
    required this.onTap,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final isActive = index == currentIndex && index != -1;
    final color = isActive
        ? AppTheme.brandPrimary
        : (isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary);

    return InkWell(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: isActive
              ? (isDark
                  ? AppTheme.darkBgTertiary
                  : AppTheme.bgTertiary)
              : Colors.transparent,
          border: Border(
            right: BorderSide(
              color: isActive ? AppTheme.brandPrimary : Colors.transparent,
              width: 3,
            ),
          ),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
        child: Row(
          children: [
            Icon(
              icon,
              size: 22,
              color: color,
            ),
            const SizedBox(width: 16),
            Text(
              title,
              style: TextStyle(
                fontSize: 16,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                color: color,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

