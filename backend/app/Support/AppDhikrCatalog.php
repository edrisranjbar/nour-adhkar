<?php

namespace App\Support;

/** The app's built-in adhkar collections and the collection icons it can draw. */
final class AppDhikrCatalog
{
    /** Built-in collection ids in the app (AdhkarData.kt), with their titles. */
    public const BUILT_IN = [
        'morning' => 'اذکار صبحگاه',
        'evening' => 'اذکار شامگاه',
        'daily' => 'اذکار روزانه',
        'sleep' => 'اذکار خواب',
        'after_salah' => 'اذکار پس از نماز',
        'after_adhan' => 'دعای پس از اذان',
        'quran_prayers' => 'دعا و نیایش‌های قرآنی',
        'sunnah_prayers' => 'دعاهایی از سنت رسول (ص)',
        'ramadan' => 'اذکار ماه رمضان',
        'waking_up' => 'ذکر بیدار شدن',
        'entering_bathroom' => 'دعای ورود به سرویس بهداشتی',
        'leaving_bathroom' => 'دعای خروج از سرویس بهداشتی',
        'clothing' => 'اذکار پوشیدن لباس',
        'before_eating' => 'ذکر پیش از غذا',
        'after_eating' => 'ذکر پس از غذا',
        'leaving_home' => 'ذکر خروج از خانه',
        'entering_home' => 'ذکر ورود به خانه',
        'entering_mosque' => 'دعای ورود به مسجد',
        'leaving_mosque' => 'دعای خروج از مسجد',
        'riding_vehicle' => 'دعای سوار شدن',
        'starting_journey' => 'دعای آغاز سفر',
        'returning_travel' => 'ذکر بازگشت از سفر',
        'night_restlessness' => 'دعای بی‌قراری شبانه',
        'istikhara' => 'دعای استخاره',
        'funeral_prayer' => 'دعای نماز جنازه',
    ];

    /** Icons the app draws for a new collection (HomeQuickAccess.iconForName). */
    public const ICONS = [
        'menu_book' => 'کتاب',
        'wb_sunny' => 'خورشید',
        'nights_stay' => 'ماه و ستاره',
        'bedtime' => 'خواب',
        'alarm' => 'ساعت زنگ‌دار',
        'mosque' => 'مسجد',
        'self_improvement' => 'نماز و تأمل',
        'volunteer_activism' => 'دست و قلب',
        'psychology' => 'اندیشه',
        'campaign' => 'اذان و ندا',
        'nightlight' => 'هلال ماه',
        'restaurant' => 'غذا',
        'checkroom' => 'لباس',
        'directions_car' => 'خودرو',
        'flight_takeoff' => 'سفر',
        'flight_land' => 'بازگشت از سفر',
    ];

    public static function collectionTitle(object $collection): string
    {
        return $collection->builtin_key
            ? (self::BUILT_IN[$collection->builtin_key] ?? $collection->builtin_key)
            : (string) $collection->title;
    }
}
