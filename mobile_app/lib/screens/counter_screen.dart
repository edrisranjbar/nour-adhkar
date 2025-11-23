import 'package:flutter/material.dart';
import '../theme/app_theme.dart';

class CounterScreen extends StatelessWidget {
  const CounterScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    
    return Scaffold(
      backgroundColor: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
      appBar: AppBar(
        title: const Text('تسبیح'),
      ),
      body: Center(
        child: Text(
          'صفحه تسبیح',
          style: Theme.of(context).textTheme.headlineMedium,
        ),
      ),
    );
  }
}

