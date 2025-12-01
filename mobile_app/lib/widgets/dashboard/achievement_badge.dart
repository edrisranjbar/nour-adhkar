import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class AchievementBadge extends StatelessWidget {
  final Map<String, dynamic> badge;
  final bool isDark;

  const AchievementBadge({
    super.key,
    required this.badge,
    required this.isDark,
  });

  IconData _getIconFromString(String? iconName) {
    switch (iconName) {
      case 'star':
        return FontAwesomeIcons.star;
      case 'fire':
        return FontAwesomeIcons.fire;
      case 'crown':
        return FontAwesomeIcons.crown;
      case 'heart':
        return FontAwesomeIcons.heart;
      case 'sun':
        return FontAwesomeIcons.sun;
      case 'moon':
        return FontAwesomeIcons.moon;
      default:
        return FontAwesomeIcons.trophy;
    }
  }

  Color _getRarityColor(String? rarity) {
    switch (rarity) {
      case 'bronze':
        return const Color(0xFFCD7F32);
      case 'silver':
        return const Color(0xFFC0C0C0);
      case 'gold':
        return const Color(0xFFFFD700);
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final name = badge['name'] ?? 'نشان';
    final description = badge['description'] ?? '';
    final icon = _getIconFromString(badge['icon']);
    final rarity = badge['rarity'] ?? 'bronze';
    
    // Safely convert unlocked to bool (handles int 0/1, bool, or string)
    final unlockedValue = badge['unlocked'];
    final unlocked = unlockedValue == true || 
                     unlockedValue == 1 || 
                     (unlockedValue is String && unlockedValue == '1') ||
                     (unlockedValue is int && unlockedValue != 0);
    
    // Safely convert progress to double
    final progressValue = badge['progress'];
    double progress = 0.0;
    if (progressValue is double) {
      progress = progressValue;
    } else if (progressValue is int) {
      progress = progressValue.toDouble();
    } else if (progressValue is String) {
      progress = double.tryParse(progressValue) ?? 0.0;
    }

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Badge Circle
        Stack(
          alignment: Alignment.center,
          children: [
            // Progress ring
            if (!unlocked && progress > 0)
              SizedBox(
                width: 70,
                height: 70,
                child: CircularProgressIndicator(
                  value: progress.clamp(0.0, 1.0),
                  strokeWidth: 3,
                  backgroundColor: isDark
                      ? Colors.grey[800]
                      : Colors.grey[300],
                  valueColor: AlwaysStoppedAnimation<Color>(
                    _getRarityColor(rarity).withOpacity(0.7),
                  ),
                ),
              ),
            // Badge container
            Container(
              width: 60,
              height: 60,
              decoration: BoxDecoration(
                gradient: unlocked
                    ? LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          _getRarityColor(rarity),
                          _getRarityColor(rarity).withOpacity(0.7),
                        ],
                      )
                    : null,
                color: unlocked
                    ? null
                    : (isDark ? Colors.grey[800] : Colors.grey[300]),
                shape: BoxShape.circle,
                boxShadow: unlocked
                    ? [
                        BoxShadow(
                          color: _getRarityColor(rarity).withOpacity(0.4),
                          blurRadius: 12,
                          spreadRadius: 2,
                        ),
                      ]
                    : null,
              ),
              child: Icon(
                icon,
                color: unlocked ? Colors.white : Colors.grey[600],
                size: 28,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        // Badge name
        Text(
          name,
          style: TextStyle(
            fontSize: 12,
            fontWeight: unlocked ? FontWeight.w600 : FontWeight.w400,
            color: unlocked
                ? (isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary)
                : (isDark ? Colors.grey[600] : Colors.grey[500]),
            fontFamily: AppTheme.fontPrimary,
          ),
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        const SizedBox(height: 2),
        // Description
        Flexible(
          child: Text(
            description,
            style: TextStyle(
              fontSize: 9,
              color: isDark ? Colors.grey[600] : Colors.grey[500],
              fontFamily: AppTheme.fontPrimary,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.visible,
          ),
        ),
      ],
    );
  }
}

