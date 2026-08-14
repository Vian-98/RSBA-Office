<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format a number to Indonesian Rupiah
     * 
     * @param mixed $number Number to format (integer/float/string)
     * @param bool $withPrefix Include 'Rp' prefix (default: true)
     * @param bool $withDecimals Include decimal points (default: true)
     * @param bool $decimalSeparator Decimal separator (default: ',')
     * @param bool $thousandSeparator Thousand separator (default: '.')
     * @return string Formatted rupiah string
     */
    function formatRupiah($number, $withPrefix = true, $withDecimals = true, $decimalSeparator = ',', $thousandSeparator = '.', $withSpace = false)
    {
        // Convert to float and handle invalid input
        $number = is_numeric($number) ? $number : 0;
        // $number = floatval(str_replace([',', '.'], '', $number));

        // Format the number
        if ($withDecimals) {
            $formatted = number_format($number, 2, $decimalSeparator, $thousandSeparator);
        } else {
            $formatted = number_format($number, 0, $decimalSeparator, $thousandSeparator);
        }

        // Add prefix if required (Sesuai kaidah EYD/PUEBI: Rp tanpa titik dan tanpa spasi)
        if ($withPrefix) {
            if (is_string($withPrefix)) {
                return $withPrefix . $formatted;
            }
            return ($withSpace ? 'Rp ' : 'Rp') . $formatted;
        }


        return $formatted;
    }

}

if (!function_exists('parseRupiah')) {
    /**
     * Parse a Rupiah formatted string back to number => Rp.15.000,50 -> 15000.50
     * 
     * @param string $rupiahString Rupiah formatted string
     * @return float Number value
     */
    function parseRupiah($rupiahString)
    {
        // Remove Rp prefix and any whitespace
        $cleaned = preg_replace('/[^0-9,.]/', '', $rupiahString);

        // Replace thousand separator and convert decimal separator
        $number = str_replace(['.', ','], ['', '.'], $cleaned);

        return floatval($number);
    }
}
