import 'package:flutter/material.dart';

class AppTheme {
  // Brand Colors - Elegant earth tones matching web design
  static const Color primaryBrown = Color(0xFF8B6F47);
  static const Color secondaryGold = Color(0xFFD4AF37);
  static const Color accentBeige = Color(0xFFF5E6D3);
  static const Color darkBrown = Color(0xFF5D4E37);
  static const Color lightBeige = Color(0xFFFAF6F0);
  
  // Semantic Colors
  static const Color success = Color(0xFF4CAF50);
  static const Color error = Color(0xFFE53935);
  static const Color warning = Color(0xFFFFA726);
  static const Color info = Color(0xFF29B6F6);
  
  // Neutral Colors
  static const Color textDark = Color(0xFF2C2C2C);
  static const Color textLight = Color(0xFF757575);
  static const Color background = Color(0xFFFFFBF5);
  static const Color surface = Colors.white;
  static const Color divider = Color(0xFFE0E0E0);

  static ThemeData get lightTheme {
    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.light(
        primary: primaryBrown,
        secondary: secondaryGold,
        tertiary: accentBeige,
        surface: surface,
        background: background,
        error: error,
        onPrimary: Colors.white,
        onSecondary: darkBrown,
        onSurface: textDark,
        onBackground: textDark,
      ),
      
      // Typography
      fontFamily: 'Source Sans Pro',
      textTheme: const TextTheme(
        displayLarge: TextStyle(
          fontFamily: 'Playfair Display',
          fontSize: 32,
          fontWeight: FontWeight.bold,
          color: textDark,
        ),
        displayMedium: TextStyle(
          fontFamily: 'Playfair Display',
          fontSize: 28,
          fontWeight: FontWeight.bold,
          color: textDark,
        ),
        displaySmall: TextStyle(
          fontFamily: 'Playfair Display',
          fontSize: 24,
          fontWeight: FontWeight.bold,
          color: textDark,
        ),
        headlineMedium: TextStyle(
          fontFamily: 'Playfair Display',
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: textDark,
        ),
        titleLarge: TextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: textDark,
        ),
        titleMedium: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w600,
          color: textDark,
        ),
        bodyLarge: TextStyle(
          fontSize: 16,
          color: textDark,
        ),
        bodyMedium: TextStyle(
          fontSize: 14,
          color: textDark,
        ),
        bodySmall: TextStyle(
          fontSize: 12,
          color: textLight,
        ),
      ),
      
      // App Bar Theme
      appBarTheme: const AppBarTheme(
        backgroundColor: surface,
        foregroundColor: textDark,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: TextStyle(
          fontFamily: 'Playfair Display',
          fontSize: 20,
          fontWeight: FontWeight.bold,
          color: textDark,
        ),
      ),
      
      // Card Theme
      cardTheme: CardTheme(
        elevation: 2,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
        ),
        color: surface,
      ),
      
      // Elevated Button Theme
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: primaryBrown,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      
      // Outlined Button Theme
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: primaryBrown,
          side: const BorderSide(color: primaryBrown, width: 1.5),
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      
      // Text Button Theme
      textButtonTheme: TextButtonThemeData(
        style: TextButton.styleFrom(
          foregroundColor: primaryBrown,
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      
      // Input Decoration Theme
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: lightBeige,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: primaryBrown, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: error, width: 1),
        ),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      
      // Bottom Navigation Bar Theme
      bottomNavigationBarTheme: const BottomNavigationBarThemeData(
        backgroundColor: surface,
        selectedItemColor: primaryBrown,
        unselectedItemColor: textLight,
        type: BottomNavigationBarType.fixed,
        elevation: 8,
      ),
      
      // Chip Theme
      chipTheme: ChipThemeData(
        backgroundColor: accentBeige,
        labelStyle: const TextStyle(color: darkBrown),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
      ),
      
      // Divider Theme
      dividerTheme: const DividerThemeData(
        color: divider,
        thickness: 1,
        space: 1,
      ),
    );
  }
}
