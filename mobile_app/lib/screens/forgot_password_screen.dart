import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;
  String? _successMessage;

  @override
  void dispose() {
    _emailController.dispose();
    super.dispose();
  }

  Future<void> _handleForgotPassword() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_isLoading) {
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      final result = await AuthService.forgotPassword(
        _emailController.text.trim(),
      );

      if (mounted) {
        setState(() {
          _isLoading = false;
          if (result['success'] == true) {
            _successMessage = result['message'] ?? 'لینک بازیابی رمز عبور به ایمیل شما ارسال شد';
            _errorMessage = null;
          } else {
            _errorMessage = result['message'] ?? 'خطا در ارسال لینک بازیابی';
            _successMessage = null;
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'خطا در ارسال لینک بازیابی';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _handleResend() async {
    if (_isLoading) {
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _successMessage = null;
    });

    try {
      final result = await AuthService.resendPasswordReset(
        _emailController.text.trim(),
      );

      if (mounted) {
        setState(() {
          _isLoading = false;
          if (result['success'] == true) {
            _successMessage = result['message'] ?? 'لینک بازیابی رمز عبور مجدداً به ایمیل شما ارسال شد';
            _errorMessage = null;
          } else {
            _errorMessage = result['message'] ?? 'خطا در ارسال مجدد لینک بازیابی';
            _successMessage = null;
          }
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'خطا در ارسال مجدد لینک بازیابی';
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
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back,
            color: isDark ? Colors.white : Colors.black87,
          ),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Enhanced Title
                  Text(
                    'فراموشی رمز عبور',
                    textAlign: TextAlign.center,
                    textDirection: TextDirection.rtl,
                    style: TextStyle(
                      fontSize: 36,
                      fontWeight: FontWeight.w800,
                      color: isDark ? Colors.white : Colors.black87,
                      fontFamily: AppTheme.fontPrimary,
                      letterSpacing: -0.5,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'ایمیل خود را وارد کنید تا لینک بازیابی رمز عبور برای شما ارسال شود',
                    textAlign: TextAlign.center,
                    textDirection: TextDirection.rtl,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w400,
                      color: isDark 
                          ? Colors.white.withOpacity(0.7)
                          : Colors.black54,
                      fontFamily: AppTheme.fontPrimary,
                      height: 1.6,
                    ),
                  ),
                  const SizedBox(height: 40),

                  // Enhanced Form Card
                  Container(
                    decoration: BoxDecoration(
                      color: isDark 
                          ? const Color(0xFF1E1E1E)
                          : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isDark
                            ? Colors.white.withOpacity(0.1)
                            : Colors.black.withOpacity(0.08),
                        width: 1,
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: isDark
                              ? Colors.black.withOpacity(0.5)
                              : Colors.black.withOpacity(0.1),
                          blurRadius: 20,
                          offset: const Offset(0, 8),
                          spreadRadius: 0,
                        ),
                        BoxShadow(
                          color: AppTheme.brandPrimary.withOpacity(0.1),
                          blurRadius: 30,
                          offset: const Offset(0, 4),
                          spreadRadius: -5,
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.all(28),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Email Field
                          Row(
                            children: [
                              Icon(
                                FontAwesomeIcons.envelope,
                                size: 16,
                                color: isDark 
                                    ? Colors.white.withOpacity(0.7)
                                    : Colors.black54,
                              ),
                              const SizedBox(width: 8),
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
                            ],
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
                              hintText: 'example@email.com',
                              hintStyle: TextStyle(
                                color: isDark 
                                    ? Colors.white.withOpacity(0.4)
                                    : Colors.black38,
                                fontFamily: AppTheme.fontPrimary,
                              ),
                              filled: true,
                              fillColor: isDark 
                                  ? Colors.white.withOpacity(0.05)
                                  : Colors.black.withOpacity(0.03),
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide(
                                  color: isDark 
                                      ? Colors.white.withOpacity(0.2)
                                      : Colors.black.withOpacity(0.15),
                                  width: 1.5,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide(
                                  color: isDark 
                                      ? Colors.white.withOpacity(0.2)
                                      : Colors.black.withOpacity(0.15),
                                  width: 1.5,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(14),
                                borderSide: BorderSide(
                                  color: AppTheme.brandPrimary,
                                  width: 2.5,
                                ),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 18,
                                vertical: 18,
                              ),
                            ),
                            validator: (value) {
                              if (value == null || value.isEmpty) {
                                return 'لطفاً ایمیل خود را وارد کنید';
                              }
                              if (!value.contains('@') || !value.contains('.')) {
                                return 'لطفاً یک ایمیل معتبر وارد کنید';
                              }
                              return null;
                            },
                            onFieldSubmitted: (_) => _handleForgotPassword(),
                          ),
                          const SizedBox(height: 28),

                          // Success Message
                          if (_successMessage != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                              margin: const EdgeInsets.only(bottom: 20),
                              decoration: BoxDecoration(
                                color: Colors.green.withOpacity(0.15),
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                  color: Colors.green.withOpacity(0.4),
                                  width: 1.5,
                                ),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    FontAwesomeIcons.circleCheck,
                                    color: Colors.green.shade700,
                                    size: 18,
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: Text(
                                      _successMessage!,
                                      textDirection: TextDirection.rtl,
                                      style: TextStyle(
                                        color: Colors.green.shade700,
                                        fontSize: 14,
                                        fontWeight: FontWeight.w600,
                                        fontFamily: AppTheme.fontPrimary,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                          // Error Message
                          if (_errorMessage != null)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
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

                          // Submit Button
                          Container(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: [
                                  AppTheme.brandPrimary,
                                  AppTheme.brandSecondary,
                                ],
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                              ),
                              borderRadius: BorderRadius.circular(14),
                              boxShadow: [
                                BoxShadow(
                                  color: AppTheme.brandPrimary.withOpacity(0.4),
                                  blurRadius: 15,
                                  offset: const Offset(0, 6),
                                  spreadRadius: -2,
                                ),
                              ],
                            ),
                            child: ElevatedButton(
                              onPressed: _isLoading ? null : _handleForgotPassword,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: Colors.transparent,
                                shadowColor: Colors.transparent,
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(vertical: 18),
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
                                ),
                                elevation: 0,
                              ),
                              child: _isLoading
                                  ? const SizedBox(
                                      height: 22,
                                      width: 22,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2.5,
                                        valueColor: AlwaysStoppedAnimation<Color>(
                                          Colors.white,
                                        ),
                                      ),
                                    )
                                  : Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const FaIcon(
                                          FontAwesomeIcons.paperPlane,
                                          size: 20,
                                        ),
                                        const SizedBox(width: 10),
                                        Text(
                                          'ارسال لینک بازیابی',
                                          style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.w700,
                                            fontFamily: AppTheme.fontPrimary,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),

                          // Resend Button (shown after successful send)
                          if (_successMessage != null) ...[
                            const SizedBox(height: 16),
                            TextButton(
                              onPressed: _isLoading ? null : _handleResend,
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    FontAwesomeIcons.arrowRotateRight,
                                    size: 16,
                                    color: isDark 
                                        ? Colors.white.withOpacity(0.8)
                                        : Colors.black54,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'ارسال مجدد',
                                    textDirection: TextDirection.rtl,
                                    style: TextStyle(
                                      color: isDark 
                                          ? Colors.white.withOpacity(0.8)
                                          : Colors.black54,
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      fontFamily: AppTheme.fontPrimary,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 32),

                  // Back to Login Link
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).pop();
                    },
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    ),
                    child: Text(
                      'بازگشت به ورود',
                      textDirection: TextDirection.rtl,
                      style: TextStyle(
                        color: isDark 
                            ? Colors.white.withOpacity(0.9)
                            : Colors.black87,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                        fontFamily: AppTheme.fontPrimary,
                        decoration: TextDecoration.underline,
                        decorationThickness: 1.5,
                      ),
                    ),
                  ),
                  const SizedBox(height: 20),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

