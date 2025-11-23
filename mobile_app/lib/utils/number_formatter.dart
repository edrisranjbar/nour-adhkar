class NumberFormatter {
  // Convert Western Arabic digits to Persian digits
  static String toPersianDigits(String text) {
    const Map<String, String> persianDigits = {
      '0': '۰',
      '1': '۱',
      '2': '۲',
      '3': '۳',
      '4': '۴',
      '5': '۵',
      '6': '۶',
      '7': '۷',
      '8': '۸',
      '9': '۹',
    };
    
    String result = text;
    persianDigits.forEach((english, persian) {
      result = result.replaceAll(english, persian);
    });
    return result;
  }
  
  // Convert a number to Persian digits string
  static String formatNumber(int number) {
    return toPersianDigits(number.toString());
  }
  
  // Convert a number to Persian digits string (for double)
  static String formatDouble(double number, {int decimals = 0}) {
    if (decimals == 0) {
      return toPersianDigits(number.toInt().toString());
    }
    return toPersianDigits(number.toStringAsFixed(decimals));
  }
}


