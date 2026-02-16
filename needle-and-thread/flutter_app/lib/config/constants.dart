class AppConstants {
  // API Configuration
  static const String baseUrl = 'http://localhost:3000/api';
  static const String uploadsUrl = 'http://localhost:3000/uploads';
  
  // For Android Emulator, use: http://10.0.2.2:3000/api
  // For iOS Simulator, use: http://localhost:3000/api
  // For Real Device, use your computer's IP: http://192.168.1.XXX:3000/api
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String cartKey = 'cart_data';
  
  // App Info
  static const String appName = 'Needle & Thread';
  static const String appVersion = '1.0.0';
  
  // Pagination
  static const int productsPerPage = 9;
  static const int ordersPerPage = 20;
  
  // Validation
  static const int minPasswordLength = 6;
  static const int maxFileSize = 5 * 1024 * 1024; // 5MB
  
  // Timeouts
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
}
