import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'dart:ui';
import '../theme/app_theme.dart';
import 'login_screen.dart';
import 'register_screen.dart';

class WelcomeScreen extends StatelessWidget {
  const WelcomeScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final bgColor = isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary;

    return Scaffold(
      backgroundColor: bgColor,
      body: Stack(
        children: [
          // Geometric Pattern Background
          CustomPaint(
            painter: GeometricPatternPainter(
              color: (isDark ? AppTheme.darkBrandDark : AppTheme.brandDark)
                  .withOpacity(0.08),
            ),
            size: Size.infinite,
          ),

          // Main Content
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const SizedBox(height: 40),

                    // Large Stylized Logo Text
                    ShaderMask(
                      shaderCallback: (bounds) => LinearGradient(
                        colors: [AppTheme.brandDark, AppTheme.brandPrimary],
                      ).createShader(bounds),
                      child: Text(
                        'اذکار نور',
                        style: TextStyle(
                          fontSize: 80,
                          fontWeight: FontWeight.w300,
                          color: Colors.white,
                          fontFamily: AppTheme.fontArabic,
                          height: 1.2,
                          shadows: [
                            Shadow(
                              color: Colors.black.withOpacity(0.2),
                              blurRadius: 15,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Subtitle in Yellow/Gold
                    Text(
                      'اذکاری که با آن قلب‌ها آرام می‌گیرند',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.w400,
                        color: AppTheme.warning,
                        fontFamily: AppTheme.fontPrimary,
                        letterSpacing: 1,
                        shadows: [
                          Shadow(
                            color: Colors.black.withOpacity(0.15),
                            blurRadius: 6,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      textAlign: TextAlign.center,
                      textDirection: TextDirection.rtl,
                    ),
                    const SizedBox(height: 80),

                    // Login Button
                    Container(
                      width: double.infinity,
                      constraints: const BoxConstraints(maxWidth: 400),
                      height: 56,
                      decoration: BoxDecoration(
                        color: AppTheme.brandPrimary,
                        borderRadius: BorderRadius.circular(12),
                        boxShadow: [
                          BoxShadow(
                            color: AppTheme.brandPrimary.withOpacity(0.3),
                            blurRadius: 12,
                            offset: const Offset(0, 4),
                          ),
                        ],
                      ),
                      child: ElevatedButton(
                        onPressed: () {
                          Get.to(() => const LoginScreen());
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: Text(
                          'ورود',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // New Account Button
                    Container(
                      width: double.infinity,
                      constraints: const BoxConstraints(maxWidth: 400),
                      height: 56,
                      decoration: BoxDecoration(
                        color: isDark
                            ? Colors.white.withOpacity(0.1)
                            : Colors.white.withOpacity(0.6),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(
                          color: AppTheme.brandPrimary.withOpacity(0.3),
                          width: 1.5,
                        ),
                      ),
                      child: ElevatedButton(
                        onPressed: () {
                          Get.to(() => const RegisterScreen());
                        },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.transparent,
                          shadowColor: Colors.transparent,
                          foregroundColor: AppTheme.brandPrimary,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          elevation: 0,
                        ),
                        child: Text(
                          'حساب جدید',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
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
      ..strokeWidth = 1.0;

    // Create interconnected squares and diamonds pattern
    final tileSize = 60.0;
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
