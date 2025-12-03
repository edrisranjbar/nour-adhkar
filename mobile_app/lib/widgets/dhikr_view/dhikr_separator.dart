import 'package:flutter/material.dart';

class DhikrSeparator extends StatelessWidget {
  final Color brandColor;

  const DhikrSeparator({
    super.key,
    required this.brandColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 1,
      margin: const EdgeInsets.symmetric(horizontal: 20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Colors.transparent,
            brandColor.withOpacity(0.3),
            brandColor.withOpacity(0.6),
            brandColor.withOpacity(0.3),
            Colors.transparent,
          ],
        ),
        borderRadius: BorderRadius.circular(1),
      ),
    );
  }
}

