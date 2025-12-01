import 'package:flutter/material.dart';
import 'package:flutter/services.dart' show Clipboard, ClipboardData, HapticFeedback;
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'dart:async';
import '../theme/app_theme.dart';
import '../services/api_service.dart';
import '../services/settings_service.dart';
import '../widgets/modern_toast.dart';
import '../widgets/dhikr_view/dhikr_card.dart';
import '../widgets/dhikr_view/counter_footer.dart';
import '../widgets/dhikr_view/congratulations_modal.dart';

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
  String? _collectionName;
  String? _error;
  bool _bumping = false;
  AnimationController? _bumpController;
  AnimationController? _slideController;
  bool _vibrationEnabled = true;
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

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final collection = await ApiService.getCollectionBySlug(widget.collectionSlug);
      
      if (collection != null && collection['adhkar'] != null) {
        final adhkarList = List<Map<String, dynamic>>.from(collection['adhkar']);
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
                'source': item['source'] ?? '',
                'reference': item['reference'] ?? '',
              };
            })
            .where((dhikr) {
              // Filter out empty dhikrs - must have at least arabic_text or translation
              final arabicText = (dhikr['arabic_text'] ?? '').toString().trim();
              final translation = (dhikr['translation'] ?? '').toString().trim();
              return arabicText.isNotEmpty || translation.isNotEmpty;
            })
            .toList();

        if (mounted) {
          setState(() {
            _adhkar = adhkars;
            _collectionName = widget.collectionName ?? 
                             collection['name'] ?? 
                             'اذکار';
            _isLoading = false;
            // Reset index if current index is out of bounds after filtering
            if (_currentIndex >= adhkars.length && adhkars.isNotEmpty) {
              _currentIndex = 0;
            }
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

  Future<void> _copyDhikr() async {
    if (_currentDhikr == null) return;
    
    final dhikr = _currentDhikr!;
    final text = '${dhikr['title'] ?? ''}\n\n'
        '${dhikr['prefix'] ?? ''}\n'
        '${dhikr['arabic_text'] ?? ''}\n'
        '${dhikr['translation'] ?? ''}';
    
    try {
      await Clipboard.setData(ClipboardData(text: text));
      ModernToast.show(
        context,
        message: 'متن ذکر کپی شد',
        icon: FontAwesomeIcons.check,
        backgroundColor: Colors.green,
        iconColor: Colors.white,
        duration: const Duration(seconds: 2),
      );
      HapticFeedback.lightImpact();
    } catch (e) {
      ModernToast.show(
        context,
        message: 'خطا در کپی کردن متن',
        icon: FontAwesomeIcons.circleExclamation,
        backgroundColor: Colors.red,
        iconColor: Colors.white,
        duration: const Duration(seconds: 2),
      );
    }
  }


  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return GestureDetector(
      onHorizontalDragEnd: (details) {
        if (details.primaryVelocity != null) {
          // In RTL: swipe right (positive) = next, swipe left (negative) = previous
          if (details.primaryVelocity! > 0) {
            _goToNextDhikr();
          } else if (details.primaryVelocity! < 0) {
            _goToPreviousDhikr();
          }
        }
      },
      child: Container(
        color: isDark ? AppTheme.darkBgPrimary : AppTheme.bgSecondary,
        child: SafeArea(
          child: Stack(
          children: [
            Column(
              children: [
                // Header
                _buildHeader(isDark),
                
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
            
            // Congratulations Modal
            if (_showCongratulations)
              _buildCongratulationsModal(isDark),
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

    return GestureDetector(
      onTap: _incrementCounter,
      child: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
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
              mainAxisSize: MainAxisSize.max,
              children: [
                DhikrCard(
                  dhikr: _currentDhikr!,
                  isDark: isDark,
                  currentCount: _currentCounter,
                  onCopyTap: _copyDhikr,
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

    return CounterFooter(
      dhikr: _currentDhikr,
      currentIndex: _currentIndex,
      totalCount: _adhkar.length,
      currentCount: _currentCounter,
      isDark: isDark,
      bumpController: _bumpController!,
      bumping: _bumping,
      onIncrement: _incrementCounter,
    );
  }

  Widget _buildCongratulationsModal(bool isDark) {
    return CongratulationsModal(
      isDark: isDark,
      onClose: () {
        setState(() {
          _showCongratulations = false;
        });
        Navigator.of(context).pop();
      },
    );
  }
}
