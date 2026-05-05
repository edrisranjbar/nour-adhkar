import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:get/get.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _passwordVisible = false;
  bool _isLoading = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _checkIfAlreadyLoggedIn();
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _checkIfAlreadyLoggedIn() async {
    debugPrint('[LoginScreen] Checking if user is already logged in...');
    final isAuthenticated = await AuthService.isAuthenticated();

    if (isAuthenticated && mounted) {
      debugPrint(
        '[LoginScreen] User is already authenticated, redirecting to dashboard',
      );
      // Navigate to main dashboard using GetX
      Get.offAllNamed('/main');
    } else {
      debugPrint('[LoginScreen] User is not authenticated, showing login form');
    }
  }

  void _togglePasswordVisibility() {
    setState(() {
      _passwordVisible = !_passwordVisible;
    });
  }

  Future<void> _handleLogin() async {
    debugPrint('[LoginScreen] Login button pressed');

    if (!_formKey.currentState!.validate()) {
      debugPrint('[LoginScreen] Form validation failed');
      return;
    }

    if (_isLoading) {
      debugPrint('[LoginScreen] Login already in progress, ignoring request');
      return;
    }

    final email = _emailController.text.trim();
    debugPrint('[LoginScreen] Starting login process for email: $email');

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      debugPrint('[LoginScreen] Calling AuthService.login()');
      final result = await AuthService.login(email, _passwordController.text);

      debugPrint('[LoginScreen] AuthService.login() completed');
      debugPrint('[LoginScreen] Result: $result');
      debugPrint('[LoginScreen] Success: ${result['success']}');

      if (mounted) {
        if (result['success'] == true) {
          debugPrint(
            '[LoginScreen] Login successful, calling onLoginSuccess callback',
          );
          // Login successful
          setState(() {
            _isLoading = false;
          });
          debugPrint('[LoginScreen] Login successful, navigating to dashboard');
          // Navigate to main dashboard using GetX
          Get.offAllNamed('/main');
        } else {
          final errorMsg =
              result['message'] ??
              'خطا در ورود به سیستم. لطفاً دوباره تلاش کنید.';
          debugPrint('[LoginScreen] Login failed: $errorMsg');
          setState(() {
            _errorMessage = errorMsg;
            _isLoading = false;
          });
        }
      } else {
        debugPrint('[LoginScreen] Widget not mounted, skipping state update');
      }
    } catch (e, stackTrace) {
      debugPrint('[LoginScreen] Exception caught in _handleLogin');
      debugPrint('[LoginScreen] Exception: $e');
      debugPrint('[LoginScreen] Stack trace: $stackTrace');

      if (mounted) {
        setState(() {
          // Show more specific error messages
          String errorMsg = 'خطا در ورود به سیستم';
          if (e.toString().contains('TimeoutException') ||
              e.toString().contains('timeout')) {
            errorMsg = 'زمان درخواست به پایان رسید. لطفاً دوباره تلاش کنید.';
            debugPrint('[LoginScreen] Timeout error detected');
          } else if (e.toString().contains('SocketException') ||
              e.toString().contains('network')) {
            errorMsg = 'اتصال اینترنت خود را بررسی کنید';
            debugPrint('[LoginScreen] Network error detected');
          } else if (e.toString().contains('FormatException') ||
              e.toString().contains('JSON')) {
            errorMsg = 'خطا در پردازش پاسخ سرور. لطفاً دوباره تلاش کنید.';
            debugPrint('[LoginScreen] JSON parsing error detected');
          } else {
            debugPrint('[LoginScreen] Unknown error type');
          }
          _errorMessage = errorMsg;
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
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
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(24),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 420),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const SizedBox(height: 20),
                      // Title
                      Text(
                        'ورود',
                        textAlign: TextAlign.center,
                        textDirection: TextDirection.rtl,
                        style: TextStyle(
                          fontSize: 28,
                          fontWeight: FontWeight.w700,
                          color: isDark ? Colors.white : Colors.black87,
                          fontFamily: AppTheme.fontPrimary,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: 40),

                      // Login Form
                      Form(
                        key: _formKey,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            // Email Field
                            Text(
                              'ایمیل',
                              textDirection: TextDirection.rtl,
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white : Colors.black87,
                                fontFamily: AppTheme.fontPrimary,
                              ),
                            ),
                            const SizedBox(height: 10),
                            TextFormField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              textDirection: TextDirection.ltr,
                              textAlign: TextAlign.left,
                              style: TextStyle(
                                color: isDark ? Colors.white : Colors.black87,
                                fontFamily: AppTheme.fontPrimary,
                                fontSize: 16,
                                fontWeight: FontWeight.w500,
                              ),
                              decoration: InputDecoration(
                                hintText: 'لطفاً ایمیل خود را وارد کنید',
                                hintStyle: TextStyle(
                                  color: isDark
                                      ? Colors.white.withOpacity(0.4)
                                      : Colors.black38,
                                  fontFamily: AppTheme.fontPrimary,
                                ),
                                filled: true,
                                fillColor: isDark
                                    ? Colors.white.withOpacity(0.05)
                                    : Colors.white.withOpacity(0.7),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: isDark
                                        ? Colors.white.withOpacity(0.2)
                                        : Colors.black.withOpacity(0.1),
                                    width: 1.5,
                                  ),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: isDark
                                        ? Colors.white.withOpacity(0.2)
                                        : Colors.black.withOpacity(0.1),
                                    width: 1.5,
                                  ),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: AppTheme.brandPrimary,
                                    width: 2,
                                  ),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 16,
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'لطفاً ایمیل خود را وارد کنید';
                                }
                                if (!value.contains('@') ||
                                    !value.contains('.')) {
                                  return 'لطفاً یک ایمیل معتبر وارد کنید';
                                }
                                return null;
                              },
                            ),
                            const SizedBox(height: 24),

                            // Password Field
                            Text(
                              'رمز عبور',
                              textDirection: TextDirection.rtl,
                              style: TextStyle(
                                fontSize: 15,
                                fontWeight: FontWeight.w600,
                                color: isDark ? Colors.white : Colors.black87,
                                fontFamily: AppTheme.fontPrimary,
                              ),
                            ),
                            const SizedBox(height: 10),
                            TextFormField(
                              controller: _passwordController,
                              obscureText: !_passwordVisible,
                              textDirection: TextDirection.ltr,
                              textAlign: TextAlign.left,
                              style: TextStyle(
                                color: isDark ? Colors.white : Colors.black87,
                                fontFamily: AppTheme.fontPrimary,
                                fontSize: 16,
                                fontWeight: FontWeight.w500,
                              ),
                              decoration: InputDecoration(
                                hintText: 'لطفاً رمز عبور خود را وارد کنید',
                                hintStyle: TextStyle(
                                  color: isDark
                                      ? Colors.white.withOpacity(0.4)
                                      : Colors.black38,
                                  fontFamily: AppTheme.fontPrimary,
                                ),
                                filled: true,
                                fillColor: isDark
                                    ? Colors.white.withOpacity(0.05)
                                    : Colors.white.withOpacity(0.7),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: isDark
                                        ? Colors.white.withOpacity(0.2)
                                        : Colors.black.withOpacity(0.1),
                                    width: 1.5,
                                  ),
                                ),
                                enabledBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: isDark
                                        ? Colors.white.withOpacity(0.2)
                                        : Colors.black.withOpacity(0.1),
                                    width: 1.5,
                                  ),
                                ),
                                focusedBorder: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(12),
                                  borderSide: BorderSide(
                                    color: AppTheme.brandPrimary,
                                    width: 2,
                                  ),
                                ),
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 16,
                                ),
                                suffixIcon: IconButton(
                                  icon: Icon(
                                    _passwordVisible
                                        ? FontAwesomeIcons.eyeSlash
                                        : FontAwesomeIcons.eye,
                                    color: isDark
                                        ? Colors.white.withOpacity(0.6)
                                        : Colors.black54,
                                    size: 20,
                                  ),
                                  onPressed: _togglePasswordVisibility,
                                ),
                              ),
                              validator: (value) {
                                if (value == null || value.isEmpty) {
                                  return 'لطفاً رمز عبور خود را وارد کنید';
                                }
                                if (value.length < 6) {
                                  return 'رمز عبور باید حداقل ۶ کاراکتر باشد';
                                }
                                return null;
                              },
                              onFieldSubmitted: (_) => _handleLogin(),
                            ),
                            const SizedBox(height: 28),

                            // Error Message
                            if (_errorMessage != null)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 16,
                                  vertical: 14,
                                ),
                                margin: const EdgeInsets.only(bottom: 20),
                                decoration: BoxDecoration(
                                  color: Colors.red.withOpacity(0.15),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: Colors.red.withOpacity(0.4),
                                    width: 1.5,
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    Icon(
                                      FontAwesomeIcons.circleExclamation,
                                      color: Colors.red,
                                      size: 18,
                                    ),
                                    const SizedBox(width: 12),
                                    Expanded(
                                      child: Text(
                                        _errorMessage!,
                                        textDirection: TextDirection.rtl,
                                        style: TextStyle(
                                          color: Colors.red.shade700,
                                          fontSize: 14,
                                          fontWeight: FontWeight.w600,
                                          fontFamily: AppTheme.fontPrimary,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                            // Login Button
                            Container(
                              width: double.infinity,
                              height: 56,
                              decoration: BoxDecoration(
                                color: AppTheme.brandPrimary,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: [
                                  BoxShadow(
                                    color: AppTheme.brandPrimary.withOpacity(
                                      0.3,
                                    ),
                                    blurRadius: 12,
                                    offset: const Offset(0, 4),
                                  ),
                                ],
                              ),
                              child: ElevatedButton(
                                onPressed: _isLoading ? null : _handleLogin,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.transparent,
                                  shadowColor: Colors.transparent,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  elevation: 0,
                                ),
                                child: _isLoading
                                    ? const SizedBox(
                                        height: 22,
                                        width: 22,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2.5,
                                          valueColor:
                                              AlwaysStoppedAnimation<Color>(
                                                Colors.white,
                                              ),
                                        ),
                                      )
                                    : Text(
                                        'ورود',
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
                      const SizedBox(height: 24),

                      // Additional Links
                      TextButton(
                        onPressed: () {
                          Get.to(() => const RegisterScreen());
                        },
                        style: TextButton.styleFrom(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                        ),
                        child: Text(
                          'حساب کاربری ندارید؟ ثبت نام کنید',
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            color: isDark
                                ? Colors.white.withOpacity(0.9)
                                : Colors.black87,
                            fontSize: 15,
                            fontWeight: FontWeight.w500,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                    ],
                  ),
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
