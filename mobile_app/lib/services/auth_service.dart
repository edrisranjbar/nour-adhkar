import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config.dart';

class AuthService {
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'user_data';
  static const String _isAuthenticatedKey = 'is_authenticated';

  // Check if user is authenticated
  static Future<bool> isAuthenticated() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString(_tokenKey);
      final isAuth = prefs.getBool(_isAuthenticatedKey) ?? false;
      return token != null && token.isNotEmpty && isAuth;
    } catch (e) {
      return false;
    }
  }

  // Get stored token
  static Future<String?> getToken() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      return prefs.getString(_tokenKey);
    } catch (e) {
      return null;
    }
  }

  // Get user data
  static Future<Map<String, dynamic>?> getUser() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final userJson = prefs.getString(_userKey);
      if (userJson != null) {
        return json.decode(userJson) as Map<String, dynamic>;
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  // Login
  static Future<Map<String, dynamic>> login(String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('${AppConfig.baseApiUrl}/auth/login'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: json.encode({
          'email': email,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        
        if (data['token'] != null) {
          final prefs = await SharedPreferences.getInstance();
          
          // Store token
          await prefs.setString(_tokenKey, data['token']);
          
          // Store user data
          if (data['user'] != null) {
            await prefs.setString(_userKey, json.encode(data['user']));
          }
          
          // Set authenticated flag
          await prefs.setBool(_isAuthenticatedKey, true);
          
          return {
            'success': true,
            'token': data['token'],
            'user': data['user'],
          };
        } else {
          return {
            'success': false,
            'message': data['message'] ?? 'خطای نامشخص در ورود',
          };
        }
      } else if (response.statusCode == 401) {
        return {
          'success': false,
          'message': 'ایمیل یا رمز عبور اشتباه است',
        };
      } else {
        final data = json.decode(response.body);
        return {
          'success': false,
          'message': data['message'] ?? 'خطا در ورود به سیستم',
        };
      }
    } catch (e) {
      if (e.toString().contains('TimeoutException') || e.toString().contains('timeout')) {
        return {
          'success': false,
          'message': 'زمان درخواست به پایان رسید. لطفاً دوباره تلاش کنید.',
        };
      } else if (e.toString().contains('SocketException') || e.toString().contains('network')) {
        return {
          'success': false,
          'message': 'اتصال اینترنت خود را بررسی کنید',
        };
      } else {
        return {
          'success': false,
          'message': 'خطا در ورود به سیستم',
        };
      }
    }
  }

  // Logout
  static Future<void> logout() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_tokenKey);
      await prefs.remove(_userKey);
      await prefs.setBool(_isAuthenticatedKey, false);
    } catch (e) {
      // Ignore error
    }
  }
}

