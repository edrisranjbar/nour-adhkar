import 'package:flutter/material.dart';

class AppTheme {
  // Light Mode Colors (matching Vue.js CSS variables)
  static const Color bgPrimary = Color(0xFFD1BB9E);
  static const Color bgSecondary = Color(0xFFFFFFFF);
  static const Color bgTertiary = Color(0xFFF0F0F0); // rgba(240, 240, 240, .67)
  
  static const Color textPrimary = Color(0xFF2C2A2A);
  static const Color textSecondary = Color(0xFF666666);
  static const Color textLight = Color(0xFFFFFFFF);
  
  static const Color brandPrimary = Color(0xFF9C8466);
  static const Color brandSecondary = Color(0xFFA79277);
  static const Color brandLight = Color(0xFFC5B192);
  static const Color brandDark = Color(0xFF76644A);
  
  static const Color uiBorder = Color(0x4D9C8466); // rgba(156, 132, 102, 0.3)
  static const Color uiShadow = Color(0x40000000); // rgba(0, 0, 0, 0.25)
  static const Color uiShadowLight = Color(0x1A000000); // rgba(0, 0, 0, 0.1)
  
  static const Color success = Color(0xFF4CAF50);
  static const Color warning = Color(0xFFFFC107);
  static const Color danger = Color(0xFFE53935);
  static const Color info = Color(0xFF2196F3);
  
  // Dark Mode Colors
  static const Color darkBgPrimary = Color(0xFF2C2A2A);
  static const Color darkBgSecondary = Color(0xFF1E1E1E);
  static const Color darkBgTertiary = Color(0xFF3A3838);
  
  static const Color darkTextPrimary = Color(0xFFE0E0E0);
  static const Color darkTextSecondary = Color(0xFFBBBBBB);
  
  static const Color darkBrandPrimary = Color(0xFFB9A583);
  static const Color darkBrandSecondary = Color(0xFFC5B192);
  static const Color darkBrandLight = Color(0xFFD1BB9E);
  static const Color darkBrandDark = Color(0xFFA79277);
  
  // Font Families
  static const String fontPrimary = 'Vazir';
  static const String fontArabic = 'Othman Taha';
  
  // Light Theme
  static ThemeData lightTheme = ThemeData(
    useMaterial3: true,
    fontFamily: fontPrimary,
    brightness: Brightness.light,
    primaryColor: brandPrimary,
    scaffoldBackgroundColor: bgPrimary,
    colorScheme: const ColorScheme.light(
      primary: brandPrimary,
      secondary: brandSecondary,
      surface: bgSecondary,
      error: danger,
      onPrimary: textLight,
      onSecondary: textLight,
      onSurface: textPrimary,
      onError: textLight,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: brandSecondary,
      foregroundColor: textLight,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 24,
        fontWeight: FontWeight.w500,
        color: textLight,
      ),
    ),
    cardTheme: CardThemeData(
      color: bgTertiary,
      elevation: 4,
      shadowColor: uiShadow,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
      ),
    ),
    textTheme: const TextTheme(
      displayLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 48,
        fontWeight: FontWeight.w400,
        color: textPrimary,
      ),
      displayMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 36,
        fontWeight: FontWeight.w400,
        color: textPrimary,
      ),
      displaySmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 30,
        fontWeight: FontWeight.w400,
        color: textPrimary,
      ),
      headlineLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 24,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      headlineMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 20,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      headlineSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      titleLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 20,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      titleMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      titleSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      bodyLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w400,
        color: textPrimary,
      ),
      bodyMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w400,
        color: textPrimary,
      ),
      bodySmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 14,
        fontWeight: FontWeight.w400,
        color: textSecondary,
      ),
      labelLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      labelMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 14,
        fontWeight: FontWeight.w500,
        color: textPrimary,
      ),
      labelSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: textSecondary,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: brandPrimary,
        foregroundColor: textLight,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        elevation: 2,
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: brandPrimary,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: bgSecondary,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: uiBorder),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: uiBorder),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: brandPrimary, width: 2),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
    ),
  );
  
  // Dark Theme
  static ThemeData darkTheme = ThemeData(
    useMaterial3: true,
    fontFamily: fontPrimary,
    brightness: Brightness.dark,
    primaryColor: darkBrandPrimary,
    scaffoldBackgroundColor: darkBgPrimary,
    colorScheme: const ColorScheme.dark(
      primary: darkBrandPrimary,
      secondary: darkBrandSecondary,
      surface: darkBgTertiary,
      error: danger,
      onPrimary: textLight,
      onSecondary: textLight,
      onSurface: darkTextPrimary,
      onError: textLight,
    ),
    appBarTheme: const AppBarTheme(
      backgroundColor: darkBgTertiary,
      foregroundColor: darkTextPrimary,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 24,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
    ),
    cardTheme: CardThemeData(
      color: darkBgTertiary,
      elevation: 4,
      shadowColor: uiShadow,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
      ),
    ),
    textTheme: const TextTheme(
      displayLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 48,
        fontWeight: FontWeight.w400,
        color: darkTextPrimary,
      ),
      displayMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 36,
        fontWeight: FontWeight.w400,
        color: darkTextPrimary,
      ),
      displaySmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 30,
        fontWeight: FontWeight.w400,
        color: darkTextPrimary,
      ),
      headlineLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 24,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      headlineMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 20,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      headlineSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      titleLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 20,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      titleMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      titleSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      bodyLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 18,
        fontWeight: FontWeight.w400,
        color: darkTextPrimary,
      ),
      bodyMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w400,
        color: darkTextPrimary,
      ),
      bodySmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 14,
        fontWeight: FontWeight.w400,
        color: darkTextSecondary,
      ),
      labelLarge: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 16,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      labelMedium: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 14,
        fontWeight: FontWeight.w500,
        color: darkTextPrimary,
      ),
      labelSmall: TextStyle(
        fontFamily: fontPrimary,
        fontSize: 12,
        fontWeight: FontWeight.w500,
        color: darkTextSecondary,
      ),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: darkBrandPrimary,
        foregroundColor: textLight,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
        elevation: 2,
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: darkBrandPrimary,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(8),
        ),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: darkBgTertiary,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: uiBorder),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: uiBorder),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: darkBrandPrimary, width: 2),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
    ),
  );
}

