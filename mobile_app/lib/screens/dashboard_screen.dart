import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class DashboardScreen extends StatelessWidget {
  const DashboardScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
      appBar: AppBar(
        title: const Text('داشبورد'),
      ),
      body: Center(
        child: Text(
          'صفحه داشبورد',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}

