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
  late AnimationController _dotsAnimationController;

  @override
  void initState() {
    super.initState();
    _startProgress();
    _startDotsAnimation();
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

  void _startDotsAnimation() {
    _dotsAnimationController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 2),
    )..repeat();
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
    _dotsAnimationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.bgPrimary,
      body: SafeArea(
        child: Stack(
          children: [
            // Main Content
            Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  // Logo
                  Container(
                    width: 130,
                    height: 130,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 12,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.all(16),
                    child: Image.asset(
                      'assets/icons/logo.png',
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) {
                        return const Icon(
                          Icons.mosque,
                          size: 80,
                          color: AppTheme.brandPrimary,
                        );
                      },
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Title
                  RichText(
                    text: TextSpan(
                      style: const TextStyle(
                        fontSize: 48,
                        fontWeight: FontWeight.w400,
                        color: AppTheme.brandPrimary,
                        fontFamily: AppTheme.fontPrimary,
                      ),
                      children: const [
                        TextSpan(text: 'اذکار '),
                        TextSpan(
                          text: 'نور',
                          style: TextStyle(fontWeight: FontWeight.w700),
                        ),
                      ],
                    ),
                    textDirection: TextDirection.rtl,
                  ),
                  const SizedBox(height: 40),

                  // Progress Container
                  Container(
                    constraints: const BoxConstraints(maxWidth: 300),
                    height: 16,
                    margin: const EdgeInsets.symmetric(horizontal: 40),
                    decoration: BoxDecoration(
                      color: const Color(0xFFB09B81),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Stack(
                        children: [
                          // Progress Bar
                          FractionallySizedBox(
                            widthFactor: _progress / 100,
                            child: Container(
                              height: 16,
                              decoration: const BoxDecoration(
                                color: Color(0xFF907554),
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Loading Text
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Text(
                        'درحال بارگذاری',
                        style: TextStyle(
                          fontSize: 24,
                          color: AppTheme.brandPrimary,
                          fontFamily: AppTheme.fontPrimary,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                      const SizedBox(width: 4),
                      AnimatedBuilder(
                        animation: _dotsAnimationController,
                        builder: (context, child) {
                          final dots = '...'.substring(
                            0,
                            ((_dotsAnimationController.value * 3).floor() % 3) + 1,
                          );
                          return Text(
                            dots,
                            style: const TextStyle(
                              fontSize: 24,
                              color: AppTheme.brandPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                  const SizedBox(height: 40),
                ],
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
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w300,
                  color: AppTheme.brandPrimary,
                  fontFamily: AppTheme.fontPrimary,
                ),
                textDirection: TextDirection.rtl,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

