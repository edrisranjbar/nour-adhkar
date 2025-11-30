import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class ModernToast {
  static void show(
    BuildContext context, {
    required String message,
    IconData? icon,
    Color? backgroundColor,
    Color? iconColor,
    Duration? duration,
  }) {
    final overlay = Overlay.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    final overlayEntry = OverlayEntry(
      builder: (context) => _ToastOverlay(
        message: message,
        icon: icon,
        backgroundColor: backgroundColor,
        iconColor: iconColor,
        isDark: isDark,
        duration: duration ?? const Duration(seconds: 2),
      ),
    );

    overlay.insert(overlayEntry);

    Future.delayed(duration ?? const Duration(seconds: 2), () {
      if (overlayEntry.mounted) {
        overlayEntry.remove();
      }
    });
  }
}

class _ToastOverlay extends StatefulWidget {
  final String message;
  final IconData? icon;
  final Color? backgroundColor;
  final Color? iconColor;
  final bool isDark;
  final Duration duration;

  const _ToastOverlay({
    required this.message,
    this.icon,
    this.backgroundColor,
    this.iconColor,
    required this.isDark,
    required this.duration,
  });

  @override
  State<_ToastOverlay> createState() => _ToastOverlayState();
}

class _ToastOverlayState extends State<_ToastOverlay>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<Offset> _slideAnimation;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 300),
    );

    _slideAnimation = Tween<Offset>(
      begin: const Offset(0, -1.5),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutCubic,
    ));

    _fadeAnimation = Tween<double>(
      begin: 0.0,
      end: 1.0,
    ).animate(CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOut,
    ));

    _controller.forward();

    Future.delayed(widget.duration - const Duration(milliseconds: 300), () {
      if (mounted) {
        _controller.reverse();
      }
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final backgroundColor = widget.backgroundColor ??
        (widget.isDark
            ? AppTheme.darkBrandPrimary
            : AppTheme.brandPrimary);

    final iconColor = widget.iconColor ?? Colors.white;

    return Positioned(
      top: MediaQuery.of(context).padding.top + 16,
      left: 16,
      right: 16,
      child: SlideTransition(
        position: _slideAnimation,
        child: FadeTransition(
          opacity: _fadeAnimation,
          child: Material(
            color: Colors.transparent,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
              decoration: BoxDecoration(
                color: backgroundColor,
                borderRadius: BorderRadius.circular(16),
                boxShadow: [
                  BoxShadow(
                    color: backgroundColor.withOpacity(0.3),
                    blurRadius: 20,
                    offset: const Offset(0, 8),
                    spreadRadius: 0,
                  ),
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 10,
                    offset: const Offset(0, 4),
                    spreadRadius: 0,
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (widget.icon != null) ...[
                    Icon(
                      widget.icon,
                      color: iconColor,
                      size: 20,
                    ),
                    const SizedBox(width: 12),
                  ],
                  Flexible(
                    child: Text(
                      widget.message,
                      textDirection: TextDirection.rtl,
                      style: TextStyle(
                        color: iconColor,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        fontFamily: AppTheme.fontPrimary,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

