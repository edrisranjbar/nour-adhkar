import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../../theme/app_theme.dart';

class AnimatedBadge extends StatefulWidget {
  final Map<String, dynamic> badge;
  final bool isDark;
  final Duration delay;

  const AnimatedBadge({
    super.key,
    required this.badge,
    required this.isDark,
    this.delay = Duration.zero,
  });

  @override
  State<AnimatedBadge> createState() => _AnimatedBadgeState();
}

class _AnimatedBadgeState extends State<AnimatedBadge>
    with TickerProviderStateMixin {
  late AnimationController _entranceController;
  late AnimationController _pulseController;
  late Animation<double> _scaleAnimation;
  late Animation<double> _fadeAnimation;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    
    // Entrance animation controller
    _entranceController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );

    // Pulse animation controller for unlocked badges
    final unlockedValue = widget.badge['unlocked'];
    final unlocked = unlockedValue == true ||
        unlockedValue == 1 ||
        (unlockedValue is String && unlockedValue == '1') ||
        (unlockedValue is int && unlockedValue != 0);

    _pulseController = AnimationController(
      duration: const Duration(milliseconds: 1500),
      vsync: this,
    );

    _scaleAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entranceController,
        curve: Curves.elasticOut,
      ),
    );

    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entranceController,
        curve: const Interval(0.0, 0.6, curve: Curves.easeOut),
      ),
    );

    if (unlocked) {
      _pulseAnimation = Tween<double>(begin: 1.0, end: 1.08).animate(
        CurvedAnimation(
          parent: _pulseController,
          curve: Curves.easeInOut,
        ),
      );
    } else {
      _pulseAnimation = AlwaysStoppedAnimation(1.0);
    }

    // Start animation after delay
    Future.delayed(widget.delay, () {
      if (mounted) {
        _entranceController.forward().then((_) {
          // After entrance animation completes, start continuous pulse for unlocked badges
          if (unlocked && mounted) {
            _pulseController.repeat(reverse: true);
          }
        });
      }
    });
  }

  @override
  void dispose() {
    _entranceController.dispose();
    _pulseController.dispose();
    super.dispose();
  }

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
    final name = widget.badge['name'] ?? 'نشان';
    final description = widget.badge['description'] ?? '';
    final icon = _getIconFromString(widget.badge['icon']);
    final rarity = widget.badge['rarity'] ?? 'bronze';

    // Safely convert unlocked to bool (handles int 0/1, bool, or string)
    final unlockedValue = widget.badge['unlocked'];
    final unlocked = unlockedValue == true ||
        unlockedValue == 1 ||
        (unlockedValue is String && unlockedValue == '1') ||
        (unlockedValue is int && unlockedValue != 0);

    // Safely convert progress to double
    final progressValue = widget.badge['progress'];
    double progress = 0.0;
    if (progressValue is double) {
      progress = progressValue;
    } else if (progressValue is int) {
      progress = progressValue.toDouble();
    } else if (progressValue is String) {
      progress = double.tryParse(progressValue) ?? 0.0;
    }

    return FadeTransition(
      opacity: _fadeAnimation,
      child: ScaleTransition(
        scale: _scaleAnimation,
        child: GestureDetector(
          onTapDown: (_) {
            _entranceController.forward(from: 0.8);
          },
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Badge Circle
              AnimatedBuilder(
                animation: _pulseAnimation,
                builder: (context, child) {
                  return Transform.scale(
                    scale: unlocked ? _pulseAnimation.value : 1.0,
                    child: child,
                  );
                },
                child: Stack(
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
                          backgroundColor: widget.isDark
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
                            : (widget.isDark ? Colors.grey[800] : Colors.grey[300]),
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
              ),
              const SizedBox(height: 6),
              // Badge name
              Text(
                name,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: unlocked ? FontWeight.w600 : FontWeight.w400,
                  color: unlocked
                      ? (widget.isDark
                          ? AppTheme.darkTextPrimary
                          : AppTheme.textPrimary)
                      : (widget.isDark ? Colors.grey[600] : Colors.grey[500]),
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
                    color: widget.isDark ? Colors.grey[600] : Colors.grey[500],
                    fontFamily: AppTheme.fontPrimary,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.visible,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// Keep the original class for backward compatibility
class AchievementBadge extends StatelessWidget {
  final Map<String, dynamic> badge;
  final bool isDark;

  const AchievementBadge({
    super.key,
    required this.badge,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return AnimatedBadge(
      badge: badge,
      isDark: isDark,
    );
  }
}

