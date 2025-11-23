import 'package:shared_preferences/shared_preferences.dart';

class SettingsService {
  static const String _themeModeKey = 'theme_mode';
  static const String _fontSizeKey = 'font_size';
  static const String _notificationsKey = 'notifications';
  static const String _vibrationKey = 'vibration';
  static const String _soundKey = 'sound';

  // Theme Mode: 'light', 'dark', or 'system'
  static Future<String> getThemeMode() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_themeModeKey) ?? 'system';
    } catch (e) {
      return 'system';
    }
  }

  static Future<void> setThemeMode(String mode) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_themeModeKey, mode);
    } catch (e) {
      // Ignore error
    }
  }

  // Font Size: 1-5 scale
  static Future<int> getFontSize() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getInt(_fontSizeKey) ?? 3;
    } catch (e) {
      return 3;
    }
  }

  static Future<void> setFontSize(int size) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setInt(_fontSizeKey, size);
    } catch (e) {
      // Ignore error
    }
  }

  // Notifications
  static Future<bool> getNotificationsEnabled() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getBool(_notificationsKey) ?? true;
    } catch (e) {
      return true;
    }
  }

  static Future<void> setNotificationsEnabled(bool enabled) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_notificationsKey, enabled);
    } catch (e) {
      // Ignore error
    }
  }

  // Vibration
  static Future<bool> getVibrationEnabled() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getBool(_vibrationKey) ?? true;
    } catch (e) {
      return true;
    }
  }

  static Future<void> setVibrationEnabled(bool enabled) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_vibrationKey, enabled);
    } catch (e) {
      // Ignore error
    }
  }

  // Sound
  static Future<bool> getSoundEnabled() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getBool(_soundKey) ?? true;
    } catch (e) {
      return true;
    }
  }

  static Future<void> setSoundEnabled(bool enabled) async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setBool(_soundKey, enabled);
    } catch (e) {
      // Ignore error
    }
  }
}

