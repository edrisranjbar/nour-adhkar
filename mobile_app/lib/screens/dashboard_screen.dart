import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:shamsi_date/shamsi_date.dart';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../utils/number_formatter.dart';

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
      final normalizedBadges = badges.map((badge) {
        final unlocked = badge['unlocked'];
        bool unlockedBool = false;
        if (unlocked is bool) {
          unlockedBool = unlocked;
        } else if (unlocked is int) {
          unlockedBool = unlocked != 0;
        } else if (unlocked is String) {
          unlockedBool = unlocked == '1' || unlocked.toLowerCase() == 'true';
        }
        
        final progress = badge['progress'];
        double progressDouble = 0.0;
        if (progress is double) {
          progressDouble = progress;
        } else if (progress is int) {
          progressDouble = progress.toDouble();
        } else if (progress is String) {
          progressDouble = double.tryParse(progress) ?? 0.0;
        }
        
        return {
          ...badge,
          'unlocked': unlockedBool,
          'progress': progressDouble,
        };
      }).toList();
      
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
      color: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
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
                      _buildProfileSection(isDark),

                      // Stats Cards
                      _buildStatsSection(isDark),

                      // Streak Calendar
                      _buildStreakCalendar(isDark),

                      // Badges Section - Always show
                      _buildBadgesSection(isDark),

                      // Recent Activities
                      if (_recentActivities.isNotEmpty)
                        _buildRecentActivitiesSection(isDark),
                    ],
                  ),
                ),
              ),
      ),
    );
  }

  Widget _buildProfileSection(bool isDark) {
    final userName = _user?['name'] ?? _user?['email'] ?? 'کاربر';
    final profilePhotoUrl = _user?['avatar'] ??
        _user?['profile_photo'] ??
        _user?['photo'];

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          // Profile Photo
          CircleAvatar(
            radius: 40,
            backgroundColor: isDark
                ? AppTheme.darkBrandSecondary
                : AppTheme.brandSecondary,
            backgroundImage: profilePhotoUrl != null && profilePhotoUrl.isNotEmpty
                ? NetworkImage(profilePhotoUrl)
                : null,
            child: profilePhotoUrl == null || profilePhotoUrl.isEmpty
                ? Icon(
                    FontAwesomeIcons.user,
                    color: Colors.white,
                    size: 32,
                  )
                : null,
          ),
          const SizedBox(width: 16),
          // User Info
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  userName,
                  style: TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.w600,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
                if (_user?['email'] != null) ...[
                  const SizedBox(height: 4),
                  Text(
                    _user!['email'],
                    style: TextStyle(
                      fontSize: 14,
                      color: isDark
                          ? AppTheme.darkTextSecondary
                          : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsSection(bool isDark) {
    final totalDhikrs = _userStats?['total_dhikrs'] ?? 0;
    final todayCount = _userStats?['today_count'] ?? 0;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'آمار شما',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: isDark
                  ? AppTheme.darkBrandPrimary
                  : AppTheme.brandPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _StatCard(
                  icon: FontAwesomeIcons.fire,
                  label: 'استریک',
                  value: _streak ?? 0,
                  color: Colors.orange,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _StatCard(
                  icon: FontAwesomeIcons.heart,
                  label: 'امتیاز قلب',
                  value: _heartScore ?? 0,
                  color: Colors.red,
                  isDark: isDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _StatCard(
                  icon: FontAwesomeIcons.book,
                  label: 'کل اذکار',
                  value: totalDhikrs,
                  color: AppTheme.brandPrimary,
                  isDark: isDark,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _StatCard(
                  icon: FontAwesomeIcons.calendarDay,
                  label: 'امروز',
                  value: todayCount,
                  color: AppTheme.brandSecondary,
                  isDark: isDark,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStreakCalendar(bool isDark) {
    final completedDates = _userStats?['completed_dates'] as List? ?? [];
    final today = Jalali.now();
    
    // Get last 7 days
    final last7Days = List.generate(7, (index) {
      return today.addDays(-(6 - index));
    });

    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(
                FontAwesomeIcons.calendarDays,
                size: 20,
                color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
              ),
              const SizedBox(width: 8),
              Text(
                '۷ روز گذشته',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: last7Days.map((jalaliDate) {
              final dateStr = '${jalaliDate.year}-${jalaliDate.month.toString().padLeft(2, '0')}-${jalaliDate.day.toString().padLeft(2, '0')}';
              final isCompleted = completedDates.contains(dateStr);
              final isToday = jalaliDate.day == today.day && 
                             jalaliDate.month == today.month && 
                             jalaliDate.year == today.year;
              
              return _DayCircle(
                day: jalaliDate.day,
                monthName: _getJalaliMonthName(jalaliDate.month),
                isCompleted: isCompleted,
                isToday: isToday,
                isDark: isDark,
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  String _getJalaliMonthName(int month) {
    const months = [
      'فرو', 'ارد', 'خرد', 'تیر', 'مرد', 'شهر',
      'مهر', 'آبا', 'آذر', 'دی', 'بهم', 'اسف'
    ];
    return months[month - 1];
  }

  Widget _buildBadgesSection(bool isDark) {
    // If no badges from API, show default achievement badges
    final displayBadges = _badges.isNotEmpty ? _badges : _getDefaultBadges();
    
    return Container(
      margin: const EdgeInsets.all(16),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Icon(
                    FontAwesomeIcons.trophy,
                    size: 20,
                    color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'دستاوردها',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w600,
                      color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ),
              Text(
                '${displayBadges.where((b) => b['unlocked'] == true).length}/${displayBadges.length}',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 140,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: displayBadges.length,
              itemBuilder: (context, index) {
                final badge = displayBadges[index];
                return _AchievementBadge(
                  badge: badge,
                  isDark: isDark,
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  List<Map<String, dynamic>> _getDefaultBadges() {
    // Helper function to safely get int from stats
    int _getIntFromStats(String key) {
      final value = _userStats?[key];
      if (value == null) return 0;
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      if (value is double) return value.toInt();
      return 0;
    }

    final totalDhikrs = _getIntFromStats('total_dhikrs');
    final favoriteCount = _getIntFromStats('favorite_count');
    final currentStreak = _streak ?? 0;

    return [
      {
        'name': 'آغازگر',
        'description': 'اولین ذکر خود را تمام کنید',
        'icon': 'star',
        'rarity': 'bronze',
        'unlocked': totalDhikrs > 0,
        'progress': totalDhikrs >= 1 ? 1.0 : 0.0,
      },
      {
        'name': 'پیگیر',
        'description': '۳ روز پشت سر هم ذکر بخوانید',
        'icon': 'fire',
        'rarity': 'bronze',
        'unlocked': currentStreak >= 3,
        'progress': (currentStreak / 3).clamp(0.0, 1.0),
      },
      {
        'name': 'مداوم',
        'description': '۷ روز پشت سر هم ذکر بخوانید',
        'icon': 'fire',
        'rarity': 'silver',
        'unlocked': currentStreak >= 7,
        'progress': (currentStreak / 7).clamp(0.0, 1.0),
      },
      {
        'name': 'استاد',
        'description': '۱۰۰ ذکر کامل کنید',
        'icon': 'crown',
        'rarity': 'gold',
        'unlocked': totalDhikrs >= 100,
        'progress': (totalDhikrs / 100).clamp(0.0, 1.0),
      },
      {
        'name': 'محبوب',
        'description': '۱۰ ذکر را به علاقه‌مندی اضافه کنید',
        'icon': 'heart',
        'rarity': 'silver',
        'unlocked': favoriteCount >= 10,
        'progress': (favoriteCount / 10).clamp(0.0, 1.0),
      },
      {
        'name': 'صبحگاه',
        'description': 'اذکار صبح را ۳۰ بار بخوانید',
        'icon': 'sun',
        'rarity': 'gold',
        'unlocked': false,
        'progress': 0.5,
      },
    ];
  }

  Widget _buildRecentActivitiesSection(bool isDark) {
    return Container(
      margin: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'فعالیت‌های اخیر',
            style: TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.w600,
              color: isDark
                  ? AppTheme.darkBrandPrimary
                  : AppTheme.brandPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 12),
          ..._recentActivities.take(5).map((activity) {
            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isDark
                    ? AppTheme.darkBgTertiary
                    : AppTheme.bgSecondary,
                borderRadius: BorderRadius.circular(12),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.05),
                    blurRadius: 4,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: isDark
                          ? AppTheme.darkBrandSecondary
                          : AppTheme.brandSecondary,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(
                      activity['type'] == 'dhikr'
                          ? FontAwesomeIcons.book
                          : activity['type'] == 'contribution'
                              ? FontAwesomeIcons.handHoldingHeart
                              : FontAwesomeIcons.gift,
                      color: Colors.white,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          activity['description'] ?? 'فعالیت',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: isDark
                                ? AppTheme.darkTextPrimary
                                : AppTheme.textPrimary,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                        if (activity['date'] != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            _formatDate(activity['date']),
                            style: TextStyle(
                              fontSize: 12,
                              color: isDark
                                  ? AppTheme.darkTextSecondary
                                  : AppTheme.textSecondary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            );
          }).toList(),
        ],
      ),
    );
  }

  String _formatDate(dynamic date) {
    if (date is String) {
      try {
        final dateTime = DateTime.parse(date);
        final now = DateTime.now();
        final difference = now.difference(dateTime);

        if (difference.inDays == 0) {
          return 'امروز';
        } else if (difference.inDays == 1) {
          return 'دیروز';
        } else if (difference.inDays < 7) {
          return '${difference.inDays} روز پیش';
        } else {
          return '${dateTime.year}/${dateTime.month}/${dateTime.day}';
        }
      } catch (e) {
        return date;
      }
    }
    return date.toString();
  }
}

class _StatCard extends StatelessWidget {
  final IconData icon;
  final String label;
  final int value;
  final Color color;
  final bool isDark;

  const _StatCard({
    required this.icon,
    required this.label,
    required this.value,
    required this.color,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        children: [
          Icon(
            icon,
            color: color,
            size: 32,
          ),
          const SizedBox(height: 8),
          Text(
            NumberFormatter.formatNumber(value),
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.w700,
              color: isDark
                  ? AppTheme.darkTextPrimary
                  : AppTheme.textPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: isDark
                  ? AppTheme.darkTextSecondary
                  : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

class _DayCircle extends StatelessWidget {
  final int day;
  final String monthName;
  final bool isCompleted;
  final bool isToday;
  final bool isDark;

  const _DayCircle({
    required this.day,
    required this.monthName,
    required this.isCompleted,
    required this.isToday,
    required this.isDark,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: isCompleted
                ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                : (isDark ? AppTheme.darkBgSecondary : AppTheme.bgTertiary),
            shape: BoxShape.circle,
            border: isToday
                ? Border.all(
                    color: isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary,
                    width: 2,
                  )
                : null,
          ),
          child: Center(
            child: isCompleted
                ? const Icon(
                    Icons.check,
                    color: Colors.white,
                    size: 24,
                  )
                : Text(
                    NumberFormatter.formatNumber(day),
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: isDark
                          ? AppTheme.darkTextSecondary
                          : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
          ),
        ),
        const SizedBox(height: 4),
        Text(
          monthName,
          style: TextStyle(
            fontSize: 10,
            color: isDark
                ? AppTheme.darkTextSecondary
                : AppTheme.textSecondary,
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
      ],
    );
  }
}

class _AchievementBadge extends StatelessWidget {
  final Map<String, dynamic> badge;
  final bool isDark;

  const _AchievementBadge({
    required this.badge,
    required this.isDark,
  });

  IconData _getIconFromString(String? iconName) {
    switch (iconName) {
      case 'star':
        return FontAwesomeIcons.star;
      case 'fire':
        return FontAwesomeIcons.fire;
      case 'crown':
        return FontAwesomeIcons.crown;
      case 'heart':
        return FontAwesomeIcons.heart;
      case 'sun':
        return FontAwesomeIcons.sun;
      case 'moon':
        return FontAwesomeIcons.moon;
      default:
        return FontAwesomeIcons.trophy;
    }
  }

  Color _getRarityColor(String? rarity) {
    switch (rarity) {
      case 'bronze':
        return const Color(0xFFCD7F32);
      case 'silver':
        return const Color(0xFFC0C0C0);
      case 'gold':
        return const Color(0xFFFFD700);
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    final name = badge['name'] ?? 'نشان';
    final description = badge['description'] ?? '';
    final icon = _getIconFromString(badge['icon']);
    final rarity = badge['rarity'] ?? 'bronze';
    // Safely convert unlocked to bool (handles int 0/1, bool, or string)
    final unlockedValue = badge['unlocked'];
    final unlocked = unlockedValue == true || 
                     unlockedValue == 1 || 
                     (unlockedValue is String && unlockedValue == '1') ||
                     (unlockedValue is int && unlockedValue != 0);
    
    // Safely convert progress to double
    final progressValue = badge['progress'];
    double progress = 0.0;
    if (progressValue is double) {
      progress = progressValue;
    } else if (progressValue is int) {
      progress = progressValue.toDouble();
    } else if (progressValue is String) {
      progress = double.tryParse(progressValue) ?? 0.0;
    }

    return Container(
      width: 110,
      margin: const EdgeInsets.only(left: 12),
      child: Column(
        children: [
          // Badge Circle
          Stack(
            alignment: Alignment.center,
            children: [
              // Progress ring
              if (!unlocked && progress > 0)
                SizedBox(
                  width: 80,
                  height: 80,
                  child: CircularProgressIndicator(
                    value: progress.clamp(0.0, 1.0),
                    strokeWidth: 3,
                    backgroundColor: isDark
                        ? Colors.grey[800]
                        : Colors.grey[300],
                    valueColor: AlwaysStoppedAnimation<Color>(
                      _getRarityColor(rarity).withOpacity(0.7),
                    ),
                  ),
                ),
              // Badge container
              Container(
                width: 70,
                height: 70,
                decoration: BoxDecoration(
                  gradient: unlocked
                      ? LinearGradient(
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                          colors: [
                            _getRarityColor(rarity),
                            _getRarityColor(rarity).withOpacity(0.7),
                          ],
                        )
                      : null,
                  color: unlocked
                      ? null
                      : (isDark ? Colors.grey[800] : Colors.grey[300]),
                  shape: BoxShape.circle,
                  boxShadow: unlocked
                      ? [
                          BoxShadow(
                            color: _getRarityColor(rarity).withOpacity(0.4),
                            blurRadius: 12,
                            spreadRadius: 2,
                          ),
                        ]
                      : null,
                ),
                child: Icon(
                  icon,
                  color: unlocked ? Colors.white : Colors.grey[600],
                  size: 32,
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          // Badge name
          Text(
            name,
            style: TextStyle(
              fontSize: 13,
              fontWeight: unlocked ? FontWeight.w600 : FontWeight.w400,
              color: unlocked
                  ? (isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary)
                  : (isDark ? Colors.grey[600] : Colors.grey[500]),
              fontFamily: AppTheme.fontPrimary,
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 4),
          // Description
          Text(
            description,
            style: TextStyle(
              fontSize: 10,
              color: isDark ? Colors.grey[600] : Colors.grey[500],
              fontFamily: AppTheme.fontPrimary,
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }
}
