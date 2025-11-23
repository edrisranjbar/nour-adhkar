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
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Column(
        children: [
          // Morning and Night
          _buildCardRow([
            _buildCollectionCard(
              context,
              title: 'اذکار صبحگاه',
              icon: FontAwesomeIcons.sun,
              count: _getCollectionCount('morning'),
              onTap: () => onCollectionTap?.call('morning'),
            ),
            _buildCollectionCard(
              context,
              title: 'اذکار شامگاه',
              icon: FontAwesomeIcons.moon,
              count: _getCollectionCount('night'),
              onTap: () => onCollectionTap?.call('night'),
            ),
          ]),
          const SizedBox(height: 16),
          // Daily and Ramadan
          _buildCardRow([
            _buildCollectionCard(
              context,
              title: 'اذکار روزانه',
              icon: FontAwesomeIcons.calendarDay,
              count: _getCollectionCount('daily'),
              onTap: () => onCollectionTap?.call('daily'),
            ),
            _buildCollectionCard(
              context,
              title: 'اذکار ماه رمضان',
              icon: FontAwesomeIcons.starAndCrescent,
              count: _getCollectionCount('ramadan'),
              onTap: () => onCollectionTap?.call('ramadan'),
            ),
          ]),
          const SizedBox(height: 16),
          // Sleep and Istikhara
          _buildCardRow([
            _buildCollectionCard(
              context,
              title: 'دعای خواب',
              icon: FontAwesomeIcons.bed,
              count: _getCollectionCount('sleep'),
              onTap: () => onCollectionTap?.call('sleep'),
            ),
            _buildCollectionCard(
              context,
              title: 'دعای استخاره',
              icon: FontAwesomeIcons.handsPraying,
              count: _getCollectionCount('istikhara'),
              onTap: () => onCollectionTap?.call('istikhara'),
            ),
          ]),
          const SizedBox(height: 16),
          // Counter
          _buildCardRow([
            _buildCollectionCard(
              context,
              title: 'تسبیح شمار',
              icon: FontAwesomeIcons.circleNotch,
              count: null,
              onTap: onCounterTap,
            ),
          ]),
        ],
      ),
    );
  }

  Widget _buildCardRow(List<Widget> cards) {
    return Row(
      children: cards
          .expand((card) => [card, const SizedBox(width: 16)])
          .take(cards.length * 2 - 1)
          .toList(),
    );
  }

  Widget _buildCollectionCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    int? count,
    VoidCallback? onTap,
  }) {
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        child: CategoryCard(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              Icon(
                icon,
                size: 32,
                color: Colors.white,
              ),
              const SizedBox(height: 12),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w600,
                  color: Colors.white,
                  fontFamily: AppTheme.fontPrimary,
                ),
              ),
              if (count != null) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.2),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    '$count ذکر',
                    style: const TextStyle(
                      fontSize: 12,
                      color: Colors.white,
                      fontFamily: AppTheme.fontPrimary,
                    ),
                  ),
                ),
              ],
            ],
          ),
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

