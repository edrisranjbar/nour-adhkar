import 'package:flutter/material.dart';

class HeartScoreDisplay extends StatelessWidget {
  final int heartScore;

  const HeartScoreDisplay({super.key, required this.heartScore});

  @override
  Widget build(BuildContext context) {
    final fillPercentage = (heartScore / 100).clamp(0.0, 1.0);

    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 24,
          height: 24,
          child: CustomPaint(
            painter: HeartPainter(
              fillPercentage: fillPercentage,
              heartColor: Colors.red.shade600,
              backgroundColor: Colors.white.withOpacity(0.2),
            ),
          ),
        ),
        const SizedBox(width: 6),
        Text(
          '$heartScore/100',
          style: const TextStyle(
            color: Colors.white,
            fontSize: 14,
            fontWeight: FontWeight.w600,
            fontFamily: 'Vazir',
          ),
        ),
      ],
    );
  }
}

class HeartPainter extends CustomPainter {
  final double fillPercentage;
  final Color heartColor;
  final Color backgroundColor;

  HeartPainter({
    required this.fillPercentage,
    required this.heartColor,
    required this.backgroundColor,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..style = PaintingStyle.fill
      ..color = backgroundColor;

    // Draw background heart outline
    final backgroundPath = _createHeartPath(size);
    canvas.drawPath(backgroundPath, paint);

    // Draw filled heart based on percentage
    if (fillPercentage > 0) {
      paint.color = heartColor;

      // Create a path for the filled portion
      final fillPath = _createHeartPath(size);

      // Create a clip rect based on fill percentage (from bottom to top)
      final fillHeight = size.height * fillPercentage;
      final fillRect = Rect.fromLTRB(
        0,
        size.height - fillHeight,
        size.width,
        size.height,
      );

      canvas.save();
      canvas.clipRect(fillRect);
      canvas.drawPath(fillPath, paint);
      canvas.restore();

      // Add some glow effect for higher scores
      if (fillPercentage > 0.7) {
        paint.color = heartColor.withOpacity(0.3);
        paint.maskFilter = const MaskFilter.blur(BlurStyle.normal, 2);
        canvas.drawPath(fillPath, paint);
        paint.maskFilter = null;
      }
    }

    // Draw heart outline
    final outlinePaint = Paint()
      ..style = PaintingStyle.stroke
      ..color = Colors.white.withOpacity(0.8)
      ..strokeWidth = 1.5;

    canvas.drawPath(_createHeartPath(size), outlinePaint);

    // Add veins/lines for realism when heart is filled
    if (fillPercentage > 0.3) {
      _drawVeins(canvas, size, fillPercentage);
    }
  }

  Path _createHeartPath(Size size) {
    final path = Path();
    final width = size.width;
    final height = size.height;

    // Heart shape using bezier curves
    path.moveTo(width / 2, height * 0.25);

    // Left curve
    path.cubicTo(
      width * 0.1,
      height * 0.1,
      0,
      height * 0.3,
      width * 0.2,
      height * 0.6,
    );

    // Bottom left curve
    path.cubicTo(
      width * 0.2,
      height * 0.8,
      width * 0.3,
      height * 0.9,
      width / 2,
      height,
    );

    // Bottom right curve
    path.cubicTo(
      width * 0.7,
      height * 0.9,
      width * 0.8,
      height * 0.8,
      width * 0.8,
      height * 0.6,
    );

    // Right curve
    path.cubicTo(
      width,
      height * 0.3,
      width * 0.9,
      height * 0.1,
      width / 2,
      height * 0.25,
    );

    path.close();
    return path;
  }

  void _drawVeins(Canvas canvas, Size size, double fillPercentage) {
    final veinPaint = Paint()
      ..color = Colors.red.shade800.withOpacity(fillPercentage * 0.6)
      ..strokeWidth = 1.0
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final width = size.width;
    final height = size.height;
    final filledHeight = height * fillPercentage;

    // Only draw veins in the filled portion
    if (filledHeight > height * 0.4) {
      // Main vein from bottom to top
      canvas.drawLine(
        Offset(width * 0.5, height),
        Offset(width * 0.5, height - filledHeight + height * 0.1),
        veinPaint,
      );

      // Left branch
      if (filledHeight > height * 0.6) {
        canvas.drawLine(
          Offset(width * 0.5, height - filledHeight * 0.6),
          Offset(width * 0.35, height - filledHeight * 0.7),
          veinPaint,
        );
      }

      // Right branch
      if (filledHeight > height * 0.7) {
        canvas.drawLine(
          Offset(width * 0.5, height - filledHeight * 0.5),
          Offset(width * 0.65, height - filledHeight * 0.6),
          veinPaint,
        );
      }
    }
  }

  @override
  bool shouldRepaint(covariant HeartPainter oldDelegate) {
    return oldDelegate.fillPercentage != fillPercentage ||
        oldDelegate.heartColor != heartColor ||
        oldDelegate.backgroundColor != backgroundColor;
  }
}
