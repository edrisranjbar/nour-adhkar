import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import 'dhikr_view_screen.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  List<Map<String, dynamic>> _favorites = [];
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
    
    final isAuth = await AuthService.isAuthenticated();
    setState(() => _isAuthenticated = isAuth);

    if (isAuth) {
      final user = await AuthService.getUser();
      setState(() {
        _heartScore = user?['heart_score'] ?? 0;
        _streak = user?['streak'] ?? 0;
      });
      await _loadFavorites();
    }
    
    setState(() => _isLoading = false);
  }

  Future<void> _loadFavorites() async {
    try {
      final favorites = await ApiService.getFavorites();
      if (mounted) {
        setState(() {
          _favorites = favorites;
        });
      }
    } catch (e) {
      print('Error loading favorites: $e');
    }
  }

  Future<void> _removeFavorite(int dhikrId) async {
    try {
      await ApiService.toggleFavorite(dhikrId);
      await _loadFavorites();
    } catch (e) {
      print('Error removing favorite: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      color: isDark ? AppTheme.darkBgPrimary : AppTheme.bgPrimary,
      child: SafeArea(
        child: Column(
          children: [
            // Header
            AppHeader(
              title: 'لیست علاقه‌مندی‌ها',
              description: 'اذکار مورد علاقه شما',
              isAuthenticated: _isAuthenticated,
              heartScore: _heartScore,
              streak: _streak,
              onMenuTap: () {
                Scaffold.of(context).openDrawer();
              },
            ),

            // Content
            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : !_isAuthenticated
                      ? _buildNotAuthenticatedState(isDark)
                      : _favorites.isEmpty
                          ? _buildEmptyState(isDark)
                          : _buildFavoritesList(isDark),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotAuthenticatedState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              FontAwesomeIcons.lock,
              size: 64,
              color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
            ),
            const SizedBox(height: 16),
            Text(
              'برای مشاهده لیست علاقه‌مندی‌ها',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w500,
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'لطفا وارد حساب کاربری خود شوید',
              style: TextStyle(
                fontSize: 14,
                color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                fontFamily: AppTheme.fontPrimary,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              FontAwesomeIcons.heart,
              size: 64,
              color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
            ),
            const SizedBox(height: 16),
            Text(
              'هنوز ذکری به علاقه‌مندی‌ها اضافه نکرده‌اید',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w500,
                color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              'با ضربه روی آیکون قلب در صفحه اذکار، می‌توانید آنها را به علاقه‌مندی‌ها اضافه کنید',
              style: TextStyle(
                fontSize: 14,
                color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                fontFamily: AppTheme.fontPrimary,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFavoritesList(bool isDark) {
    return RefreshIndicator(
      onRefresh: _loadFavorites,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _favorites.length,
        itemBuilder: (context, index) {
          final favorite = _favorites[index];
          return _buildFavoriteCard(favorite, isDark);
        },
      ),
    );
  }

  Widget _buildFavoriteCard(Map<String, dynamic> favorite, bool isDark) {
    final dhikr = favorite['dhikr'] ?? {};
    final arabicText = dhikr['arabic_text'] ?? '';
    final translation = dhikr['translation'] ?? '';
    final collectionName = favorite['collection']?['name'] ?? '';
    final dhikrId = dhikr['id'];

    return Card(
      margin: const EdgeInsets.only(bottom: 16),
      color: isDark ? AppTheme.darkBgTertiary : AppTheme.bgSecondary,
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
      ),
      child: InkWell(
        onTap: () {
          // Navigate to the dhikr in its collection
          if (favorite['collection']?['slug'] != null) {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (context) => DhikrViewScreen(
                  collectionSlug: favorite['collection']['slug'],
                  collectionName: collectionName,
                ),
              ),
            );
          }
        },
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Collection name and remove button
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  if (collectionName.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: isDark
                            ? AppTheme.darkBrandPrimary.withOpacity(0.2)
                            : AppTheme.brandPrimary.withOpacity(0.1),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        collectionName,
                        style: TextStyle(
                          fontSize: 12,
                          color: isDark
                              ? AppTheme.darkBrandPrimary
                              : AppTheme.brandPrimary,
                          fontFamily: AppTheme.fontPrimary,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  IconButton(
                    icon: const Icon(
                      FontAwesomeIcons.solidHeart,
                      color: Colors.red,
                      size: 20,
                    ),
                    onPressed: () {
                      if (dhikrId != null) {
                        _removeFavorite(dhikrId);
                      }
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                  ),
                ],
              ),
              
              const SizedBox(height: 16),
              
              // Arabic text
              if (arabicText.isNotEmpty)
                Directionality(
                  textDirection: TextDirection.rtl,
                  child: Text(
                    arabicText,
                    style: TextStyle(
                      fontSize: 20,
                      height: 2.0,
                      color: isDark
                          ? AppTheme.darkTextPrimary
                          : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontArabic,
                    ),
                    textAlign: TextAlign.center,
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              
              if (arabicText.isNotEmpty && translation.isNotEmpty)
                const SizedBox(height: 12),
              
              // Translation
              if (translation.isNotEmpty)
                Text(
                  translation,
                  style: TextStyle(
                    fontSize: 14,
                    color: isDark
                        ? AppTheme.darkTextSecondary
                        : AppTheme.textSecondary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
            ],
          ),
        ),
      ),
    );
  }
}

