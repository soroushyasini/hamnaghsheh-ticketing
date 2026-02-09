<?php
/**
 * Persian/Jalali Date Conversion Helper
 *
 * @package    Hamnaghsheh_Ticketing
 * @subpackage Hamnaghsheh_Ticketing/includes
 */

class Hamnaghsheh_Ticketing_Jalali {

    /**
     * Convert Gregorian date to Persian/Jalali date
     *
     * @param string $format Date format. Supported formats:
     *                       - Y: 4-digit year
     *                       - y: 2-digit year
     *                       - m: Month with leading zero (01-12)
     *                       - n: Month without leading zero (1-12)
     *                       - d: Day with leading zero (01-31)
     *                       - j: Day without leading zero (1-31)
     *                       - H, i, s: Time components (passed through from PHP date())
     * @param int|null $timestamp Unix timestamp (null for current time)
     * @return string Formatted Persian date
     */
    public static function jdate($format = 'Y-m-d', $timestamp = null) {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        list($gYear, $gMonth, $gDay) = explode('-', date('Y-m-d', $timestamp));
        list($jYear, $jMonth, $jDay) = self::gregorian_to_jalali($gYear, $gMonth, $gDay);
        
        $replacements = [
            'Y' => str_pad($jYear, 4, '0', STR_PAD_LEFT),
            'y' => substr($jYear, -2),
            'm' => str_pad($jMonth, 2, '0', STR_PAD_LEFT),
            'n' => $jMonth,
            'd' => str_pad($jDay, 2, '0', STR_PAD_LEFT),
            'j' => $jDay,
        ];
        
        $result = $format;
        foreach ($replacements as $key => $value) {
            $result = str_replace($key, $value, $result);
        }
        
        // Handle time components using PHP's date function
        // Replace any remaining format characters with values from PHP date()
        $time_format = preg_replace('/[Yymndj]/', '', $format);
        if (!empty($time_format)) {
            $time_parts = date($time_format, $timestamp);
            $result = preg_replace('/[His:\/\s-]+/', $time_parts, $result, 1);
        }
        
        return $result;
    }

    /**
     * Convert Gregorian to Jalali date
     *
     * @param int $g_y Gregorian year
     * @param int $g_m Gregorian month
     * @param int $g_d Gregorian day
     * @return array [jalali_year, jalali_month, jalali_day]
     */
    private static function gregorian_to_jalali($g_y, $g_m, $g_d) {
        $g_y = (int)$g_y;
        $g_m = (int)$g_m;
        $g_d = (int)$g_d;
        
        $gy = $g_y - 1600;
        $gm = $g_m - 1;
        $gd = $g_d - 1;

        $g_day_no = 365 * $gy + floor(($gy + 3) / 4) - floor(($gy + 99) / 100) + floor(($gy + 399) / 400);

        for ($i = 0; $i < $gm; ++$i) {
            $g_day_no += self::g_days_in_month($i, $g_y);
        }

        $g_day_no += $gd;

        $j_day_no = $g_day_no - 79;

        $j_np = floor($j_day_no / 12053);
        $j_day_no = $j_day_no % 12053;

        $jy = 979 + 33 * $j_np + 4 * floor($j_day_no / 1461);

        $j_day_no %= 1461;

        if ($j_day_no >= 366) {
            $jy += floor(($j_day_no - 1) / 365);
            $j_day_no = ($j_day_no - 1) % 365;
        }

        $j_month_days = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        if (self::is_jalali_leap($jy)) {
            $j_month_days[11] = 30;
        }

        $jm = 0;
        for ($i = 0; $i < 12; ++$i) {
            if ($j_day_no < $j_month_days[$i]) {
                $jm = $i + 1;
                break;
            }
            $j_day_no -= $j_month_days[$i];
        }

        $jd = $j_day_no + 1;

        return [$jy, $jm, $jd];
    }

    /**
     * Get number of days in Gregorian month
     */
    private static function g_days_in_month($month, $year) {
        $days = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        
        if ($month == 1 && self::is_gregorian_leap($year)) {
            return 29;
        }
        
        return $days[$month];
    }

    /**
     * Check if Gregorian year is leap
     */
    private static function is_gregorian_leap($year) {
        return (($year % 4 == 0) && ($year % 100 != 0)) || ($year % 400 == 0);
    }

    /**
     * Check if Jalali year is leap
     */
    private static function is_jalali_leap($year) {
        $breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
        $gy = $year + 621;
        $leapJ = -14;
        $jp = $breaks[0];

        $jump = 0;
        for ($i = 1; $i <= 19; $i++) {
            $jm = $breaks[$i];
            $jump = $jm - $jp;
            if ($year < $jm) {
                break;
            }
            $leapJ = $leapJ + floor($jump / 33) * 8 + floor(($jump % 33) / 4);
            $jp = $jm;
        }
        $n = $year - $jp;

        $leapJ = $leapJ + floor($n / 33) * 8 + floor(($n % 33 + 3) / 4);

        if (($jump % 33) == 4 && $jump - $n == 4) {
            $leapJ += 1;
        }

        $leapG = floor($gy / 4) - floor((floor($gy / 100) + 1) * 3 / 4) - 150;

        return ($leapG - $leapJ) == 0;
    }

    /**
     * Get Persian month name
     */
    public static function get_month_name($month) {
        $months = [
            1 => 'فروردین',
            2 => 'اردیبهشت',
            3 => 'خرداد',
            4 => 'تیر',
            5 => 'مرداد',
            6 => 'شهریور',
            7 => 'مهر',
            8 => 'آبان',
            9 => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند'
        ];
        
        return isset($months[$month]) ? $months[$month] : '';
    }
}
