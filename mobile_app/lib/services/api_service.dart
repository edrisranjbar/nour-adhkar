import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../config.dart';
import 'auth_service.dart';

class ApiService {
  static Future<List<Map<String, dynamic>>> getCollections() async {
    try {
      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/collections'),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['collections'] != null) {
          final collections = List<Map<String, dynamic>>.from(
            data['collections'],
          );
          return collections.map((collection) {
            // Count adhkar from the adhkar array if it exists
            int adhkarCount = 0;
            if (collection['adhkar'] != null) {
              final adhkarList = collection['adhkar'];
              if (adhkarList is List) {
                adhkarCount = adhkarList.length;
              }
            }
            // Also check for adhkar_count if it exists (for backwards compatibility)
            if (collection['adhkar_count'] != null) {
              final count = collection['adhkar_count'];
              if (count is int) {
                adhkarCount = count;
              } else if (count is String) {
                adhkarCount = int.tryParse(count) ?? adhkarCount;
              }
            }

            return {
              'name': collection['name'] ?? '',
              'path': collection['slug'] ?? '',
              'items': adhkarCount,
            };
          }).toList();
        }
      }
      return [];
    } catch (e) {
      debugPrint('Error fetching collections: $e');
      return [];
    }
  }

  static Future<Map<String, dynamic>?> getCollectionBySlug(String slug) async {
    try {
      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/collections/$slug'),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['collection'] != null) {
          return data['collection'] as Map<String, dynamic>;
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error fetching collection: $e');
      return null;
    }
  }

  static Future<List<Map<String, dynamic>>> getAdhkarByCollection(
    String slug,
  ) async {
    try {
      // The collection endpoint already includes adhkar, so we fetch the collection
      final collection = await getCollectionBySlug(slug);

      if (collection != null && collection['adhkar'] != null) {
        final adhkar = List<Map<String, dynamic>>.from(collection['adhkar']);
        return adhkar.map((item) {
          return {
            'id': item['id'],
            'title': item['title'] ?? '',
            'arabic_text': item['arabic_text'] ?? '',
            'translation': item['translation'] ?? '',
            'transliteration': item['transliteration'] ?? '',
            'count': item['count'] ?? 1,
            'prefix': item['prefix'] ?? '',
            'suffix': item['suffix'] ?? '',
            'source': item['source'] ?? '',
            'reference': item['reference'] ?? '',
          };
        }).toList();
      }
      return [];
    } catch (e) {
      debugPrint('Error fetching adhkar: $e');
      return [];
    }
  }

  static Future<Map<String, dynamic>?> getUserProfile() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/user/profile'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['profile'] != null) {
          return data['profile'] as Map<String, dynamic>;
        }
      }
      return null;
    } catch (e) {
      debugPrint('Error fetching user profile: $e');
      return null;
    }
  }

  static Future<Map<String, dynamic>?> getUserStats() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/user/stats'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'streak': data['streak'] ?? 0,
          'total_adhkar_completed': data['total_dhikrs'] ??
              data['total_adhkar_completed'] ??
              0,
          'today_count': data['today_count'] ?? 0,
          'favorite_count': data['favorite_count'] ?? 0,
          'completed_dates': data['completed_dates'] ?? [],
        };
      }
      return null;
    } catch (e) {
      debugPrint('Error fetching user stats: $e');
      return null;
    }
  }

  static Future<Map<String, dynamic>?> getDashboard() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return null;

      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/user/dashboard'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return {
          'stats': data['stats'] ?? {},
          'recent_activities': data['recent_activities'] ?? [],
        };
      }
      return null;
    } catch (e) {
      debugPrint('Error fetching dashboard: $e');
      return null;
    }
  }

  static Future<Map<String, dynamic>> completeDhikrWithDetails([
    int? dhikrCount,
  ]) async {
    try {
      final token = await AuthService.getToken();
      if (token == null) {
        return {'success': false, 'error': 'No authentication token'};
      }

      debugPrint('[ApiService] Completing dhikr...');

      final requestBody = dhikrCount != null
          ? json.encode({'count': dhikrCount})
          : null;

      final response = await http.post(
        Uri.parse('${AppConfig.baseApiUrl}/dhikr'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
        body: requestBody,
      );

      debugPrint(
        '[ApiService] Dhikr completion response: ${response.statusCode} - ${response.body}',
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          debugPrint('[ApiService] Dhikr completed successfully on backend');

          // Use the updated user data directly from the response instead of fetching again
          if (data['user'] != null) {
            final prefs = await SharedPreferences.getInstance();
            await prefs.setString('user', json.encode(data['user']));
            debugPrint('[ApiService] Updated user data stored locally');
          }

          final collectionCompleted = data['collection_completed'] == true;

          if (collectionCompleted) {
            debugPrint('[ApiService] Daily collection completed.');
          }

          return {
            'success': true,
            'collection_completed': collectionCompleted,
            'today_count': data['today_count'] ?? 0,
          };
        }
      }
      return {'success': false};
    } catch (e) {
      debugPrint('Error completing dhikr: $e');
      return {'success': false, 'error': e.toString()};
    }
  }

  // Backward compatibility method
  static Future<bool> completeDhikr() async {
    final result = await completeDhikrWithDetails();
    return result['success'] == true;
  }

  static Future<List<Map<String, dynamic>>> getFavorites() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return [];

      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/user/favorites'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['favorites'] != null) {
          return List<Map<String, dynamic>>.from(data['favorites']);
        }
      }
      return [];
    } catch (e) {
      debugPrint('Error fetching favorites: $e');
      return [];
    }
  }

  static Future<bool> toggleFavorite(int dhikrId) async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return false;

      final response = await http.post(
        Uri.parse('${AppConfig.baseApiUrl}/user/favorites/$dhikrId'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      return response.statusCode == 200 || response.statusCode == 201;
    } catch (e) {
      debugPrint('Error toggling favorite: $e');
      return false;
    }
  }
}
