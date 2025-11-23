import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'theme/app_theme.dart';
import 'screens/home_screen.dart';
import 'screens/counter_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/settings_screen.dart';
import 'screens/login_screen.dart';
import 'widgets/bottom_navigation.dart';
import 'widgets/splash_screen.dart';
import 'widgets/app_drawer.dart';
import 'services/auth_service.dart';

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

class MyApp extends StatelessWidget {
  const MyApp({super.key});

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
      themeMode: ThemeMode.system,
      home: const SplashWrapper(),
    );
  }
}

class SplashWrapper extends StatefulWidget {
  const SplashWrapper({super.key});

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
      final prefs = await SharedPreferences.getInstance();
      final hasSplashBeenShown = prefs.getBool('splashShown') ?? false;
      final isAuth = await AuthService.isAuthenticated();
      
      if (mounted) {
        setState(() {
          _showSplash = !hasSplashBeenShown;
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
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool('splashShown', true);
    } catch (e) {
      // Ignore error
    }
    
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

    // Check authentication - show login if not authenticated
    if (!_isAuthenticated) {
      return LoginScreen(onLoginSuccess: _onLoginSuccess);
    }

    return const MainScreen();
  }
}

class MainScreen extends StatefulWidget {
  const MainScreen({super.key});

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

  final List<Widget> _screens = [
    const HomeScreen(),
    const CounterScreen(),
    const DashboardScreen(),
    const SettingsScreen(),
  ];

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
    setState(() {
      _currentIndex = index;
    });
    // Close drawer if open
    _scaffoldKey.currentState?.closeDrawer();
  }

  Future<void> _handleLogout() async {
    await AuthService.logout();
    if (mounted) {
      // Navigate back to login by rebuilding the app
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(
          builder: (context) => const LoginScreen(),
        ),
      );
    }
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
        onLogoutTap: _handleLogout,
      ),
      body: Stack(
        children: [
          // Main content with bottom padding for navigation
          Padding(
            padding: const EdgeInsets.only(bottom: 102), // Space for bottom nav
            child: _screens[_currentIndex],
          ),
          // Bottom Navigation positioned at the bottom
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: BottomNavigation(
              currentIndex: _currentIndex,
              onTap: _onTabTapped,
            ),
          ),
        ],
      ),
    );
  }
}
