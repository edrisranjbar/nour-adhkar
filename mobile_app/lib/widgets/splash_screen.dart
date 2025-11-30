import 'package:flutter/material.dart';
import 'dart:async';
import '../theme/app_theme.dart';
import '../config.dart';

class SplashScreen extends StatefulWidget {
  final VoidCallback? onComplete;

  const SplashScreen({
    super.key,
    this.onComplete,
  });

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  double _progress = 0.0;
  late Timer _progressTimer;
  late AnimationController _fadeAnimationController;

  @override
  void initState() {
    super.initState();
    _startProgress();
    _startFadeAnimation();
  }

  void _startProgress() {
    _progressTimer = Timer.periodic(const Duration(milliseconds: 50), (timer) {
      if (mounted) {
        setState(() {
          _progress += 2.5;
          if (_progress >= 100) {
            _progress = 100;
            timer.cancel();
            _completeSplash();
          }
        });
      } else {
        timer.cancel();
      }
    });
  }

  void _startFadeAnimation() {
    _fadeAnimationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    )..forward();
  }

  void _completeSplash() {
    Future.delayed(const Duration(milliseconds: 300), () {
      if (mounted && widget.onComplete != null) {
        widget.onComplete!();
      }
    });
  }

  @override
  void dispose() {
    _progressTimer.cancel();
    _fadeAnimationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? AppTheme.darkBgPrimary : AppTheme.brandDark;
    
    return Scaffold(
      backgroundColor: bgColor,
      body: Stack(
        children: [
          // Geometric Pattern Background
          CustomPaint(
            painter: GeometricPatternPainter(
              color: (isDark ? AppTheme.darkBrandDark : AppTheme.brandDark)
                  .withOpacity(0.15),
            ),
            size: Size.infinite,
          ),

          // Main Content
          SafeArea(
            child: Center(
              child: FadeTransition(
                opacity: _fadeAnimationController,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Large Stylized Arabic Text "أذكار"
                    ShaderMask(
                      shaderCallback: (bounds) => LinearGradient(
                        colors: [
                          Colors.white,
                          Colors.white.withOpacity(0.9),
                        ],
                      ).createShader(bounds),
                      child: Text(
                        'اذکار نور',
                        style: TextStyle(
                          fontSize: 100,
                          fontWeight: FontWeight.w300,
                          color: Colors.white,
                          fontFamily: AppTheme.fontArabic,
                          letterSpacing: 8,
                          height: 1.2,
                          shadows: [
                            Shadow(
                              color: Colors.black.withOpacity(0.3),
                              blurRadius: 20,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Subtitle in Yellow/Gold
                    Text(
                      'الا بذكر الله تطمئن القلوب',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w400,
                        color: AppTheme.warning,
                        fontFamily: AppTheme.fontArabic,
                        letterSpacing: 2,
                        shadows: [
                          Shadow(
                            color: Colors.black.withOpacity(0.2),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      textDirection: TextDirection.rtl,
                    ),
                    const SizedBox(height: 60),

                    // Subtle Progress Indicator
                    Container(
                      constraints: const BoxConstraints(maxWidth: 200),
                      height: 3,
                      margin: const EdgeInsets.symmetric(horizontal: 40),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(3),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(3),
                        child: FractionallySizedBox(
                          widthFactor: _progress / 100,
                          alignment: Alignment.centerLeft,
                          child: Container(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  AppTheme.warning,
                                  AppTheme.warning.withOpacity(0.8),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // Version Number at Bottom
          Positioned(
            bottom: 24,
            left: 0,
            right: 0,
            child: Text(
              'نسخه ${AppConfig.appVersion}',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w300,
                color: Colors.white.withOpacity(0.6),
                fontFamily: AppTheme.fontPrimary,
              ),
              textDirection: TextDirection.rtl,
            ),
          ),
        ],
      ),
    );
  }
}

// Custom Painter for Geometric Pattern
class GeometricPatternPainter extends CustomPainter {
  final Color color;

  GeometricPatternPainter({required this.color});

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = 1.5;

    // Create interconnected squares and diamonds pattern
    final tileSize = 80.0;
    final rows = (size.height / tileSize).ceil() + 1;
    final cols = (size.width / tileSize).ceil() + 1;

    for (int row = 0; row < rows; row++) {
      for (int col = 0; col < cols; col++) {
        final x = col * tileSize;
        final y = row * tileSize;

        // Draw diamond shape
        final diamondPath = Path();
        diamondPath.moveTo(x + tileSize / 2, y);
        diamondPath.lineTo(x + tileSize, y + tileSize / 2);
        diamondPath.lineTo(x + tileSize / 2, y + tileSize);
        diamondPath.lineTo(x, y + tileSize / 2);
        diamondPath.close();

        canvas.drawPath(diamondPath, paint);

        // Draw connecting lines to form circular motif
        if (row > 0 && col > 0) {
          // Connect to previous diamond
          canvas.drawLine(
            Offset(x + tileSize / 2, y + tileSize / 2),
            Offset(x - tileSize / 2, y - tileSize / 2),
            paint,
          );
        }
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

