import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config.dart';

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
          return collections.map((collection) => {
            'name': collection['name'] ?? '',
            'path': collection['slug'] ?? '',
            'items': collection['adhkar_count'] ?? 0,
          }).toList();
        }
      }
      return [];
    } catch (e) {
      print('Error fetching collections: $e');
      return [];
    }
  }
}

