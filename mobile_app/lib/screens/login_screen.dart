import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../services/auth_service.dart';

class LoginScreen extends StatefulWidget {
  final VoidCallback? onLoginSuccess;

  const LoginScreen({
    super.key,
    this.onLoginSuccess,
  });

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
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _togglePasswordVisibility() {
    setState(() {
      _passwordVisible = !_passwordVisible;
    });
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_isLoading) {
      return;
    }

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final result = await AuthService.login(
        _emailController.text.trim(),
        _passwordController.text,
      );

      if (mounted) {
        if (result['success'] == true) {
          // Login successful
          if (widget.onLoginSuccess != null) {
            widget.onLoginSuccess!();
          }
        } else {
          setState(() {
            _errorMessage = result['message'] ?? 'خطا در ورود به سیستم';
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'خطا در ورود به سیستم';
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
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 400),
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // Logo
                  Container(
                    width: 100,
                    height: 100,
                    margin: const EdgeInsets.only(bottom: 32),
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
                          size: 60,
                          color: AppTheme.brandSecondary,
                        );
                      },
                    ),
                  ),

                  // Title
                  Text(
                    'وارد شوید',
                    textAlign: TextAlign.center,
                    textDirection: TextDirection.rtl,
                    style: TextStyle(
                      fontSize: 32,
                      fontWeight: FontWeight.bold,
                      color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                  const SizedBox(height: 32),

                  // Login Form Card
                  Container(
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkBgSecondary : AppTheme.bgSecondary,
                      borderRadius: BorderRadius.circular(16),
                      boxShadow: [
                        BoxShadow(
                          color: isDark ? AppTheme.uiShadow : AppTheme.uiShadowLight,
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    padding: const EdgeInsets.all(24),
                    child: Form(
                      key: _formKey,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Email Field
                          Text(
                            'ایمیل',
                            textDirection: TextDirection.rtl,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _emailController,
                            keyboardType: TextInputType.emailAddress,
                            textDirection: TextDirection.ltr,
                            textAlign: TextAlign.left,
                            style: TextStyle(
                              color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                            decoration: InputDecoration(
                              hintText: 'example@email.com',
                              hintStyle: TextStyle(
                                color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                                fontFamily: AppTheme.fontPrimary,
                              ),
                              filled: true,
                              fillColor: isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: isDark ? Colors.grey[700]! : Colors.grey[300]!,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: isDark ? Colors.grey[700]! : Colors.grey[300]!,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                  color: AppTheme.brandPrimary,
                                  width: 2,
                                ),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 14,
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
                          ),
                          const SizedBox(height: 20),

                          // Password Field
                          Text(
                            'رمز عبور',
                            textDirection: TextDirection.rtl,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w500,
                              color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                          const SizedBox(height: 8),
                          TextFormField(
                            controller: _passwordController,
                            obscureText: !_passwordVisible,
                            textDirection: TextDirection.ltr,
                            textAlign: TextAlign.left,
                            style: TextStyle(
                              color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                            decoration: InputDecoration(
                              hintText: 'رمز عبور خود را وارد کنید',
                              hintStyle: TextStyle(
                                color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                                fontFamily: AppTheme.fontPrimary,
                              ),
                              filled: true,
                              fillColor: isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary,
                              border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: isDark ? Colors.grey[700]! : Colors.grey[300]!,
                                ),
                              ),
                              enabledBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: BorderSide(
                                  color: isDark ? Colors.grey[700]! : Colors.grey[300]!,
                                ),
                              ),
                              focusedBorder: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12),
                                borderSide: const BorderSide(
                                  color: AppTheme.brandPrimary,
                                  width: 2,
                                ),
                              ),
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 16,
                                vertical: 14,
                              ),
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _passwordVisible
                                      ? FontAwesomeIcons.eyeSlash
                                      : FontAwesomeIcons.eye,
                                  color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
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
                          const SizedBox(height: 24),

                          // Error Message
                          if (_errorMessage != null)
                            Container(
                              padding: const EdgeInsets.all(12),
                              margin: const EdgeInsets.only(bottom: 16),
                              decoration: BoxDecoration(
                                color: AppTheme.danger.withOpacity(0.1),
                                borderRadius: BorderRadius.circular(8),
                                border: Border.all(
                                  color: AppTheme.danger.withOpacity(0.3),
                                ),
                              ),
                              child: Text(
                                _errorMessage!,
                                textDirection: TextDirection.rtl,
                                style: TextStyle(
                                  color: AppTheme.danger,
                                  fontSize: 14,
                                  fontWeight: FontWeight.w500,
                                  fontFamily: AppTheme.fontPrimary,
                                ),
                              ),
                            ),

                          // Login Button
                          ElevatedButton(
                            onPressed: _isLoading ? null : _handleLogin,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: isDark
                                  ? AppTheme.darkBrandPrimary
                                  : AppTheme.brandPrimary,
                              foregroundColor: AppTheme.textLight,
                              padding: const EdgeInsets.symmetric(vertical: 16),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                              elevation: 2,
                            ),
                            child: _isLoading
                                ? const SizedBox(
                                    height: 20,
                                    width: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      valueColor: AlwaysStoppedAnimation<Color>(
                                        AppTheme.textLight,
                                      ),
                                    ),
                                  )
                                : Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const FaIcon(
                                        FontAwesomeIcons.signInAlt,
                                        size: 18,
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        'ورود',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.w600,
                                          fontFamily: AppTheme.fontPrimary,
                                        ),
                                      ),
                                    ],
                                  ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Additional Links
                  Column(
                    children: [
                      TextButton(
                        onPressed: () {
                          // Navigate to register
                          print('Navigate to register');
                        },
                        child: Text(
                          'حساب کاربری ندارید؟ اینجا ثبت نام کنید',
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                      TextButton(
                        onPressed: () {
                          // Navigate to forgot password
                          print('Navigate to forgot password');
                        },
                        child: Text(
                          'فراموشی رمز عبور',
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                    ],
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

