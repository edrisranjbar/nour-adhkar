import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
      appBar: AppBar(
        title: const Text('تنظیمات'),
      ),
      body: Center(
        child: Text(
          'صفحه تنظیمات',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}

