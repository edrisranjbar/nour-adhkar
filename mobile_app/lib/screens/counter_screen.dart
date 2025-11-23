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
  int? _goalCount; // null means unlimited, 30, 100 are the options
  Timer? _timer;
  int _elapsedSeconds = 0;
  bool _timerStarted = false;
  String _customDhikr = '';
  bool _isCustomMode = false;
  final TextEditingController _customDhikrController = TextEditingController();

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
      return num < 10 ? '۰${NumberFormatter.formatNumber(num)}' : NumberFormatter.formatNumber(num);
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
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      // Load daily collection adhkars
      final collection = await ApiService.getCollectionBySlug('daily');
      
      if (collection != null && collection['adhkar'] != null) {
        final adhkarList = List<Map<String, dynamic>>.from(collection['adhkar']);
        final adhkars = adhkarList.map((item) {
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
        }).toList();

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
            backgroundColor: isDark ? AppTheme.darkBgSecondary : AppTheme.bgPrimary,
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
                  style: TextStyle(
                    fontFamily: AppTheme.fontPrimary,
                  ),
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
    
    // For unlimited goal, just increment
    if (_goalCount == null) {
      setState(() {
        _currentCount++;
      });

      // Trigger bump animation
      _bumpController?.forward(from: 0.0);

      // Haptic feedback
      if (_vibrationEnabled) {
        HapticFeedback.lightImpact();
      }
    } else if (_currentCount < targetCount) {
      setState(() {
        _currentCount++;
      });

      // Trigger bump animation
      _bumpController?.forward(from: 0.0);

      // Haptic feedback
      if (_vibrationEnabled) {
        HapticFeedback.lightImpact();
      }

      // Check if completed
      if (_currentCount >= targetCount) {
        _handleCompletion();
      }
    }
  }
  
  void _setGoal(int? goal) {
    setState(() {
      _goalCount = goal;
      _currentCount = 0;
      _hasCompleted = false;
    });
    _resetTimer();
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
      await ApiService.completeDhikr();
    } catch (e) {
      print('Error saving completion: $e');
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

  void _goToPreviousDhikr() {
    if (_currentIndex > 0) {
      _selectDhikr(_currentIndex - 1);
      if (_vibrationEnabled) {
        HapticFeedback.selectionClick();
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
            : _adhkars.isEmpty
                ? Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          Icons.inbox_outlined,
                          size: 64,
                          color: isDark
                              ? AppTheme.darkTextSecondary
                              : AppTheme.textSecondary,
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
                  )
                : Column(
                    children: [
                      // Header
                      AppHeader(
                        title: 'ذکرشمار',
                        description: 'شمارش اذکار روزانه',
                        isAuthenticated: _isAuthenticated,
                        onMenuTap: () {
                          Scaffold.of(context).openDrawer();
                        },
                      ),

                      // Dhikr Selection Chips
                      if (_adhkars.length > 1 || _isCustomMode)
                        Container(
                          height: 60,
                          margin: const EdgeInsets.symmetric(vertical: 8),
                          child: ListView.builder(
                            scrollDirection: Axis.horizontal,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            itemCount: _adhkars.length + 1, // +1 for custom chip
                            itemBuilder: (context, index) {
                              // Custom dhikr chip
                              if (index == _adhkars.length) {
                                return GestureDetector(
                                  onTap: _showCustomDhikrDialog,
                                  child: Container(
                                    margin: const EdgeInsets.only(left: 8),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 16,
                                      vertical: 8,
                                    ),
                                    decoration: BoxDecoration(
                                      color: _isCustomMode
                                          ? (isDark
                                              ? AppTheme.darkBrandPrimary
                                              : AppTheme.brandPrimary)
                                          : (isDark
                                              ? AppTheme.darkBgTertiary
                                              : AppTheme.bgTertiary),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(
                                        color: _isCustomMode
                                            ? (isDark
                                                ? AppTheme.darkBrandPrimary
                                                : AppTheme.brandPrimary)
                                            : Colors.transparent,
                                        width: 2,
                                      ),
                                    ),
                                    child: Center(
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            Icons.add,
                                            size: 16,
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
                                              fontSize: 14,
                                              fontWeight: _isCustomMode
                                                  ? FontWeight.w600
                                                  : FontWeight.w400,
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
                              
                              // Regular dhikr chips
                              final dhikr = _adhkars[index];
                              final isSelected = !_isCustomMode && index == _currentIndex;
                              return GestureDetector(
                                onTap: () => _selectDhikr(index),
                                child: Container(
                                  margin: const EdgeInsets.only(left: 8),
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 16,
                                    vertical: 8,
                                  ),
                                  decoration: BoxDecoration(
                                    color: isSelected
                                        ? (isDark
                                            ? AppTheme.darkBrandPrimary
                                            : AppTheme.brandPrimary)
                                        : (isDark
                                            ? AppTheme.darkBgTertiary
                                            : AppTheme.bgTertiary),
                                    borderRadius: BorderRadius.circular(20),
                                    border: Border.all(
                                      color: isSelected
                                          ? (isDark
                                              ? AppTheme.darkBrandPrimary
                                              : AppTheme.brandPrimary)
                                          : Colors.transparent,
                                      width: 2,
                                    ),
                                  ),
                                  child: Center(
                                    child: Text(
                                      dhikr['title'] ?? NumberFormatter.formatNumber(index + 1),
                                      style: TextStyle(
                                        fontSize: 14,
                                        fontWeight: isSelected
                                            ? FontWeight.w600
                                            : FontWeight.w400,
                                        color: isSelected
                                            ? Colors.white
                                            : (isDark
                                                ? AppTheme.darkTextPrimary
                                                : AppTheme.textPrimary),
                                        fontFamily: AppTheme.fontPrimary,
                                      ),
                                    ),
                                  ),
                                ),
                              );
                            },
                          ),
                        ),

                      // Main Content
                      Expanded(
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            children: [
                              // Enhanced Timer Display
                              if (_timerStarted)
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 24,
                                    vertical: 20,
                                  ),
                                  margin: const EdgeInsets.only(bottom: 16),
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      colors: isDark
                                          ? [
                                              AppTheme.darkBrandPrimary.withOpacity(0.2),
                                              AppTheme.darkBrandPrimary.withOpacity(0.1),
                                            ]
                                          : [
                                              AppTheme.brandPrimary.withOpacity(0.1),
                                              AppTheme.brandPrimary.withOpacity(0.05),
                                            ],
                                      begin: Alignment.topRight,
                                      end: Alignment.bottomLeft,
                                    ),
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: isDark
                                          ? AppTheme.darkBrandPrimary.withOpacity(0.4)
                                          : AppTheme.brandPrimary.withOpacity(0.3),
                                      width: 2,
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: (isDark
                                                ? AppTheme.darkBrandPrimary
                                                : AppTheme.brandPrimary)
                                            .withOpacity(0.15),
                                        blurRadius: 12,
                                        offset: const Offset(0, 4),
                                      ),
                                    ],
                                  ),
                                  child: Column(
                                    children: [
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.center,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.all(10),
                                            decoration: BoxDecoration(
                                              color: isDark
                                                  ? AppTheme.darkBrandPrimary.withOpacity(0.3)
                                                  : AppTheme.brandPrimary.withOpacity(0.2),
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            child: Icon(
                                              FontAwesomeIcons.stopwatch,
                                              color: isDark
                                                  ? AppTheme.darkBrandPrimary
                                                  : AppTheme.brandPrimary,
                                              size: 24,
                                            ),
                                          ),
                                          const SizedBox(width: 16),
                                          Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'زمان سپری شده',
                                                style: TextStyle(
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.w400,
                                                  color: isDark
                                                      ? AppTheme.darkTextSecondary
                                                      : AppTheme.textSecondary,
                                                  fontFamily: AppTheme.fontPrimary,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                _formatTime(_elapsedSeconds),
                                                style: TextStyle(
                                                  fontSize: 28,
                                                  fontWeight: FontWeight.w700,
                                                  color: isDark
                                                      ? AppTheme.darkBrandPrimary
                                                      : AppTheme.brandPrimary,
                                                  fontFamily: AppTheme.fontPrimary,
                                                  letterSpacing: 2,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ],
                                  ),
                                ),
                              
                              // Dhikr Card
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? AppTheme.darkBgTertiary
                                      : AppTheme.bgSecondary,
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
                                  children: [
                                    if (_isCustomMode) ...[
                                      // Custom Dhikr
                                      Directionality(
                                        textDirection: TextDirection.rtl,
                                        child: Text(
                                          _customDhikr,
                                          style: TextStyle(
                                            fontSize: 28,
                                            height: 2.0,
                                            color: isDark
                                                ? AppTheme.darkTextPrimary
                                                : AppTheme.textPrimary,
                                            fontFamily: AppTheme.fontArabic,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),
                                      ),
                                      const SizedBox(height: 16),
                                      TextButton.icon(
                                        onPressed: _showCustomDhikrDialog,
                                        icon: const Icon(Icons.edit, size: 16),
                                        label: const Text('ویرایش'),
                                        style: TextButton.styleFrom(
                                          foregroundColor: isDark
                                              ? AppTheme.darkBrandPrimary
                                              : AppTheme.brandPrimary,
                                        ),
                                      ),
                                    ] else ...[
                                      // Prefix
                                      if (_currentDhikr?['prefix'] != null &&
                                          (_currentDhikr!['prefix'] as String)
                                              .isNotEmpty)
                                        Text(
                                          _currentDhikr!['prefix'],
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: isDark
                                                ? AppTheme.darkTextSecondary
                                                : AppTheme.textSecondary,
                                            fontFamily: AppTheme.fontPrimary,
                                            fontStyle: FontStyle.italic,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),

                                      if (_currentDhikr?['prefix'] != null &&
                                          (_currentDhikr!['prefix'] as String)
                                              .isNotEmpty)
                                        const SizedBox(height: 12),

                                      // Arabic Text
                                      if (_currentDhikr?['arabic_text'] != null)
                                        Directionality(
                                          textDirection: TextDirection.rtl,
        child: Text(
                                            _currentDhikr!['arabic_text'],
                                            style: TextStyle(
                                              fontSize: 28,
                                              height: 2.0,
                                              color: isDark
                                                  ? AppTheme.darkTextPrimary
                                                  : AppTheme.textPrimary,
                                              fontFamily: AppTheme.fontArabic,
                                            ),
                                            textAlign: TextAlign.center,
                                          ),
                                        ),

                                      const SizedBox(height: 16),

                                      // Suffix
                                      if (_currentDhikr?['suffix'] != null &&
                                          (_currentDhikr!['suffix'] as String)
                                              .isNotEmpty) ...[
                                        const SizedBox(height: 12),
                                        Text(
                                          _currentDhikr!['suffix'],
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: isDark
                                                ? AppTheme.darkTextSecondary
                                                : AppTheme.textSecondary,
                                            fontFamily: AppTheme.fontPrimary,
                                            fontStyle: FontStyle.italic,
                                          ),
                                          textAlign: TextAlign.center,
                                        ),
                                      ],
                                    ],
                                  ],
                                ),
                              ),

                              const SizedBox(height: 24),

                              // Goal Selection
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'هدف شمارش',
                                    style: TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                      color: isDark
                                          ? AppTheme.darkTextSecondary
                                          : AppTheme.textSecondary,
                                      fontFamily: AppTheme.fontPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  Wrap(
                                    spacing: 8,
                                    children: [
                                      ChoiceChip(
                                        label: const Text('۳۰'),
                                        selected: _goalCount == 30,
                                        onSelected: (selected) => _setGoal(selected ? 30 : null),
                                        selectedColor: isDark
                                            ? AppTheme.darkBrandPrimary
                                            : AppTheme.brandPrimary,
                                        labelStyle: TextStyle(
                                          color: _goalCount == 30
                                              ? Colors.white
                                              : (isDark
                                                  ? AppTheme.darkTextPrimary
                                                  : AppTheme.textPrimary),
                                          fontFamily: AppTheme.fontPrimary,
                                        ),
                                      ),
                                      ChoiceChip(
                                        label: const Text('۱۰۰'),
                                        selected: _goalCount == 100,
                                        onSelected: (selected) => _setGoal(selected ? 100 : null),
                                        selectedColor: isDark
                                            ? AppTheme.darkBrandPrimary
                                            : AppTheme.brandPrimary,
                                        labelStyle: TextStyle(
                                          color: _goalCount == 100
                                              ? Colors.white
                                              : (isDark
                                                  ? AppTheme.darkTextPrimary
                                                  : AppTheme.textPrimary),
                                          fontFamily: AppTheme.fontPrimary,
                                        ),
                                      ),
                                      ChoiceChip(
                                        label: const Text('نامحدود'),
                                        selected: _goalCount == null,
                                        onSelected: (selected) => _setGoal(null),
                                        selectedColor: isDark
                                            ? AppTheme.darkBrandPrimary
                                            : AppTheme.brandPrimary,
                                        labelStyle: TextStyle(
                                          color: _goalCount == null
                                              ? Colors.white
                                              : (isDark
                                                  ? AppTheme.darkTextPrimary
                                                  : AppTheme.textPrimary),
                                          fontFamily: AppTheme.fontPrimary,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),

                              const SizedBox(height: 24),

                              // Progress Bar
                              if (_goalCount != null)
                                Column(
                                  children: [
                                    Row(
                                      mainAxisAlignment:
                                          MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          'پیشرفت',
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w500,
                                            color: isDark
                                                ? AppTheme.darkTextSecondary
                                                : AppTheme.textSecondary,
                                            fontFamily: AppTheme.fontPrimary,
                                          ),
                                        ),
                                        Text(
                                          '${NumberFormatter.formatNumber(_currentCount)} از ${NumberFormatter.formatNumber(_goalCount ?? 0)}',
                                          style: TextStyle(
                                            fontSize: 14,
                                            fontWeight: FontWeight.w600,
                                            color: isDark
                                                ? AppTheme.darkBrandPrimary
                                                : AppTheme.brandPrimary,
                                            fontFamily: AppTheme.fontPrimary,
                                          ),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    LinearProgressIndicator(
                                      value: _goalCount != null
                                          ? (_currentCount / _goalCount!)
                                          : 0,
                                      backgroundColor: isDark
                                        ? AppTheme.darkBgTertiary
                                        : AppTheme.bgTertiary,
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      _hasCompleted
                                          ? AppTheme.success
                                          : (isDark
                                              ? AppTheme.darkBrandPrimary
                                              : AppTheme.brandPrimary),
                                      ),
                                      minHeight: 8,
                                    ),
                                    const SizedBox(height: 24),
                                  ],
                                ),

                              const SizedBox(height: 24),

                              // Counter Button
                              AnimatedBuilder(
                                animation: _bumpController!,
                                builder: (context, child) {
                                  final scale = 1.0 +
                                      (_bumpController!.value * 0.1);
                                  return Transform.scale(
                                    scale: scale,
                                    child: child,
                                  );
                                },
                                child: GestureDetector(
                                  onTap: _hasCompleted
                                      ? _goToNextDhikr
                                      : _incrementCount,
                                  child: Container(
                                    width: 200,
                                    height: 200,
                                    decoration: BoxDecoration(
                                      color: _hasCompleted
                                          ? AppTheme.success
                                          : (isDark
                                              ? AppTheme.darkBrandPrimary
                                              : AppTheme.brandPrimary),
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: (_hasCompleted
                                                  ? AppTheme.success
                                                  : (isDark
                                                      ? AppTheme.darkBrandPrimary
                                                      : AppTheme.brandPrimary))
                                              .withOpacity(0.3),
                                          blurRadius: 20,
                                          spreadRadius: 5,
                                        ),
                                      ],
                                    ),
                                    child: Center(
                                      child: Column(
                                        mainAxisAlignment:
                                            MainAxisAlignment.center,
                                        children: [
                                          Icon(
                                            _hasCompleted
                                                ? Icons.check_circle
                                                : FontAwesomeIcons.hand,
                                            color: Colors.white,
                                            size: 48,
                                          ),
                                          const SizedBox(height: 12),
                                          Text(
                                            NumberFormatter.formatNumber(_currentCount),
                                            style: const TextStyle(
                                              fontSize: 48,
                                              fontWeight: FontWeight.w700,
                                              color: Colors.white,
                                              fontFamily: AppTheme.fontPrimary,
                                            ),
                                          ),
                                          if (_hasCompleted) ...[
                                            const SizedBox(height: 4),
                                            const Text(
                                              'تکمیل شد',
                                              style: TextStyle(
                                                fontSize: 14,
                                                color: Colors.white,
                                                fontFamily: AppTheme.fontPrimary,
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                  ),
                                ),
                              ),

                              const SizedBox(height: 32),

                              // Action Buttons
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  // Reset Button
                                  ElevatedButton.icon(
                                    onPressed: _resetCounter,
                                    icon: const Icon(Icons.refresh),
                                    label: const Text('بازنشانی'),
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: isDark
                                          ? AppTheme.darkBgTertiary
                                          : AppTheme.bgTertiary,
                                      foregroundColor: isDark
                                          ? AppTheme.darkTextPrimary
                                          : AppTheme.textPrimary,
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 24,
                                        vertical: 12,
                                      ),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 32),
                            ],
                          ),
                        ),
                      ),
                    ],
        ),
      ),
    );
  }
}
