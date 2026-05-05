import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';

class PrivacyPolicyScreen extends StatefulWidget {
  const PrivacyPolicyScreen({super.key});

  @override
  State<PrivacyPolicyScreen> createState() => _PrivacyPolicyScreenState();
}

class _PrivacyPolicyScreenState extends State<PrivacyPolicyScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
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
            child: Column(
              children: [
                // Header
                Container(
                  padding: const EdgeInsets.all(20),
                  child: Row(
                    children: [
                      IconButton(
                        onPressed: () {
                          Navigator.of(context).pop();
                        },
                        icon: Icon(
                          FontAwesomeIcons.arrowRight,
                          color: isDark ? Colors.white : Colors.black87,
                          size: 20,
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Text(
                          'سیاست حفظ حریم خصوصی',
                          textAlign: TextAlign.center,
                          textDirection: TextDirection.rtl,
                          style: TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                            color: isDark ? Colors.white : Colors.black87,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      ),
                      const SizedBox(width: 48), // Balance the back button
                    ],
                  ),
                ),

                // Content
                Expanded(
                  child: Container(
                    margin: const EdgeInsets.symmetric(horizontal: 20),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E1E1E) : Colors.white,
                      borderRadius: BorderRadius.circular(16),
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
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Column(
                        children: [
                          // Scrollable Content
                          Expanded(
                            child: SingleChildScrollView(
                              controller: _scrollController,
                              padding: const EdgeInsets.all(24),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Title
                                  Text(
                                    'سیاست حفظ حریم خصوصی برنامه اذکار نور',
                                    textDirection: TextDirection.rtl,
                                    style: TextStyle(
                                      fontSize: 18,
                                      fontWeight: FontWeight.w700,
                                      color: isDark
                                          ? Colors.white
                                          : Colors.black87,
                                      fontFamily: AppTheme.fontPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 16),

                                  // Last Updated
                                  Text(
                                    'آخرین بروزرسانی: دی ۱۴۰۳',
                                    textDirection: TextDirection.rtl,
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark
                                          ? Colors.white.withOpacity(0.6)
                                          : Colors.black54,
                                      fontFamily: AppTheme.fontPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 24),

                                  // Introduction
                                  _buildSectionTitle('مقدمه'),
                                  _buildParagraph(
                                    'با عرض سلام و احترام،\n\n'
                                    'ما در برنامه اذکار نور به حفظ حریم خصوصی و امنیت اطلاعات شما نهایت اهمیت را قائل هستیم. این سیاست حفظ حریم خصوصی توضیح می‌دهد که چگونه اطلاعات شما را جمع‌آوری، استفاده و محافظت می‌کنیم.\n\n'
                                    'لطفاً این سیاست را با دقت مطالعه فرمایید. با استفاده از برنامه اذکار نور، شما موافقت خود را با این سیاست اعلام می‌کنید.',
                                    isDark,
                                  ),
                                  const SizedBox(height: 24),

                                  // Information We Collect
                                  _buildSectionTitle('اطلاعات جمع‌آوری شده'),
                                  _buildParagraph(
                                    'برنامه اذکار نور اطلاعات زیر را از شما جمع‌آوری می‌کند:',
                                    isDark,
                                  ),
                                  const SizedBox(height: 12),

                                  // Registration Information
                                  _buildSubSectionTitle('اطلاعات ثبت‌نام:'),
                                  _buildBulletPoint(
                                    'نام کامل: برای نمایش در پروفایل و ارتباط شخصی‌سازی شده',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'ایمیل: برای احراز هویت، ارسال اعلان‌ها و بازیابی رمز عبور',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'رمز عبور: برای امنیت حساب کاربری شما (رمز عبور به صورت رمزگذاری شده ذخیره می‌شود)',
                                    isDark,
                                  ),

                                  const SizedBox(height: 16),

                                  // Usage Information
                                  _buildSubSectionTitle(
                                    'اطلاعات استفاده از برنامه:',
                                  ),
                                  _buildBulletPoint(
                                    'امتیاز و پیشرفت: برای نمایش پیشرفت شما در انجام اذکار',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'اذکار مورد علاقه: برای ذخیره اذکاری که می‌خواهید سریع‌تر به آن‌ها دسترسی داشته باشید',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'آمار روزانه: تعداد اذکار انجام شده، زمان آخرین ورود و آمار کلی',
                                    isDark,
                                  ),

                                  const SizedBox(height: 16),

                                  // Donation Information
                                  _buildSubSectionTitle(
                                    'اطلاعات مربوط به کمک‌های مالی (اختیاری):',
                                  ),
                                  _buildBulletPoint(
                                    'مبلغ کمک: برای پردازش پرداخت',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'شناسه تراکنش: برای پیگیری و تأیید پرداخت',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'شماره کارت (۴ رقم آخر): برای شناسایی تراکنش',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'نام و ایمیل: برای رسید کمک مالی',
                                    isDark,
                                  ),

                                  const SizedBox(height: 24),

                                  // How We Use Information
                                  _buildSectionTitle('نحوه استفاده از اطلاعات'),
                                  _buildParagraph(
                                    'اطلاعات شما برای اهداف زیر استفاده می‌شود:',
                                    isDark,
                                  ),
                                  const SizedBox(height: 12),

                                  _buildBulletPoint(
                                    'ارائه خدمات: ایجاد و مدیریت حساب کاربری شما',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'شخصی‌سازی تجربه: نمایش پیشرفت، نشان‌ها و آمار شخصی',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'ارتباط: ارسال اعلان‌های مهم، یادآورها و پشتیبانی',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'امنیت: جلوگیری از سوءاستفاده و فعالیت‌های مشکوک',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'بهبود برنامه: تحلیل استفاده برای توسعه بهتر ویژگی‌ها',
                                    isDark,
                                  ),

                                  const SizedBox(height: 24),

                                  // Information Sharing
                                  _buildSectionTitle('اشتراک‌گذاری اطلاعات'),
                                  _buildParagraph(
                                    'ما متعهد هستیم که اطلاعات شما را در هیچ شرایطی با شخص یا سازمان ثالثی به اشتراک نگذاریم، مگر در موارد زیر:',
                                    isDark,
                                  ),
                                  const SizedBox(height: 12),

                                  _buildBulletPoint(
                                    'با رضایت صریح شما',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'در صورت لزوم قانونی و با حکم قضایی',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'برای پردازش پرداخت‌های کمک مالی (فقط اطلاعات ضروری پرداخت)',
                                    isDark,
                                  ),

                                  const SizedBox(height: 24),

                                  // User Rights
                                  _buildSectionTitle('حقوق شما'),
                                  _buildParagraph(
                                    'شما حقوق زیر را در مورد اطلاعات شخصی خود دارید:',
                                    isDark,
                                  ),
                                  const SizedBox(height: 12),

                                  _buildBulletPoint(
                                    'دسترسی به اطلاعات خود',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'تصحیح اطلاعات نادرست',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'حذف حساب کاربری و اطلاعات',
                                    isDark,
                                  ),
                                  _buildBulletPoint(
                                    'لغو اشتراک از اعلان‌ها',
                                    isDark,
                                  ),

                                  const SizedBox(height: 24),

                                  // Contact Us
                                  _buildSectionTitle('تماس با ما'),
                                  _buildParagraph(
                                    'اگر سؤالی درباره این سیاست حفظ حریم خصوصی دارید، می‌توانید از طریق ایمیل با ما تماس بگیرید.',
                                    isDark,
                                  ),
                                  const SizedBox(height: 12),

                                  _buildParagraph(
                                    'ایمیل پشتیبانی: edrisranjbar.dev@gmail.com\n\n'
                                    'ما در اسرع وقت به پیام‌های شما پاسخ خواهیم داد.',
                                    isDark,
                                  ),

                                  const SizedBox(height: 24),

                                  // Updates
                                  _buildSectionTitle('بروزرسانی سیاست'),
                                  _buildParagraph(
                                    'این سیاست ممکن است در آینده بروزرسانی شود. تغییرات مهم به اطلاع شما خواهد رسید.',
                                    isDark,
                                  ),

                                  const SizedBox(height: 40),
                                ],
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
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Text(
      title,
      textDirection: TextDirection.rtl,
      style: TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.w700,
        color: isDark ? Colors.white : Colors.black87,
        fontFamily: AppTheme.fontPrimary,
      ),
    );
  }

  Widget _buildSubSectionTitle(String title) {
    return Text(
      title,
      textDirection: TextDirection.rtl,
      style: TextStyle(
        fontSize: 14,
        fontWeight: FontWeight.w600,
        color: AppTheme.brandPrimary,
        fontFamily: AppTheme.fontPrimary,
      ),
    );
  }

  Widget _buildParagraph(String text, bool isDark) {
    return Text(
      text,
      textDirection: TextDirection.rtl,
      style: TextStyle(
        fontSize: 14,
        height: 1.6,
        color: isDark ? Colors.white.withOpacity(0.9) : Colors.black87,
        fontFamily: AppTheme.fontPrimary,
        fontWeight: FontWeight.w400,
      ),
    );
  }

  Widget _buildBulletPoint(String text, bool isDark) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8, right: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            '•',
            textDirection: TextDirection.rtl,
            style: TextStyle(
              fontSize: 14,
              color: AppTheme.brandPrimary,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              text,
              textDirection: TextDirection.rtl,
              style: TextStyle(
                fontSize: 14,
                height: 1.5,
                color: isDark ? Colors.white.withOpacity(0.9) : Colors.black87,
                fontFamily: AppTheme.fontPrimary,
                fontWeight: FontWeight.w400,
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
