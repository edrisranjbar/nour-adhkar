import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import '../theme/app_theme.dart';

class AppSearchBar extends StatefulWidget {
  final List<dynamic> collections;
  final String placeholder;
  final Function(String)? onSearch;

  const AppSearchBar({
    super.key,
    required this.collections,
    this.placeholder = 'جستجوی اذکار...',
    this.onSearch,
  });

  @override
  State<AppSearchBar> createState() => _AppSearchBarState();
}

class _AppSearchBarState extends State<AppSearchBar> {
  final TextEditingController _controller = TextEditingController();
  bool _isFocused = false;
  List<Map<String, dynamic>> _searchResults = [];

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _performSearch(String query) {
    if (query.trim().length < 3) {
      setState(() {
        _searchResults = [];
      });
      return;
    }

    final results = <Map<String, dynamic>>[];
    final queryLower = query.toLowerCase();
    final addedCategories = <String>{};

    for (var collection in widget.collections) {
      final name = collection['name']?.toString().toLowerCase() ?? '';
      final path = collection['path']?.toString() ?? '';

      if (name.contains(queryLower) && !addedCategories.contains(path)) {
        // Check if collection name already contains "اذکار"
        final collectionName = collection['name']?.toString() ?? '';
        final title = collectionName.contains('اذکار') || collectionName.contains('دعا')
            ? collectionName
            : 'اذکار $collectionName';
        
        results.add({
          'id': path,
          'title': title,
          'path': path,
          'type': 'category',
        });
        addedCategories.add(path);
      }
    }

    setState(() {
      _searchResults = results.take(5).toList();
    });
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        children: [
          Container(
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF2D2D2D) : Colors.white,
              borderRadius: BorderRadius.circular(8),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.1),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: TextField(
              controller: _controller,
              onChanged: _performSearch,
              onTap: () => setState(() => _isFocused = true),
              onSubmitted: (_) => setState(() => _isFocused = false),
              decoration: InputDecoration(
                hintText: widget.placeholder,
                hintStyle: TextStyle(
                  color: Colors.grey[600],
                  fontFamily: AppTheme.fontPrimary,
                ),
                prefixIcon: const Icon(
                  FontAwesomeIcons.magnifyingGlass,
                  color: AppTheme.brandSecondary,
                ),
                border: InputBorder.none,
                contentPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
              ),
              style: TextStyle(
                color: isDark ? Colors.white : Colors.black87,
                fontFamily: AppTheme.fontPrimary,
              ),
              textDirection: TextDirection.rtl,
            ),
          ),
          if (_isFocused && _searchResults.isNotEmpty)
            Container(
              margin: const EdgeInsets.only(top: 4),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF2D2D2D) : Colors.white,
                borderRadius: const BorderRadius.only(
                  bottomLeft: Radius.circular(8),
                  bottomRight: Radius.circular(8),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.1),
                    blurRadius: 6,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              constraints: const BoxConstraints(maxHeight: 300),
              child: ListView.builder(
                shrinkWrap: true,
                itemCount: _searchResults.length,
                itemBuilder: (context, index) {
                  final result = _searchResults[index];
                  return InkWell(
                    onTap: () {
                      _controller.clear();
                      setState(() {
                        _isFocused = false;
                        _searchResults = [];
                      });
                      if (widget.onSearch != null) {
                        widget.onSearch!(result['path']);
                      }
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      decoration: BoxDecoration(
                        border: Border(
                          bottom: BorderSide(
                            color: isDark
                                ? Colors.grey[800]!
                                : Colors.grey[200]!,
                            width: 1,
                          ),
                        ),
                      ),
                      child: Text(
                        result['title'],
                        style: TextStyle(
                          color: isDark ? Colors.white : Colors.black87,
                          fontFamily: AppTheme.fontPrimary,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }
}

