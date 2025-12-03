import 'package:flutter/material.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'category_card.dart';
import '../theme/app_theme.dart';

class CollectionsGrid extends StatelessWidget {
  final List<Map<String, dynamic>> collections;
  final Function(String)? onCollectionTap;
  final Function()? onCounterTap;

  const CollectionsGrid({
    super.key,
    required this.collections,
    this.onCollectionTap,
    this.onCounterTap,
  });

  @override
  Widget build(BuildContext context) {
    final collectionsList = [
      {
        'title': 'اذکار صبحگاه',
        'icon': FontAwesomeIcons.sun,
        'slug': 'morning',
        'count': _getCollectionCount('morning'),
      },
      {
        'title': 'اذکار شامگاه',
        'icon': FontAwesomeIcons.moon,
        'slug': 'night',
        'count': _getCollectionCount('night'),
      },
      {
        'title': 'اذکار روزانه',
        'icon': FontAwesomeIcons.calendarDay,
        'slug': 'daily',
        'count': _getCollectionCount('daily'),
      },
      {
        'title': 'اذکار رمضان',
        'icon': FontAwesomeIcons.starAndCrescent,
        'slug': 'ramadan',
        'count': _getCollectionCount('ramadan'),
      },
      {
        'title': 'دعای خواب',
        'icon': FontAwesomeIcons.bed,
        'slug': 'sleep',
        'count': _getCollectionCount('sleep'),
      },
      {
        'title': 'دعای استخاره',
        'icon': FontAwesomeIcons.handsPraying,
        'slug': 'istikhara',
        'count': _getCollectionCount('istikhara'),
      },
    ];

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 3,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 1.0,
        ),
        itemCount: collectionsList.length,
        itemBuilder: (context, index) {
          final item = collectionsList[index];
          return _buildCollectionCard(
            context,
            title: item['title'] as String,
            icon: item['icon'] as IconData,
            count: item['count'] as int?,
            onTap: item['slug'] == null
                ? onCounterTap
                : () => onCollectionTap?.call(item['slug'] as String),
          );
        },
      ),
    );
  }

  Widget _buildCollectionCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    int? count,
    VoidCallback? onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: CategoryCard(
        size: 'small',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.end,
          children: [
            Icon(
              icon,
              size: 24,
              color: Colors.white,
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: const TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Colors.white,
                fontFamily: AppTheme.fontPrimary,
              ),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              softWrap: true,
            ),
          ],
        ),
      ),
    );
  }

  int _getCollectionCount(String slug) {
    try {
      final collection = collections.firstWhere(
        (c) => (c['path'] ?? c['slug'] ?? '').toString() == slug,
      );
      return collection['items'] ?? collection['adhkar_count'] ?? 0;
    } catch (e) {
      return 0;
    }
  }
}

