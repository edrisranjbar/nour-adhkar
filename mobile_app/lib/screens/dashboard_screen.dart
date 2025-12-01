import 'package:flutter/material.dart';
import '../widgets/app_header.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/dashboard_service.dart';
import '../widgets/dashboard/profile_section.dart';
import '../widgets/dashboard/stats_section.dart';
import '../widgets/dashboard/streak_calendar.dart';
import '../widgets/dashboard/badges_section.dart';
import '../widgets/dashboard/recent_activities_section.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _userStats;
  List<Map<String, dynamic>> _recentActivities = [];
  List<Map<String, dynamic>> _badges = [];
  Map<String, dynamic>? _user;
  bool _isLoading = true;
  bool _isAuthenticated = false;
  int? _heartScore;
  int? _streak;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    try {
      // Load user data
      final user = await AuthService.getUser();
      final isAuth = await AuthService.isAuthenticated();

      // Load stats and dashboard data
      final stats = await ApiService.getUserStats();
      final dashboard = await ApiService.getDashboard();
      final badges = await ApiService.getUserBadges();

      // Normalize badges to ensure proper types
      final normalizedBadges = DashboardService.normalizeBadges(badges);

      if (mounted) {
        setState(() {
          _user = user;
          _isAuthenticated = isAuth;
          _userStats = stats;
          _heartScore = stats?['heart_score'] ?? user?['heart_score'] ?? 0;
          _streak = stats?['streak'] ?? user?['streak'] ?? 0;
          _recentActivities = dashboard?['recent_activities'] ?? [];
          _badges = normalizedBadges;
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
      color: Colors.white,
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
                      // Header
                      AppHeader(
                        title: 'داشبورد',
                        description: _user?['name'] ?? _user?['email'] ?? 'کاربر',
                        isAuthenticated: _isAuthenticated,
                        heartScore: _heartScore,
                        streak: _streak,
                        onMenuTap: () {
                          Scaffold.of(context).openDrawer();
                        },
                      ),

                      // Profile Section
                      ProfileSection(
                        user: _user,
                        isDark: isDark,
                      ),

                      // Divider
                      Divider(height: 1, thickness: 1, color: Colors.grey[200]),

                      // Stats Section
                      StatsSection(
                        userStats: _userStats,
                        heartScore: _heartScore,
                        streak: _streak,
                        isDark: isDark,
                      ),

                      // Divider
                      Divider(height: 1, thickness: 1, color: Colors.grey[200]),

                      // Streak Calendar
                      StreakCalendar(
                        userStats: _userStats,
                        isDark: isDark,
                      ),

                      // Divider
                      Divider(height: 1, thickness: 1, color: Colors.grey[200]),

                      // Badges Section
                      BadgesSection(
                        badges: _badges,
                        userStats: _userStats,
                        streak: _streak,
                        isDark: isDark,
                      ),

                      // Recent Activities
                      if (_recentActivities.isNotEmpty) ...[
                        // Divider
                        Divider(height: 1, thickness: 1, color: Colors.grey[200]),
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
