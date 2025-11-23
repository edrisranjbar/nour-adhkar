import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../config.dart';

class AppFooter extends StatelessWidget {
  const AppFooter({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.only(top: 48),
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF262626) : AppTheme.brandSecondary,
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(16),
          topRight: Radius.circular(16),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 20,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // App Info Section
          _FooterSection(
            title: 'اذکار نور',
            description:
                'مجموعه‌ای جامع از اذکار و ادعیه اسلامی در یک اپلیکیشن ساده و کاربرپسند',
          ),
          const SizedBox(height: 32),
          // Quick Links
          _FooterLinksSection(
            title: 'لینک‌های سریع',
            links: [
              {'title': 'اذکار صبحگاه', 'onTap': () {}},
              {'title': 'اذکار شامگاه', 'onTap': () {}},
              {'title': 'تسبیح شمار', 'onTap': () {}},
              {'title': 'مقالات', 'onTap': () {}},
            ],
          ),
          const SizedBox(height: 32),
          // Contact Info
          _ContactSection(),
          const SizedBox(height: 32),
          // Footer Bottom
          Container(
            padding: const EdgeInsets.only(top: 24),
            decoration: BoxDecoration(
              border: Border(
                top: BorderSide(
                  color: Colors.white.withOpacity(0.2),
                  width: 1,
                ),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '© ${DateTime.now().year} اذکار نور - تمامی حقوق محفوظ است.',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.7),
                    fontSize: 14,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
                Text(
                  '${AppConfig.appVersion} نسخه آزمایشی',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.7),
                    fontSize: 14,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _FooterSection extends StatelessWidget {
  final String title;
  final String description;

  const _FooterSection({
    required this.title,
    required this.description,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.w600,
            color: Colors.white,
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
        const SizedBox(height: 12),
        Container(
          width: 120,
          height: 3,
          decoration: BoxDecoration(
            color: Colors.white.withOpacity(0.5),
            borderRadius: BorderRadius.circular(3),
          ),
        ),
        const SizedBox(height: 16),
        Text(
          description,
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withOpacity(0.9),
            fontFamily: AppTheme.fontPrimary,
            height: 1.7,
          ),
          textAlign: TextAlign.justify,
        ),
      ],
    );
  }
}

class _FooterLinksSection extends StatelessWidget {
  final String title;
  final List<Map<String, dynamic>> links;

  const _FooterLinksSection({
    required this.title,
    required this.links,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 3,
              height: 18,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
            const SizedBox(width: 12),
            Text(
              title,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w500,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        ...links.map((link) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: GestureDetector(
                onTap: link['onTap'] as VoidCallback?,
                child: Row(
                  children: [
                    Text(
                      '›',
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.8),
                        fontSize: 20,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      link['title'],
                      style: TextStyle(
                        fontSize: 16,
                        color: Colors.white.withOpacity(0.9),
                        fontFamily: AppTheme.fontPrimary,
                      ),
                    ),
                  ],
                ),
              ),
            )),
      ],
    );
  }
}

class _ContactSection extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              width: 3,
              height: 18,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(3),
              ),
            ),
            const SizedBox(width: 12),
            const Text(
              'ارتباط با ما',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w500,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),
        _ContactItem(
          icon: FontAwesomeIcons.envelope,
          text: 'info@adhkar.ir',
        ),
        const SizedBox(height: 12),
        _ContactItem(
          icon: FontAwesomeIcons.globe,
          text: 'www.adhkar.ir',
        ),
        const SizedBox(height: 20),
        Row(
          children: [
            _SocialLink(icon: FontAwesomeIcons.github),
            const SizedBox(width: 16),
            _SocialLink(icon: FontAwesomeIcons.envelope),
          ],
        ),
      ],
    );
  }
}

class _ContactItem extends StatelessWidget {
  final IconData icon;
  final String text;

  const _ContactItem({
    required this.icon,
    required this.text,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(
          icon,
          color: Colors.white.withOpacity(0.9),
          size: 18,
        ),
        const SizedBox(width: 12),
        Text(
          text,
          style: TextStyle(
            fontSize: 16,
            color: Colors.white.withOpacity(0.9),
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
      ],
    );
  }
}

class _SocialLink extends StatelessWidget {
  final IconData icon;

  const _SocialLink({required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 40,
      height: 40,
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.15),
        shape: BoxShape.circle,
      ),
      child: Icon(
        icon,
        color: Colors.white,
        size: 20,
      ),
    );
  }
}

