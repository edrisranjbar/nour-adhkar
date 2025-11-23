import 'dart:convert';
import 'package:http/http.dart' as http;
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
      print('Error fetching collections: $e');
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
      print('Error fetching collection: $e');
      return null;
    }
  }

  static Future<List<Map<String, dynamic>>> getAdhkarByCollection(String slug) async {
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
      print('Error fetching adhkar: $e');
      return [];
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
          'heart_score': data['heart_score'] ?? 0,
          'total_dhikrs': data['total_dhikrs'] ?? 0,
          'today_count': data['today_count'] ?? 0,
          'favorite_count': data['favorite_count'] ?? 0,
          'completed_dates': data['completed_dates'] ?? [],
        };
      }
      return null;
    } catch (e) {
      print('Error fetching user stats: $e');
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
      print('Error fetching dashboard: $e');
      return null;
    }
  }

  static Future<List<Map<String, dynamic>>> getUserBadges() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return [];

      final response = await http.get(
        Uri.parse('${AppConfig.baseApiUrl}/user/badges'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true && data['data'] != null) {
          return List<Map<String, dynamic>>.from(data['data']);
        }
      }
      return [];
    } catch (e) {
      print('Error fetching user badges: $e');
      return [];
    }
  }

  static Future<bool> completeDhikr() async {
    try {
      final token = await AuthService.getToken();
      if (token == null) return false;

      final response = await http.post(
        Uri.parse('${AppConfig.baseApiUrl}/dhikr'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200 || response.statusCode == 201) {
        final data = json.decode(response.body);
        return data['success'] == true;
      }
      return false;
    } catch (e) {
      print('Error completing dhikr: $e');
      return false;
    }
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
      print('Error fetching favorites: $e');
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
      print('Error toggling favorite: $e');
      return false;
    }
  }
}

