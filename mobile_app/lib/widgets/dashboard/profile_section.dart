import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class ProfileSection extends StatelessWidget {
  final Map<String, dynamic>? user;
  final bool isDark;

  const ProfileSection({
    super.key,
    required this.user,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    final userName = user?['name'] ?? user?['email'] ?? 'کاربر';
    final profilePhotoUrl = user?['avatar'] ??
        user?['profile_photo'] ??
        user?['photo'];

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          // Profile Photo
          CircleAvatar(
            radius: 40,
            backgroundColor: isDark
                ? AppTheme.darkBrandSecondary
                : AppTheme.brandSecondary,
            backgroundImage: profilePhotoUrl != null && profilePhotoUrl.isNotEmpty
                ? NetworkImage(profilePhotoUrl)
                : null,
            child: profilePhotoUrl == null || profilePhotoUrl.isEmpty
                ? const Icon(
                    FontAwesomeIcons.user,
                    color: Colors.white,
                    size: 32,
                  )
                : null,
          ),
          const SizedBox(width: 16),
          // User Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  userName,
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w600,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
                if (user?['email'] != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    user!['email'],
                    style: TextStyle(
                      fontSize: 14,
                      color: isDark
                          ? AppTheme.darkTextSecondary
                          : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

