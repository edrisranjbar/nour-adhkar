import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'dart:async';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/settings_service.dart';
import '../utils/number_formatter.dart';

class CounterScreen extends StatefulWidget {
  const CounterScreen({super.key});

  @override
  State<CounterScreen> createState() => _CounterScreenState();
}

class _CounterScreenState extends State<CounterScreen>
    with SingleTickerProviderStateMixin {
  List<Map<String, dynamic>> _adhkars = [];
  Map<String, dynamic>? _currentDhikr;
  int _currentIndex = 0;
  int _currentCount = 0;
  bool _isLoading = true;
  bool _hasCompleted = false;
  bool _isAuthenticated = false;
  AnimationController? _bumpController;
  bool _vibrationEnabled = true;
  int? _goalCount; // null means unlimited, 33, 100 are the options
  Timer? _timer;
  int _elapsedSeconds = 0;
  bool _timerStarted = false;
  bool _showTimer = true;
  String _customDhikr = '';
  bool _isCustomMode = false;
  final TextEditingController _customDhikrController = TextEditingController();
  Map<String, dynamic>? _userData;

  @override
  void initState() {
    super.initState();
    _bumpController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 200),
    );
    _loadSettings();
    _loadData();
    _checkAuth();
  }

  @override
  void dispose() {
    _bumpController?.dispose();
    _timer?.cancel();
    _customDhikrController.dispose();
    super.dispose();
  }

  void _startTimer() {
    if (!_timerStarted) {
      _timerStarted = true;
      _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
        if (mounted) {
          setState(() {
            _elapsedSeconds++;
          });
        }
      });
    }
  }

  void _stopTimer() {
    _timer?.cancel();
    _timerStarted = false;
  }

  void _resetTimer() {
    _timer?.cancel();
    setState(() {
      _elapsedSeconds = 0;
      _timerStarted = false;
    });
  }

  String _formatTime(int seconds) {
    final hours = seconds ~/ 3600;
    final minutes = (seconds % 3600) ~/ 60;
    final secs = seconds % 60;

    String padLeft(int num) {
      return num < 10
          ? '۰${NumberFormatter.formatNumber(num)}'
          : NumberFormatter.formatNumber(num);
    }

    if (hours > 0) {
      return '${NumberFormatter.formatNumber(hours)}:${padLeft(minutes)}:${padLeft(secs)}';
    } else {
      return '${padLeft(minutes)}:${padLeft(secs)}';
    }
  }

  Future<void> _loadSettings() async {
    _vibrationEnabled = await SettingsService.getVibrationEnabled();
  }

  Future<void> _checkAuth() async {
    final isAuth = await AuthService.isAuthenticated();
    if (mounted) {
      setState(() {
        _isAuthenticated = isAuth;
      });
    }
    if (isAuth) {
      await _loadUserData();
    }
  }

  Future<void> _loadUserData() async {
    try {
      final userData = await AuthService.getUser();
      if (mounted && userData != null) {
        setState(() {
          _userData = userData;
        });
      }
    } catch (e) {
      print('Error loading user data: $e');
    }
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      // Load daily collection adhkars
      final collection = await ApiService.getCollectionBySlug('daily');

      if (collection != null && collection['adhkar'] != null) {
        final adhkarList = List<Map<String, dynamic>>.from(
          collection['adhkar'],
        );
        final adhkars = adhkarList
            .map((item) {
              return {
                'id': item['id'],
                'title': item['title'] ?? '',
                'arabic_text': item['arabic_text'] ?? '',
                'translation': item['translation'] ?? '',
                'transliteration': item['transliteration'] ?? '',
                'count': item['count'] ?? 33,
                'prefix': item['prefix'] ?? '',
                'suffix': item['suffix'] ?? '',
              };
            })
            .where((dhikr) {
              // Filter out empty dhikrs
              final arabicText = (dhikr['arabic_text'] ?? '').toString().trim();
              final translation = (dhikr['translation'] ?? '')
                  .toString()
                  .trim();
              return arabicText.isNotEmpty || translation.isNotEmpty;
            })
            .toList();

        if (mounted) {
          setState(() {
            _adhkars = adhkars;
            if (adhkars.isNotEmpty) {
              _currentDhikr = adhkars[0];
              _currentIndex = 0;
            }
            _isLoading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() => _isLoading = false);
        }
      }
    } catch (e) {
      print('Error loading counter data: $e');
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _refreshData() async {
    try {
      // Refresh collections data
      await _loadData();

      // Refresh user data if authenticated
      if (_isAuthenticated) {
        await AuthService.refreshUserData();
        // Reload local user data for UI update
        await _loadUserData();
        // Re-check auth state to update UI if needed
        await _checkAuth();
      }
    } catch (e) {
      print('Error refreshing data: $e');
    }
  }

  void _selectDhikr(int index) {
    if (index >= 0 && index < _adhkars.length) {
      setState(() {
        _currentIndex = index;
        _currentDhikr = _adhkars[index];
        _currentCount = 0;
        _hasCompleted = false;
        _isCustomMode = false;
      });
      _resetTimer();
    }
  }

  void _showCustomDhikrDialog() {
    showDialog(
      context: context,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;
        return Directionality(
          textDirection: TextDirection.rtl,
          child: AlertDialog(
            backgroundColor: isDark
                ? AppTheme.darkBgSecondary
                : AppTheme.bgPrimary,
            title: Text(
              'ذکر سفارشی',
              style: TextStyle(
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
            content: TextField(
              controller: _customDhikrController,
              autofocus: true,
              maxLines: 3,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 20,
                fontFamily: AppTheme.fontArabic,
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
              ),
              decoration: InputDecoration(
                hintText: 'ذکر خود را وارد کنید',
                hintStyle: TextStyle(
                  fontSize: 16,
                  color: isDark
                      ? AppTheme.darkTextSecondary
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                ),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide(
                    color: isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary,
                    width: 2,
                  ),
                ),
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text(
                  'لغو',
                  style: TextStyle(
                    color: isDark
                        ? AppTheme.darkTextSecondary
                        : AppTheme.textSecondary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
              ),
              ElevatedButton(
                onPressed: () {
                  if (_customDhikrController.text.trim().isNotEmpty) {
                    setState(() {
                      _customDhikr = _customDhikrController.text.trim();
                      _isCustomMode = true;
                      _currentDhikr = null;
                      _currentCount = 0;
                      _hasCompleted = false;
                    });
                    _resetTimer();
                    Navigator.pop(context);
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDark
                      ? AppTheme.darkBrandPrimary
                      : AppTheme.brandPrimary,
                  foregroundColor: Colors.white,
                ),
                child: const Text(
                  'تایید',
                  style: TextStyle(fontFamily: AppTheme.fontPrimary),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  void _incrementCount() {
    if (_currentDhikr == null && !_isCustomMode) return;

    // Start timer on first count
    if (_currentCount == 0) {
      _startTimer();
    }

    final targetCount = _goalCount ?? (_currentDhikr!['count'] ?? 33);

    // Check if we can increment
    if (_goalCount != null && _currentCount >= targetCount) {
      return;
    }

    setState(() {
      _currentCount++;
    });

    // Trigger bump animation
    _bumpController?.forward(from: 0.0);

    // Haptic feedback
    if (_vibrationEnabled) {
      HapticFeedback.lightImpact();
    }

    // Check if completed (only for limited goals)
    if (_goalCount != null && _currentCount >= targetCount) {
      _handleCompletion();
    }
  }

  void _handleCompletion() {
    setState(() {
      _hasCompleted = true;
    });

    // Stop timer on completion
    _stopTimer();

    // Haptic feedback for completion
    if (_vibrationEnabled) {
      HapticFeedback.mediumImpact();
    }

    // Save completion to backend if authenticated
    if (_isAuthenticated) {
      _saveCompletion();
      // Show additional heart score message after a delay
      Future.delayed(const Duration(seconds: 1), () {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: const Text('❤️ امتیاز قلب شما افزایش یافت!'),
              backgroundColor: Colors.pink.shade500,
              duration: const Duration(seconds: 2),
            ),
          );
        }
      });
    }

    // Show completion message
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: const Text('تبریک! شما ذکر را به پایان رساندید'),
        backgroundColor: AppTheme.success,
        duration: const Duration(seconds: 2),
      ),
    );
  }

  Future<void> _saveCompletion() async {
    try {
      print('[CounterScreen] Saving dhikr completion...');
      // Get the dhikr count for heart score calculation
      final dhikrCount = _currentDhikr?['count'] ?? 33;
      print('[CounterScreen] Dhikr count: $dhikrCount');

      final result = await ApiService.completeDhikrWithDetails(dhikrCount);
      print('[CounterScreen] Dhikr completion result: $result');

      if (result['success'] == true) {
        // User data is already updated in ApiService, just reload local user data for UI update
        await _loadUserData();

        // Force rebuild of the header to show updated heart score
        if (mounted) {
          setState(() {});
        }

        // Show appropriate feedback messages
        final collectionCompleted = result['collection_completed'] == true;
        final heartScoreIncrease = result['heart_score_increase'] ?? 0;

        print('[CounterScreen] Collection completed: $collectionCompleted, Heart score increase: $heartScoreIncrease');

        if (heartScoreIncrease > 0) {
          // Show heart score increase message
          Future.delayed(const Duration(seconds: 1), () {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text(
                    '❤️ امتیاز قلب شما $heartScoreIncrease امتیاز افزایش یافت!',
                  ),
                  backgroundColor: Colors.pink.shade500,
                  duration: const Duration(seconds: 2),
                ),
              );
            }
          });
        }

        if (collectionCompleted) {
          print('[CounterScreen] Showing collection completion message');
          // Show collection completion message after a delay
          Future.delayed(const Duration(seconds: 2), () {
            if (mounted) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: const Text(
                    '🎉 مجموعه روزانه تکمیل شد! ۱۰ امتیاز قلب دریافت کردید',
                  ),
                  backgroundColor: Colors.orange.shade600,
                  duration: const Duration(seconds: 3),
                ),
              );
            }
          });
        }
      }
    } catch (e) {
      print('[CounterScreen] Error saving completion: $e');
    }
  }

  void _resetCounter() {
    setState(() {
      _currentCount = 0;
      _hasCompleted = false;
    });
    _resetTimer();
  }

  void _goToNextDhikr() {
    if (_currentIndex < _adhkars.length - 1) {
      _selectDhikr(_currentIndex + 1);
      if (_vibrationEnabled) {
        HapticFeedback.selectionClick();
      }
    }
  }

  // Widget Builders
  Widget _buildEmptyState(bool isDark) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.inbox_outlined,
            size: 64,
            color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
          ),
          const SizedBox(height: 16),
          Text(
            'هیچ ذکری یافت نشد',
            style: TextStyle(
              fontSize: 18,
              color: isDark
                  ? AppTheme.darkTextSecondary
                  : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDhikrChips(bool isDark) {
    return Container(
      height: 48,
      margin: const EdgeInsets.symmetric(vertical: 8),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: _adhkars.length + 1,
        itemBuilder: (context, index) {
          if (index == _adhkars.length) {
            return _buildCustomDhikrChip(isDark);
          }
          return _buildDhikrChip(isDark, index);
        },
      ),
    );
  }

  Widget _buildCustomDhikrChip(bool isDark) {
    return GestureDetector(
      onTap: _showCustomDhikrDialog,
      child: Container(
        margin: const EdgeInsets.only(left: 6),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: _isCustomMode
              ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
              : (isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: _isCustomMode
                ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                : Colors.transparent,
            width: 1.5,
          ),
        ),
        child: Center(
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.add,
                size: 14,
                color: _isCustomMode
                    ? Colors.white
                    : (isDark
                          ? AppTheme.darkTextPrimary
                          : AppTheme.textPrimary),
              ),
              const SizedBox(width: 4),
              Text(
                'ذکر سفارشی',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: _isCustomMode ? FontWeight.w600 : FontWeight.w400,
                  color: _isCustomMode
                      ? Colors.white
                      : (isDark
                            ? AppTheme.darkTextPrimary
                            : AppTheme.textPrimary),
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDhikrChip(bool isDark, int index) {
    final dhikr = _adhkars[index];
    final isSelected = !_isCustomMode && index == _currentIndex;

    return GestureDetector(
      onTap: () => _selectDhikr(index),
      child: Container(
        margin: const EdgeInsets.only(left: 6),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected
              ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
              : (isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: isSelected
                ? (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                : Colors.transparent,
            width: 1.5,
          ),
        ),
        child: Center(
          child: Text(
            dhikr['title'] ?? NumberFormatter.formatNumber(index + 1),
            style: TextStyle(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w600 : FontWeight.w400,
              color: isSelected
                  ? Colors.white
                  : (isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary),
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTimerDisplay(bool isDark) {
    if (!_timerStarted || !_showTimer) return const SizedBox.shrink();

    final brandColor = isDark
        ? AppTheme.darkBrandPrimary
        : AppTheme.brandPrimary;

    return Positioned(
      top: 24,
      right: 24,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: (isDark ? AppTheme.darkBgSecondary : AppTheme.bgSecondary)
              .withOpacity(0.95),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: brandColor.withOpacity(0.3), width: 1),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.15),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(FontAwesomeIcons.stopwatch, color: brandColor, size: 14),
            const SizedBox(width: 8),
            Text(
              _formatTime(_elapsedSeconds),
              style: TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: brandColor,
                fontFamily: AppTheme.fontPrimary,
                letterSpacing: 1,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDhikrCard(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(28),
      decoration: BoxDecoration(
        color: isDark ? AppTheme.darkBgSecondary : AppTheme.bgSecondary,
        borderRadius: BorderRadius.circular(8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 20,
            offset: const Offset(0, 8),
            spreadRadius: 0,
          ),
          BoxShadow(
            color: (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                .withOpacity(0.1),
            blurRadius: 30,
            offset: const Offset(0, 4),
            spreadRadius: -5,
          ),
        ],
        border: Border.all(
          color: (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
              .withOpacity(0.1),
          width: 1,
        ),
      ),
      child: Column(
        children: [
          if (_isCustomMode) ...[
            Directionality(
              textDirection: TextDirection.rtl,
              child: Text(
                _customDhikr,
                style: TextStyle(
                  fontSize: 30,
                  height: 2.2,
                  color: isDark
                      ? AppTheme.darkTextPrimary
                      : AppTheme.textPrimary,
                  fontFamily: AppTheme.fontArabic,
                  fontWeight: FontWeight.w500,
                ),
                textAlign: TextAlign.center,
                overflow: TextOverflow.visible,
                softWrap: true,
              ),
            ),
            const SizedBox(height: 20),
            Container(
              decoration: BoxDecoration(
                color:
                    (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary)
                        .withOpacity(0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: TextButton.icon(
                onPressed: _showCustomDhikrDialog,
                icon: Icon(
                  Icons.edit,
                  size: 18,
                  color: isDark
                      ? AppTheme.darkBrandPrimary
                      : AppTheme.brandPrimary,
                ),
                label: Text(
                  'ویرایش',
                  style: TextStyle(
                    color: isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary,
                    fontFamily: AppTheme.fontPrimary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ),
          ] else ...[
            if (_currentDhikr?['prefix'] != null &&
                (_currentDhikr!['prefix'] as String).trim().isNotEmpty) ...[
              Text(
                _currentDhikr!['prefix'],
                style: TextStyle(
                  fontSize: 15,
                  color: isDark
                      ? AppTheme.darkTextSecondary
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                  fontStyle: FontStyle.italic,
                ),
                textAlign: TextAlign.center,
                overflow: TextOverflow.visible,
                softWrap: true,
              ),
              const SizedBox(height: 16),
            ],
            if (_currentDhikr?['arabic_text'] != null &&
                (_currentDhikr!['arabic_text'] as String).trim().isNotEmpty)
              Directionality(
                textDirection: TextDirection.rtl,
                child: Text(
                  _currentDhikr!['arabic_text'],
                  style: TextStyle(
                    fontSize: 30,
                    height: 2.2,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontArabic,
                    fontWeight: FontWeight.w500,
                  ),
                  textAlign: TextAlign.center,
                  overflow: TextOverflow.visible,
                  softWrap: true,
                ),
              ),
            if (_currentDhikr?['suffix'] != null &&
                (_currentDhikr!['suffix'] as String).trim().isNotEmpty) ...[
              const SizedBox(height: 16),
              Text(
                _currentDhikr!['suffix'],
                style: TextStyle(
                  fontSize: 15,
                  color: isDark
                      ? AppTheme.darkTextSecondary
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                  fontStyle: FontStyle.italic,
                ),
                textAlign: TextAlign.center,
                overflow: TextOverflow.visible,
                softWrap: true,
              ),
            ],
          ],
        ],
      ),
    );
  }

  void _showSettingsModal() {
    showDialog(
      context: context,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;
        return Directionality(
          textDirection: TextDirection.rtl,
          child: AlertDialog(
            backgroundColor: isDark
                ? AppTheme.darkBgSecondary
                : AppTheme.bgSecondary,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(20),
            ),
            title: Text(
              'تنظیمات',
              style: TextStyle(
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'هدف شمارش',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _buildModalGoalChip(isDark, 33, '۳۳'),
                    _buildModalGoalChip(isDark, 100, '۱۰۰'),
                    _buildModalGoalChip(isDark, null, 'نامحدود'),
                  ],
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'نمایش تایمر',
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w600,
                        color: isDark
                            ? AppTheme.darkTextPrimary
                            : AppTheme.textPrimary,
                        fontFamily: AppTheme.fontPrimary,
                      ),
                    ),
                    Switch(
                      value: _showTimer,
                      onChanged: (value) {
                        setState(() {
                          _showTimer = value;
                          if (!value && _timerStarted) {
                            _stopTimer();
                          }
                        });
                      },
                      activeColor: isDark
                          ? AppTheme.darkBrandPrimary
                          : AppTheme.brandPrimary,
                    ),
                  ],
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text(
                  'بستن',
                  style: TextStyle(
                    color: isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary,
                    fontFamily: AppTheme.fontPrimary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildModalGoalChip(bool isDark, int? value, String label) {
    final isSelected = _goalCount == value;
    final brandColor = isDark
        ? AppTheme.darkBrandPrimary
        : AppTheme.brandPrimary;

    return GestureDetector(
      onTap: () {
        setState(() {
          _goalCount = isSelected ? null : value;
          if (_goalCount != value) {
            _currentCount = 0;
            _hasCompleted = false;
          }
        });
        _resetTimer();
      },
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected
              ? brandColor
              : (isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected
                ? brandColor
                : (isDark ? AppTheme.darkBgTertiary : AppTheme.bgTertiary),
            width: 2,
          ),
          boxShadow: isSelected
              ? [
                  BoxShadow(
                    color: brandColor.withOpacity(0.3),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ]
              : null,
        ),
        child: Text(
          label,
          style: TextStyle(
            fontSize: 14,
            fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
            color: isSelected
                ? Colors.white
                : (isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary),
            fontFamily: AppTheme.fontPrimary,
          ),
        ),
      ),
    );
  }

  Widget _buildCounterButton(bool isDark) {
    final buttonColor = _hasCompleted
        ? AppTheme.success
        : (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary);

    // Calculate progress
    final targetCount = _goalCount ?? (_currentDhikr?['count'] ?? 33);
    final progress = targetCount > 0
        ? (_currentCount / targetCount).clamp(0.0, 1.0)
        : 0.0;

    return AnimatedBuilder(
      animation: _bumpController!,
      builder: (context, child) {
        final scale = 1.0 + (_bumpController!.value * 0.12);
        return Transform.scale(scale: scale, child: child);
      },
      child: GestureDetector(
        onTap: _hasCompleted ? _goToNextDhikr : _incrementCount,
        child: Container(
          width: 220,
          height: 220,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: _hasCompleted
                  ? [AppTheme.success, AppTheme.success.withOpacity(0.8)]
                  : [buttonColor, buttonColor.withOpacity(0.85)],
            ),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: buttonColor.withOpacity(0.4),
                blurRadius: 30,
                spreadRadius: 8,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: buttonColor.withOpacity(0.2),
                blurRadius: 15,
                spreadRadius: 3,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Stack(
            alignment: Alignment.center,
            children: [
              // Circular Progress Indicator
              if (_goalCount != null)
                SizedBox(
                  width: 220,
                  height: 220,
                  child: CircularProgressIndicator(
                    value: progress,
                    strokeWidth: 6,
                    backgroundColor: Colors.white.withOpacity(0.2),
                    valueColor: AlwaysStoppedAnimation<Color>(
                      _hasCompleted ? AppTheme.success : Colors.white,
                    ),
                    strokeCap: StrokeCap.round,
                  ),
                ),
              // Center Content
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  AnimatedSwitcher(
                    duration: const Duration(milliseconds: 300),
                    child: Icon(
                      _hasCompleted
                          ? Icons.check_circle
                          : FontAwesomeIcons.hand,
                      key: ValueKey(_hasCompleted),
                      color: Colors.white,
                      size: 40,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    NumberFormatter.formatNumber(_currentCount),
                    style: const TextStyle(
                      fontSize: 48,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                      fontFamily: AppTheme.fontPrimary,
                      letterSpacing: -2,
                    ),
                  ),
                  if (_goalCount != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      '/ ${NumberFormatter.formatNumber(_goalCount!)}',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w600,
                        color: Colors.white.withOpacity(0.8),
                        fontFamily: AppTheme.fontPrimary,
                      ),
                    ),
                  ],
                  if (_hasCompleted) ...[
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.25),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: const Text(
                        'تکمیل شد',
                        style: TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                          fontFamily: AppTheme.fontPrimary,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActionButtons(bool isDark) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.1),
                blurRadius: 10,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: ElevatedButton.icon(
            onPressed: _resetCounter,
            icon: const Icon(Icons.refresh, size: 20),
            label: const Text(
              'بازنشانی',
              style: TextStyle(
                fontFamily: AppTheme.fontPrimary,
                fontWeight: FontWeight.w600,
              ),
            ),
            style: ElevatedButton.styleFrom(
              backgroundColor: isDark
                  ? AppTheme.darkBgSecondary
                  : AppTheme.bgSecondary,
              foregroundColor: isDark
                  ? AppTheme.darkTextPrimary
                  : AppTheme.textPrimary,
              padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
                side: BorderSide(
                  color:
                      (isDark
                              ? AppTheme.darkBrandPrimary
                              : AppTheme.brandPrimary)
                          .withOpacity(0.2),
                  width: 1,
                ),
              ),
              elevation: 0,
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      color: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
      child: SafeArea(
        child: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _adhkars.isEmpty
            ? _buildEmptyState(isDark)
            : Column(
                children: [
                  AppHeader(
                    title: 'ذکرشمار',
                    description: _isAuthenticated && _userData != null
                        ? '❤️ امتیاز قلب: ${_userData!['heart_score'] ?? 0}'
                        : 'شمارش اذکار روزانه',
                    heartScore: _isAuthenticated && _userData != null ? _userData!['heart_score'] : null,
                    streak: _isAuthenticated && _userData != null ? _userData!['streak'] : null,
                    isAuthenticated: _isAuthenticated,
                    onMenuTap: () => Scaffold.of(context).openDrawer(),
                  ),
                  if (_adhkars.length > 1 || _isCustomMode)
                    _buildDhikrChips(isDark),
                  Expanded(
                    child: Stack(
                      children: [
                        RefreshIndicator(
                          onRefresh: _refreshData,
                          child: SingleChildScrollView(
                            physics: const AlwaysScrollableScrollPhysics(),
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              children: [
                                _buildDhikrCard(isDark),
                                const SizedBox(height: 32),
                                _buildCounterButton(isDark),
                                const SizedBox(height: 40),
                                _buildActionButtons(isDark),
                                const SizedBox(height: 24),
                              ],
                            ),
                          ),
                        ),
                        if (_timerStarted && _showTimer)
                          _buildTimerDisplay(isDark),
                        // Floating Action Button for Settings
                        Positioned(
                          bottom: 24,
                          left: 24,
                          child: FloatingActionButton(
                            onPressed: _showSettingsModal,
                            backgroundColor: isDark
                                ? AppTheme.darkBrandPrimary
                                : AppTheme.brandPrimary,
                            child: const Icon(
                              Icons.settings,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
      ),
    );
  }
}
