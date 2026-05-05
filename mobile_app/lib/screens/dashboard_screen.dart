import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../widgets/dashboard/profile_section.dart';
import '../widgets/dashboard/stats_section.dart';
import '../widgets/dashboard/streak_calendar.dart';
import '../widgets/dashboard/recent_activities_section.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _userStats;
  List<Map<String, dynamic>> _recentActivities = [];
  Map<String, dynamic>? _user;
  bool _isLoading = true;
  bool _isAuthenticated = false;
  int? _streak;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    try {
      final user = await AuthService.getUser();
      final isAuth = await AuthService.isAuthenticated();

      final stats = await ApiService.getUserStats();
      final dashboard = await ApiService.getDashboard();

      if (mounted) {
        setState(() {
          _user = user;
          _isAuthenticated = isAuth;
          _userStats = stats;
          _streak = stats?['streak'] ?? user?['streak'] ?? 0;
          _recentActivities = dashboard?['recent_activities'] ?? [];
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      color: isDark ? AppTheme.darkBgPrimary : Colors.white,
      child: SafeArea(
        child: _isLoading
            ? const Center(
                child: CircularProgressIndicator(),
              )
            : RefreshIndicator(
                onRefresh: _loadData,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Column(
                    children: [
                      AppHeader(
                        title: 'داشبورد',
                        description: _user?['name'] ?? _user?['email'] ?? 'کاربر',
                        isAuthenticated: _isAuthenticated,
                        streak: _streak,
                        onMenuTap: () {
                          Scaffold.of(context).openDrawer();
                        },
                      ),

                      ProfileSection(
                        user: _user,
                        isDark: isDark,
                      ),

                      Divider(
                        height: 1,
                        thickness: 1,
                        color: isDark ? AppTheme.darkBgTertiary : Colors.grey[200],
                      ),

                      StatsSection(
                        userStats: _userStats,
                        streak: _streak,
                        isDark: isDark,
                      ),

                      Divider(
                        height: 1,
                        thickness: 1,
                        color: isDark ? AppTheme.darkBgTertiary : Colors.grey[200],
                      ),

                      StreakCalendar(
                        userStats: _userStats,
                        isDark: isDark,
                      ),

                      if (_recentActivities.isNotEmpty) ...[
                        Divider(
                          height: 1,
                          thickness: 1,
                          color: isDark ? AppTheme.darkBgTertiary : Colors.grey[200],
                        ),
                        RecentActivitiesSection(
                          activities: _recentActivities,
                          isDark: isDark,
                        ),
                      ],
                    ],
                  ),
                ),
              ),
      ),
    );
  }
}
