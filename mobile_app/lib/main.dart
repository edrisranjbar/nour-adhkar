import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'theme/app_theme.dart';
import 'screens/home_screen.dart';
import 'screens/counter_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/favorites_screen.dart';
import 'screens/settings_screen.dart';
import 'screens/login_screen.dart';
import 'screens/welcome_screen.dart';
import 'widgets/bottom_navigation.dart';
import 'widgets/splash_screen.dart';
import 'widgets/app_drawer.dart';
import 'services/auth_service.dart';
import 'services/settings_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  
  // Set system UI overlay style
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.light,
    ),
  );
  
  runApp(const MyApp());
}

class MyApp extends StatefulWidget {
  const MyApp({super.key});

  @override
  State<MyApp> createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  ThemeMode _themeMode = ThemeMode.system;

  @override
  void initState() {
    super.initState();
    _loadThemeMode();
  }

  Future<void> _loadThemeMode() async {
    final themeMode = await SettingsService.getThemeMode();
    setState(() {
      switch (themeMode) {
        case 'light':
          _themeMode = ThemeMode.light;
          break;
        case 'dark':
          _themeMode = ThemeMode.dark;
          break;
        default:
          _themeMode = ThemeMode.system;
      }
    });
  }

  void updateThemeMode(ThemeMode mode) {
    setState(() {
      _themeMode = mode;
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'اذکار نور',
      debugShowCheckedModeBanner: false,
      // Set locale to Persian (Farsi) for RTL support
      locale: const Locale('fa', 'IR'),
      // Support Persian locale
      supportedLocales: const [
        Locale('fa', 'IR'), // Persian (Farsi)
        Locale('en', 'US'), // English as fallback
      ],
      // Localization delegates
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      themeMode: _themeMode,
      home: SplashWrapper(
        onThemeModeChanged: updateThemeMode,
      ),
    );
  }
}

class SplashWrapper extends StatefulWidget {
  final Function(ThemeMode)? onThemeModeChanged;
  
  const SplashWrapper({super.key, this.onThemeModeChanged});

  @override
  State<SplashWrapper> createState() => _SplashWrapperState();
}

class _SplashWrapperState extends State<SplashWrapper> {
  bool _showSplash = false;
  bool _isChecking = true;
  bool _isAuthenticated = false;

  @override
  void initState() {
    super.initState();
    _checkSplashAndAuth();
  }

  Future<void> _checkSplashAndAuth() async {
    try {
      final isAuth = await AuthService.isAuthenticated();
      
      if (mounted) {
        setState(() {
          _showSplash = true; // Always show splash screen
          _isAuthenticated = isAuth;
          _isChecking = false;
        });
      }
    } catch (e) {
      // If error, show splash anyway
      if (mounted) {
        setState(() {
          _showSplash = true;
          _isAuthenticated = false;
          _isChecking = false;
        });
      }
    }
  }

  Future<void> _onSplashComplete() async {
    if (mounted) {
      setState(() {
        _showSplash = false;
      });
    }
  }

  void _onLoginSuccess() {
    if (mounted) {
      setState(() {
        _isAuthenticated = true;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_isChecking) {
      return const Scaffold(
        backgroundColor: AppTheme.bgPrimary,
        body: Center(
          child: CircularProgressIndicator(),
        ),
      );
    }

    if (_showSplash) {
      return SplashScreen(onComplete: _onSplashComplete);
    }

    // Check authentication - show welcome screen if not authenticated
    if (!_isAuthenticated) {
      return WelcomeScreen(onLoginSuccess: _onLoginSuccess);
    }

    return MainScreen(
      onThemeModeChanged: widget.onThemeModeChanged,
    );
  }
}

class MainScreen extends StatefulWidget {
  final Function(ThemeMode)? onThemeModeChanged;
  
  const MainScreen({super.key, this.onThemeModeChanged});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  int _currentIndex = 0;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  bool _isAuthenticated = false;
  int? _heartScore;
  int? _streak;
  String? _userName;
  String? _profilePhotoUrl;

  List<Widget> get _screens {
    if (_isAuthenticated) {
      return [
        const HomeScreen(),
        const CounterScreen(),
        const DashboardScreen(),
        const FavoritesScreen(),
        SettingsScreen(
          onThemeModeChanged: widget.onThemeModeChanged,
        ),
      ];
    } else {
      return [
        const HomeScreen(),
        const CounterScreen(),
        SettingsScreen(
          onThemeModeChanged: widget.onThemeModeChanged,
        ),
      ];
    }
  }

  @override
  void initState() {
    super.initState();
    _loadAuthState();
  }

  Future<void> _loadAuthState() async {
    final isAuth = await AuthService.isAuthenticated();
    if (isAuth) {
      final user = await AuthService.getUser();
      setState(() {
        _isAuthenticated = true;
        _userName = user?['name'] ?? user?['email'] ?? 'کاربر';
        _profilePhotoUrl = user?['avatar'] ?? user?['profile_photo'] ?? user?['photo'];
        // TODO: Load heart score and streak from API
        _heartScore = user?['heart_score'] ?? 0;
        _streak = user?['streak'] ?? 0;
      });
    }
  }

  void _onTabTapped(int index) {
    // Map bottom nav index to screen index
    int screenIndex;
    if (_isAuthenticated) {
      // Bottom nav: Home(0), Counter(1), Dashboard(2), Settings(3)
      // Screens: Home(0), Counter(1), Dashboard(2), Favorites(3), Settings(4)
      screenIndex = index == 3 ? 4 : index;
    } else {
      // Bottom nav: Home(0), Counter(1), Settings(2)
      // Screens: Home(0), Counter(1), Settings(2)
      screenIndex = index;
    }
    setState(() {
      _currentIndex = screenIndex;
    });
    // Close drawer if open
    _scaffoldKey.currentState?.closeDrawer();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      key: _scaffoldKey,
      drawer: AppDrawer(
        isAuthenticated: _isAuthenticated,
        userName: _userName,
        profilePhotoUrl: _profilePhotoUrl,
        heartScore: _heartScore,
        streak: _streak,
        currentIndex: _currentIndex,
        onItemTap: _onTabTapped,
        onLoginTap: () {
          // Should not happen since login is required
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) => const LoginScreen(),
            ),
          );
        },
      ),
      body: _screens[_currentIndex],
      bottomNavigationBar: BottomNavigation(
        currentIndex: _isAuthenticated
            ? (_currentIndex == 4 ? 3 : _currentIndex.clamp(0, 2))
            : _currentIndex.clamp(0, 2),
        onTap: _onTabTapped,
        isAuthenticated: _isAuthenticated,
      ),
    );
  }
}
