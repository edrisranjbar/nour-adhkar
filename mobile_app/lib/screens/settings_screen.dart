import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';
import '../widgets/app_header.dart';
import '../services/settings_service.dart';
import '../services/auth_service.dart';
import '../config.dart';
import 'login_screen.dart';
import '../utils/number_formatter.dart';

class SettingsScreen extends StatefulWidget {
  final Function(ThemeMode)? onThemeModeChanged;
  
  const SettingsScreen({super.key, this.onThemeModeChanged});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  String _themeMode = 'system';
  int _fontSize = 3;
  bool _notifications = true;
  bool _vibration = true;
  bool _sound = true;
  bool _isLoading = true;
  bool _isAuthenticated = false;

  @override
  void initState() {
    super.initState();
    _loadSettings();
    _checkAuth();
  }

  Future<void> _loadSettings() async {
    setState(() => _isLoading = true);
    try {
      final themeMode = await SettingsService.getThemeMode();
      final fontSize = await SettingsService.getFontSize();
      final notifications = await SettingsService.getNotificationsEnabled();
      final vibration = await SettingsService.getVibrationEnabled();
      final sound = await SettingsService.getSoundEnabled();

      if (mounted) {
        setState(() {
          _themeMode = themeMode;
          _fontSize = fontSize;
          _notifications = notifications;
          _vibration = vibration;
          _sound = sound;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _checkAuth() async {
    final isAuth = await AuthService.isAuthenticated();
    if (mounted) {
      setState(() {
        _isAuthenticated = isAuth;
      });
    }
  }

  Future<void> _handleThemeModeChange(String? value) async {
    if (value == null) return;
    
    await SettingsService.setThemeMode(value);
    setState(() {
      _themeMode = value;
    });

    // Update theme in main app
    if (widget.onThemeModeChanged != null) {
      ThemeMode mode;
      switch (value) {
        case 'light':
          mode = ThemeMode.light;
          break;
        case 'dark':
          mode = ThemeMode.dark;
          break;
        default:
          mode = ThemeMode.system;
      }
      widget.onThemeModeChanged!(mode);
    }
  }

  Future<void> _handleFontSizeChange(int size) async {
    await SettingsService.setFontSize(size);
    setState(() {
      _fontSize = size;
    });
  }

  Future<void> _handleNotificationsChange(bool value) async {
    await SettingsService.setNotificationsEnabled(value);
    setState(() {
      _notifications = value;
    });
  }

  Future<void> _handleVibrationChange(bool value) async {
    await SettingsService.setVibrationEnabled(value);
    setState(() {
      _vibration = value;
    });
  }

  Future<void> _handleSoundChange(bool value) async {
    await SettingsService.setSoundEnabled(value);
    setState(() {
      _sound = value;
    });
  }

  void _showAboutDialog() {
    showDialog(
      context: context,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;
        return Directionality(
          textDirection: TextDirection.rtl,
          child: AlertDialog(
            backgroundColor: isDark ? AppTheme.darkBgSecondary : AppTheme.bgPrimary,
            title: Row(
              children: [
                Icon(
                  FontAwesomeIcons.mosque,
                  color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
                  size: 24,
                ),
                const SizedBox(width: 12),
                Text(
                  'اذکار نور',
                  style: TextStyle(
                    color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                    fontSize: 20,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'نسخه ${AppConfig.appVersion}',
                    style: TextStyle(
                      color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'اذکار نور یک برنامه جامع برای خواندن و شمارش اذکار و ادعیه اسلامی است. این برنامه با هدف تسهیل در یادآوری و خواندن اذکار روزانه، صبحگاهی، شامگاهی و سایر اذکار مهم طراحی شده است.',
                    style: TextStyle(
                      color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                      fontSize: 14,
                      height: 1.8,
                    ),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'ویژگی‌های برنامه:',
                    style: TextStyle(
                      color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
                      fontFamily: AppTheme.fontPrimary,
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(height: 8),
                  _buildFeatureItem('• اذکار صبح و شام', isDark),
                  _buildFeatureItem('• اذکار روزانه و ماه رمضان', isDark),
                  _buildFeatureItem('• شمارشگر ذکر با قابلیت تنظیم هدف', isDark),
                  _buildFeatureItem('• داشبورد شخصی و آمار پیشرفت', isDark),
                  _buildFeatureItem('• لیست علاقه‌مندی‌ها', isDark),
                  _buildFeatureItem('• حالت تاریک و روشن', isDark),
                  const SizedBox(height: 16),
                  Text(
                    '© ${DateTime.now().year} اذکار نور',
                    style: TextStyle(
                      color: isDark ? AppTheme.darkTextSecondary : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                      fontSize: 12,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text(
                  'بستن',
                  style: TextStyle(
                    color: isDark ? AppTheme.darkBrandPrimary : AppTheme.brandPrimary,
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

  Widget _buildFeatureItem(String text, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Text(
        text,
        style: TextStyle(
          color: isDark ? AppTheme.darkTextPrimary : AppTheme.textPrimary,
          fontFamily: AppTheme.fontPrimary,
          fontSize: 14,
          height: 1.6,
        ),
      ),
    );
  }

  Future<void> _handleLogout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('خروج از حساب کاربری'),
        content: const Text('آیا مطمئن هستید که می‌خواهید خارج شوید؟'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('انصراف'),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('خروج'),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await AuthService.logout();
      if (mounted) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(
            builder: (context) => const LoginScreen(),
          ),
          (route) => false,
        );
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
            : SingleChildScrollView(
                child: Column(
                  children: [
                    // Header
                    AppHeader(
                      title: 'تنظیمات',
                      description: 'مدیریت تنظیمات برنامه',
                      isAuthenticated: _isAuthenticated,
                      onMenuTap: () {
                        Scaffold.of(context).openDrawer();
                      },
                    ),

                    // Display Settings
                    _buildSettingsCard(
                      isDark,
                      title: 'نمایش',
                      children: [
                        _buildThemeModeSetting(isDark),
                        _buildFontSizeSetting(isDark),
                      ],
                    ),

                    // Notification Settings
                    _buildSettingsCard(
                      isDark,
                      title: 'اعلان‌ها',
                      children: [
                        _buildSwitchSetting(
                          isDark,
                          title: 'اعلان‌ها',
                          description: 'دریافت اعلان برای یادآوری اذکار',
                          value: _notifications,
                          onChanged: _handleNotificationsChange,
                          icon: FontAwesomeIcons.bell,
                        ),
                        _buildSwitchSetting(
                          isDark,
                          title: 'لرزش',
                          description: 'فعال‌سازی لرزش هنگام اعلان',
                          value: _vibration,
                          onChanged: _handleVibrationChange,
                          icon: FontAwesomeIcons.mobileScreenButton,
                        ),
                        _buildSwitchSetting(
                          isDark,
                          title: 'صدا',
                          description: 'پخش صدا هنگام اعلان',
                          value: _sound,
                          onChanged: _handleSoundChange,
                          icon: FontAwesomeIcons.volumeHigh,
                        ),
                      ],
                    ),

                    // Account Settings
                    if (_isAuthenticated)
                      _buildSettingsCard(
                        isDark,
                        title: 'حساب کاربری',
                        children: [
                          _buildActionSetting(
                            isDark,
                            title: 'خروج از حساب کاربری',
                            description: 'خروج از حساب کاربری فعلی',
                            icon: FontAwesomeIcons.rightFromBracket,
                            onTap: _handleLogout,
                            isDestructive: true,
                          ),
                        ],
                      ),

                    // App Info
                    _buildSettingsCard(
                      isDark,
                      title: 'درباره',
                      children: [
                        _buildInfoSetting(
                          isDark,
                          title: 'نسخه برنامه',
                          value: AppConfig.appVersion,
                          icon: FontAwesomeIcons.info,
                        ),
                        const Divider(height: 24),
                        _buildActionSetting(
                          isDark,
                          title: 'درباره اذکار نور',
                          description: 'اطلاعات بیشتر درباره برنامه',
                          icon: FontAwesomeIcons.circleInfo,
                          onTap: () => _showAboutDialog(),
                        ),
                      ],
                    ),

                    const SizedBox(height: 32),
                  ],
                ),
              ),
      ),
    );
  }

  Widget _buildSettingsCard(
    bool isDark, {
    required String title,
    required List<Widget> children,
  }) {
    return Container(
      margin: const EdgeInsets.all(16),
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
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
          const SizedBox(height: 16),
          ...children,
        ],
      ),
    );
  }

  Widget _buildThemeModeSetting(bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'حالت نمایش',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: isDark
                  ? AppTheme.darkTextPrimary
                  : AppTheme.textPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 8),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(
                value: 'light',
                label: Text('روشن'),
                icon: Icon(Icons.light_mode, size: 18),
              ),
              ButtonSegment(
                value: 'dark',
                label: Text('تاریک'),
                icon: Icon(Icons.dark_mode, size: 18),
              ),
              ButtonSegment(
                value: 'system',
                label: Text('سیستم'),
                icon: Icon(Icons.brightness_auto, size: 18),
              ),
            ],
            selected: {_themeMode},
            onSelectionChanged: (Set<String> newSelection) {
              _handleThemeModeChange(newSelection.first);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildFontSizeSetting(bool isDark) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'اندازه متن',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w500,
              color: isDark
                  ? AppTheme.darkTextPrimary
                  : AppTheme.textPrimary,
              fontFamily: AppTheme.fontPrimary,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              IconButton(
                onPressed: _fontSize > 1
                    ? () => _handleFontSizeChange(_fontSize - 1)
                    : null,
                icon: const Icon(Icons.remove_circle_outline),
                color: _fontSize > 1
                    ? (isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary)
                    : Colors.grey,
              ),
              Container(
                width: 60,
                alignment: Alignment.center,
                child: Text(
                  NumberFormatter.formatNumber(_fontSize),
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w600,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
              ),
              IconButton(
                onPressed: _fontSize < 5
                    ? () => _handleFontSizeChange(_fontSize + 1)
                    : null,
                icon: const Icon(Icons.add_circle_outline),
                color: _fontSize < 5
                    ? (isDark
                        ? AppTheme.darkBrandPrimary
                        : AppTheme.brandPrimary)
                    : Colors.grey,
              ),
              const Spacer(),
              Text(
                'کوچک',
                style: TextStyle(
                  fontSize: 12,
                  color: isDark
                      ? AppTheme.darkTextSecondary
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Slider(
                  value: _fontSize.toDouble(),
                  min: 1,
                  max: 5,
                  divisions: 4,
                  onChanged: (value) {
                    _handleFontSizeChange(value.toInt());
                  },
                  activeColor: isDark
                      ? AppTheme.darkBrandPrimary
                      : AppTheme.brandPrimary,
                ),
              ),
              const SizedBox(width: 8),
              Text(
                'بزرگ',
                style: TextStyle(
                  fontSize: 12,
                  color: isDark
                      ? AppTheme.darkTextSecondary
                      : AppTheme.textSecondary,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSwitchSetting(
    bool isDark, {
    required String title,
    required String description,
    required bool value,
    required Function(bool) onChanged,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          Icon(
            icon,
            size: 20,
            color: isDark
                ? AppTheme.darkBrandPrimary
                : AppTheme.brandPrimary,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w500,
                    color: isDark
                        ? AppTheme.darkTextPrimary
                        : AppTheme.textPrimary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  description,
                  style: TextStyle(
                    fontSize: 12,
                    color: isDark
                        ? AppTheme.darkTextSecondary
                        : AppTheme.textSecondary,
                    fontFamily: AppTheme.fontPrimary,
                  ),
                ),
              ],
            ),
          ),
          Switch(
            value: value,
            onChanged: onChanged,
            activeColor: isDark
                ? AppTheme.darkBrandPrimary
                : AppTheme.brandPrimary,
          ),
        ],
      ),
    );
  }

  Widget _buildActionSetting(
    bool isDark, {
    required String title,
    required String description,
    required IconData icon,
    required VoidCallback onTap,
    bool isDestructive = false,
  }) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 12),
        child: Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: isDestructive
                  ? Colors.red
                  : (isDark
                      ? AppTheme.darkBrandPrimary
                      : AppTheme.brandPrimary),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w500,
                      color: isDestructive
                          ? Colors.red
                          : (isDark
                              ? AppTheme.darkTextPrimary
                              : AppTheme.textPrimary),
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    description,
                    style: TextStyle(
                      fontSize: 12,
                      color: isDark
                          ? AppTheme.darkTextSecondary
                          : AppTheme.textSecondary,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ],
              ),
            ),
            Icon(
              Icons.chevron_left,
              color: isDark
                  ? AppTheme.darkTextSecondary
                  : AppTheme.textSecondary,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoSetting(
    bool isDark, {
    required String title,
    required String value,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      child: Row(
        children: [
          Icon(
            icon,
            size: 20,
            color: isDark
                ? AppTheme.darkBrandPrimary
                : AppTheme.brandPrimary,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w500,
                color: isDark
                    ? AppTheme.darkTextPrimary
                    : AppTheme.textPrimary,
                fontFamily: AppTheme.fontPrimary,
              ),
            ),
          ),
          Text(
            value,
            style: TextStyle(
              fontSize: 14,
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
}
