import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show Clipboard, ClipboardData, HapticFeedback;
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'dart:async';
import '../theme/app_theme.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/settings_service.dart';
import '../utils/number_formatter.dart';

class DhikrViewScreen extends StatefulWidget {
  final String collectionSlug;
  final String? collectionName;

  const DhikrViewScreen({
    super.key,
    required this.collectionSlug,
    this.collectionName,
  });

  @override
  State<DhikrViewScreen> createState() => _DhikrViewScreenState();
}

class _DhikrViewScreenState extends State<DhikrViewScreen>
    with TickerProviderStateMixin {
  List<Map<String, dynamic>> _adhkar = [];
  Map<String, int> _counters = {}; // Counter for each dhikr by arabic_text
  int _currentIndex = 0;
  bool _isLoading = true;
  bool _isAuthenticated = false;
  Set<int> _favorites = {};
  String? _collectionName;
  String? _error;
  bool _bumping = false;
  AnimationController? _bumpController;
  AnimationController? _slideController;
  bool _vibrationEnabled = true;
  String _toastMessage = '';
  bool _showToast = false;
  Timer? _toastTimer;
  bool _showCongratulations = false;

  @override
  void initState() {
    super.initState();
    _bumpController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 160),
    );
    _slideController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 250),
    );
    _loadSettings();
    _loadData();
    _checkAuth();
  }

  @override
  void dispose() {
    _bumpController?.dispose();
    _slideController?.dispose();
    _toastTimer?.cancel();
    super.dispose();
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
      if (isAuth) {
        _loadFavorites();
      }
    }
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final collection = await ApiService.getCollectionBySlug(widget.collectionSlug);
      
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
            'source': item['source'] ?? '',
            'reference': item['reference'] ?? '',
          };
        }).toList();

        if (mounted) {
          setState(() {
            _adhkar = adhkars;
            _collectionName = widget.collectionName ?? 
                             collection['name'] ?? 
                             'اذکار';
            _isLoading = false;
          });
          _initializeCounters();
        }
      } else {
        if (mounted) {
          setState(() {
            _error = 'مجموعه مورد نظر یافت نشد';
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      print('Error loading dhikr data: $e');
      if (mounted) {
        setState(() {
          _error = 'خطایی رخ داده است';
          _isLoading = false;
        });
      }
    }
  }

  void _initializeCounters() {
    final newCounters = <String, int>{};
    for (var dhikr in _adhkar) {
      final arabicText = dhikr['arabic_text'] ?? '';
      newCounters[arabicText] = _counters[arabicText] ?? 0;
    }
    setState(() {
      _counters = newCounters;
    });
  }

  Map<String, dynamic>? get _currentDhikr {
    if (_currentIndex >= 0 && _currentIndex < _adhkar.length) {
      return _adhkar[_currentIndex];
    }
    return null;
  }

  int get _currentCounter {
    if (_currentDhikr == null) return 0;
    final arabicText = _currentDhikr!['arabic_text'] ?? '';
    return _counters[arabicText] ?? 0;
  }

  bool get _isFavorite {
    if (_currentDhikr == null) return false;
    return _favorites.contains(_currentDhikr!['id']);
  }

  double get _totalProgress {
    if (_adhkar.isEmpty) return 0;
    return ((_currentIndex + 1) / _adhkar.length * 100).clamp(5.0, 100.0);
  }

  Future<void> _loadFavorites() async {
    if (!_isAuthenticated) return;
    // TODO: Implement favorites API call
  }

  void _incrementCounter() {
    if (_currentDhikr == null) return;
    
    final arabicText = _currentDhikr!['arabic_text'] ?? '';
    final targetCount = _currentDhikr!['count'] ?? 33;
    final currentCount = _counters[arabicText] ?? 0;
    
    if (currentCount >= targetCount) {
      // If completed, go to next dhikr if available
      if (_currentIndex < _adhkar.length - 1) {
        _goToNextDhikr();
      }
      return;
    }
    
    setState(() {
      _counters[arabicText] = currentCount + 1;
    });
    
    _triggerCounterBump();
    
    if (_vibrationEnabled) {
      HapticFeedback.lightImpact();
    }
    
    // Auto-advance to next dhikr when completed
    if (currentCount + 1 >= targetCount) {
      if (_currentIndex < _adhkar.length - 1) {
        Future.delayed(const Duration(milliseconds: 300), () {
          _goToNextDhikr();
        });
      } else {
        // Last dhikr completed - show congratulations
        Future.delayed(const Duration(milliseconds: 500), () {
          if (mounted) {
            setState(() {
              _showCongratulations = true;
            });
            if (_vibrationEnabled) {
              HapticFeedback.heavyImpact();
            }
          }
        });
      }
    }
  }

  void _triggerCounterBump() {
    setState(() {
      _bumping = true;
    });
    _bumpController?.forward(from: 0.0).then((_) {
      Future.delayed(const Duration(milliseconds: 180), () {
        if (mounted) {
          setState(() {
            _bumping = false;
          });
        }
      });
    });
  }

  void _goToNextDhikr() {
    if (_currentIndex < _adhkar.length - 1) {
      setState(() {
        _currentIndex++;
      });
      _slideController?.forward(from: 0.0);
      if (_vibrationEnabled) {
        HapticFeedback.selectionClick();
      }
    }
  }

  void _goToPreviousDhikr() {
    if (_currentIndex > 0) {
      setState(() {
        _currentIndex--;
      });
      _slideController?.forward(from: 0.0);
      if (_vibrationEnabled) {
        HapticFeedback.selectionClick();
      }
    }
  }

  Future<void> _toggleFavorite() async {
    if (!_isAuthenticated) {
      _showToastMessage('لطفا ابتدا وارد شوید');
      return;
    }
    
    if (_currentDhikr == null) return;
    final dhikrId = _currentDhikr!['id'];
    
    // Optimistically update UI
    final wasFavorite = _favorites.contains(dhikrId);
    setState(() {
      if (wasFavorite) {
        _favorites.remove(dhikrId);
      } else {
        _favorites.add(dhikrId);
      }
    });
    
    // Call API
    try {
      final success = await ApiService.toggleFavorite(dhikrId);
      if (success) {
        _showToastMessage(
          wasFavorite 
            ? 'ذکر از علاقه‌مندی‌ها حذف شد' 
            : 'ذکر به علاقه‌مندی‌ها اضافه شد'
        );
      } else {
        // Revert on failure
        setState(() {
          if (wasFavorite) {
            _favorites.add(dhikrId);
          } else {
            _favorites.remove(dhikrId);
          }
        });
        _showToastMessage('خطا در ثبت تغییرات');
      }
    } catch (e) {
      // Revert on error
      setState(() {
        if (wasFavorite) {
          _favorites.add(dhikrId);
        } else {
          _favorites.remove(dhikrId);
        }
      });
      _showToastMessage('خطا در ثبت تغییرات');
    }
  }

  Future<void> _copyDhikr() async {
    if (_currentDhikr == null) return;
    
    final dhikr = _currentDhikr!;
    final text = '${dhikr['title'] ?? ''}\n\n'
        '${dhikr['prefix'] ?? ''}\n'
        '${dhikr['arabic_text'] ?? ''}\n'
        '${dhikr['translation'] ?? ''}';
    
    try {
      await Clipboard.setData(ClipboardData(text: text));
      _showToastMessage('متن ذکر کپی شد');
    } catch (e) {
      _showToastMessage('خطا در کپی کردن متن');
    }
  }

  void _showToastMessage(String message) {
    setState(() {
      _toastMessage = message;
      _showToast = true;
    });
    _toastTimer?.cancel();
    _toastTimer = Timer(const Duration(seconds: 3), () {
      if (mounted) {
        setState(() {
          _showToast = false;
        });
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return GestureDetector(
      onHorizontalDragEnd: (details) {
        if (details.primaryVelocity != null) {
          if (details.primaryVelocity! > 0) {
            _goToPreviousDhikr();
          } else if (details.primaryVelocity! < 0) {
            _goToNextDhikr();
          }
        }
      },
      child: Container(
        color: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
        child: SafeArea(
          child: Stack(
          children: [
            Column(
              children: [
                // Header
                _buildHeader(isDark),
                
                // Progress Bar (if multiple dhikrs)
                if (_adhkar.length > 1) _buildProgressBar(isDark),
                
                // Main Content
                Expanded(
                  child: _isLoading
                      ? const Center(child: CircularProgressIndicator())
                      : _error != null
                          ? _buildErrorState(isDark)
                          : _currentDhikr == null
                              ? _buildEmptyState(isDark)
                              : _buildDhikrContent(isDark),
                ),
                
                // Footer Counter
                if (!_isLoading && _error == null && _currentDhikr != null)
                  _buildCounterFooter(isDark),
              ],
            ),
            
            // Toast Notification
            if (_showToast)
              Positioned(
                top: 20,
                left: 0,
                right: 0,
                child: Center(
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    decoration: BoxDecoration(
                      color: isDark ? AppTheme.darkBrandSecondary : AppTheme.brandSecondary,
                      borderRadius: BorderRadius.circular(8),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.1),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      _toastMessage,
                      style: const TextStyle(
                        color: Colors.white,
                        fontFamily: AppTheme.fontPrimary,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
        ),
      ),
    );
  }

  Widget _buildHeader(bool isDark) {
    return Directionality(
      textDirection: TextDirection.rtl,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF262626) : AppTheme.brandSecondary,
          border: Border(
            bottom: BorderSide(
              color: Colors.white.withOpacity(0.1),
              width: 1,
            ),
          ),
        ),
        child: Row(
          children: [
            IconButton(
              icon: const Icon(Icons.arrow_back, color: Colors.white, size: 24),
              onPressed: () => Navigator.of(context).pop(),
              padding: EdgeInsets.zero,
              constraints: const BoxConstraints(),
            ),
            Expanded(
              child: Text(
                _collectionName ?? 'اذکار',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                  fontFamily: AppTheme.fontPrimary,
                ),
                textAlign: TextAlign.center,
              ),
            ),
            const SizedBox(width: 40), // Balance for back button
          ],
        ),
      ),
    );
  }

  Widget _buildProgressBar(bool isDark) {
    return Container(
      height: 4,
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(2),
        child: LinearProgressIndicator(
          value: _totalProgress / 100,
          backgroundColor: isDark
              ? AppTheme.darkBgTertiary
              : AppTheme.bgTertiary,
          valueColor: AlwaysStoppedAnimation<Color>(
            isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
          ),
          minHeight: 4,
        ),
      ),
    );
  }

  Widget _buildErrorState(bool isDark) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.error_outline,
            size: 64,
            color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
          ),
          const SizedBox(height: 16),
          Text(
            _error ?? 'خطایی رخ داده است',
            style: TextStyle(
              fontSize: 18,
              color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadData,
            child: const Text('تلاش مجدد'),
          ),
        ],
      ),
    );
  }

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
              color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDhikrContent(bool isDark) {
    if (_currentDhikr == null) return const SizedBox.shrink();
    
    final dhikr = _currentDhikr!;
    final title = dhikr['title'] ?? '';
    final prefix = dhikr['prefix'] ?? '';
    final arabicText = dhikr['arabic_text'] ?? '';
    final translation = dhikr['translation'] ?? '';

    return GestureDetector(
      onTap: _incrementCounter,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0.1, 0),
            end: Offset.zero,
          ).animate(CurvedAnimation(
            parent: _slideController!,
            curve: Curves.easeOut,
          )),
          child: FadeTransition(
            opacity: _slideController!,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Title and Action Buttons
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    if (title.isNotEmpty)
                      Expanded(
                        child: Text(
                          title,
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w600,
                            color: isDark
                                ? AppTheme.darkBrandPrimary
                                : AppTheme.brandPrimary,
                            fontFamily: AppTheme.fontPrimary,
                          ),
                        ),
                      )
                    else
                      const Spacer(),
                    Row(
                      children: [
                        IconButton(
                          icon: Icon(
                            _isFavorite ? FontAwesomeIcons.solidHeart : FontAwesomeIcons.heart,
                            color: _isFavorite ? Colors.red : (isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary),
                            size: 20,
                          ),
                          onPressed: _toggleFavorite,
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                        ),
                        const SizedBox(width: 16),
                        IconButton(
                          icon: Icon(
                            FontAwesomeIcons.copy,
                            color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                            size: 20,
                          ),
                          onPressed: _copyDhikr,
                          padding: EdgeInsets.zero,
                          constraints: const BoxConstraints(),
                        ),
                      ],
                    ),
                  ],
                ),
                
                const SizedBox(height: 16),
                
                // Prefix
                if (prefix.isNotEmpty)
                  Directionality(
                    textDirection: TextDirection.rtl,
                    child: Text(
                      prefix,
                      style: TextStyle(
                        fontSize: 16,
                        color: isDark
                            ? AppTheme.darkTextSecondary
                            : AppTheme.textSecondary,
                        fontFamily: AppTheme.fontArabic,
                        height: 2.3,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                
                if (prefix.isNotEmpty) const SizedBox(height: 12),
                
                // Arabic Text
                if (arabicText.isNotEmpty)
                  Directionality(
                    textDirection: TextDirection.rtl,
                    child: Text(
                      arabicText,
                      style: TextStyle(
                        fontSize: 24,
                        height: 2.3,
                        color: isDark
                            ? AppTheme.darkTextPrimary
                            : AppTheme.textPrimary,
                        fontFamily: AppTheme.fontArabic,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                
                const SizedBox(height: 24),
                
                // Separator
                Container(
                  height: 2,
                  margin: const EdgeInsets.symmetric(horizontal: 40),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Colors.transparent,
                        isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                        Colors.transparent,
                      ],
                    ),
                    borderRadius: BorderRadius.circular(1),
                  ),
                ),
                
                const SizedBox(height: 24),
                
                // Translation
                if (translation.isNotEmpty)
                  Text(
                    translation,
                    style: TextStyle(
                      fontSize: 18,
                      height: 2.3,
                      color: isDark
                          ? AppTheme.darkTextPrimary
                          : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                    textAlign: TextAlign.center,
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildCounterFooter(bool isDark) {
    if (_currentDhikr == null) return const SizedBox.shrink();
    
    final targetCount = _currentDhikr!['count'] ?? 33;
    final currentCount = _currentCounter;

    return Directionality(
      textDirection: TextDirection.rtl,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF262626) : AppTheme.brandSecondary,
          border: Border(
            top: BorderSide(
              color: Colors.white.withOpacity(0.1),
              width: 1,
            ),
          ),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Target count (right side in RTL)
            Text(
              '${NumberFormatter.formatNumber(targetCount)} مرتبه',
              style: const TextStyle(
                fontSize: 14,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
            
            // Counter Button (center)
            GestureDetector(
              onTap: _incrementCounter,
              child: AnimatedBuilder(
                animation: _bumpController!,
                builder: (context, child) {
                  final scale = _bumping ? 1.08 : 1.0;
                  return Transform.scale(
                    scale: scale,
                    child: child,
                  );
                },
                child: Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: isDark ? AppTheme.darkBrandSecondary : AppTheme.brandSecondary,
                      width: 2,
                    ),
                  ),
                  child: Center(
                    child: Text(
                      NumberFormatter.formatNumber(currentCount),
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w700,
                        color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                        fontFamily: AppTheme.fontPrimary,
                      ),
                    ),
                  ),
                ),
              ),
            ),
            
            // Progress details (left side in RTL)
            Text(
              '${NumberFormatter.formatNumber(currentCount)} از ${NumberFormatter.formatNumber(targetCount)} مرتبه',
              style: const TextStyle(
                fontSize: 14,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
