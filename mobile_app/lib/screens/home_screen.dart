import 'package:flutter/material.dart';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../widgets/search_bar.dart';
import '../widgets/daily_verse.dart';
import '../widgets/special_section.dart';
import '../widgets/collections_grid.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'dhikr_view_screen.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Map<String, dynamic>> _collections = [];
  bool _isLoading = true;
  bool _isAuthenticated = false;
  int? _heartScore;
  int? _streak;

  @override
  void initState() {
    super.initState();
    _loadCollections();
    _loadAuthState();
  }

  Future<void> _loadAuthState() async {
    final isAuth = await AuthService.isAuthenticated();
    if (isAuth) {
      final user = await AuthService.getUser();
      setState(() {
        _isAuthenticated = true;
        _heartScore = user?['heart_score'] ?? 0;
        _streak = user?['streak'] ?? 0;
      });
    }
  }

  Future<void> _loadCollections() async {
    setState(() => _isLoading = true);
    try {
      final collections = await ApiService.getCollections();
      setState(() {
        _collections = collections;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _refreshData() async {
    await Future.wait([
      _loadCollections(),
      _loadAuthState(),
    ]);
  }

  int _getCollectionCount(String slug) {
    try {
      final collection = _collections.firstWhere(
        (c) => (c['path'] ?? '').toString() == slug,
      );
      return collection['items'] ?? 0;
    } catch (e) {
      return 0;
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
                onRefresh: _refreshData,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Column(
                    children: [
                    // Header
                    AppHeader(
                      title: 'اذکار نور',
                      description: 'پلتفرم فارسی اذکار و ادعیه اسلامی',
                      isAuthenticated: _isAuthenticated,
                      heartScore: _heartScore,
                      streak: _streak,
                      onLoginTap: () {
                        // Navigate to login
                      },
                      onLogoutTap: () {
                        setState(() {
                          _isAuthenticated = false;
                        });
                      },
                      onMenuTap: () {
                        // Open drawer - will use Scaffold.of(context) from MainScreen
                        Scaffold.of(context).openDrawer();
                      },
                    ),

                    // Main Content
                    // Search Bar
                    AppSearchBar(
                      collections: _collections,
                      onSearch: (path) {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => DhikrViewScreen(
                              collectionSlug: path,
                            ),
                          ),
                        );
                      },
                    ),

                    // Daily Verse
                    const DailyVerse(),

                    // Special Section
                    SpecialSection(
                      morningCount: _getCollectionCount('morning'),
                      nightCount: _getCollectionCount('night'),
                      onMorningTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => const DhikrViewScreen(
                              collectionSlug: 'morning',
                              collectionName: 'اذکار صبحگاه',
                            ),
                          ),
                        );
                      },
                      onNightTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => const DhikrViewScreen(
                              collectionSlug: 'night',
                              collectionName: 'اذکار شامگاه',
                            ),
                          ),
                        );
                      },
                    ),

                    // Section Title
                    Container(
                      margin: const EdgeInsets.symmetric(horizontal: 16),
                      child: Row(
                        children: [
                          Container(
                            width: 4,
                            height: 24,
                            decoration: BoxDecoration(
                              color: AppTheme.brandSecondary,
                              borderRadius: BorderRadius.circular(2),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Text(
                            'دسته بندی ها',
                            style: TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.w500,
                              color: isDark
                                  ? AppTheme.darkBrandPrimary
                                  : AppTheme.textPrimary,
                              fontFamily: AppTheme.fontPrimary,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Collections Grid
                    CollectionsGrid(
                      collections: _collections,
                      onCollectionTap: (slug) {
                        final collection = _collections.firstWhere(
                          (c) => (c['path'] ?? '').toString() == slug,
                          orElse: () => <String, dynamic>{},
                        );
                        Navigator.of(context).push(
                          MaterialPageRoute(
                            builder: (context) => DhikrViewScreen(
                              collectionSlug: slug,
                              collectionName: collection['name'],
                            ),
                          ),
                        );
                      },
                      onCounterTap: () {
                        // Navigate to counter
                        print('Navigate to counter');
                      },
                    ),
                  ],
                ),
              ),
            ),
      ),
    );
  }
}
