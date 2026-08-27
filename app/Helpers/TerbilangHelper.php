<?php

if (!function_exists('bilangan')) {
    function bilangan($number)
    {
        $number = (int) round(abs($number));
        $words = ["", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"];
        $temp = "";

        if ($number < 12) {
            $temp = " " . $words[$number];
        } else if ($number < 20) {
            $temp = bilangan($number - 10) . " belas ";
        } else if ($number < 100) {
            $temp = bilangan((int)($number / 10)) . " puluh " . bilangan($number % 10);
        } else if ($number < 200) {
            $temp = " seratus " . bilangan($number - 100);
        } else if ($number < 1000) {
            $temp = bilangan((int)($number / 100)) . " ratus " . bilangan($number % 100);
        } else if ($number < 2000) {
            $temp = " seribu " . bilangan($number - 1000);
        } else if ($number < 1000000) {
            $temp = bilangan((int)($number / 1000)) . " ribu " . bilangan($number % 1000);
        } else if ($number < 1000000000) {
            $temp = bilangan((int)($number / 1000000)) . " juta " . bilangan($number % 1000000);
        } else if ($number < 1000000000000) {
            $temp = bilangan((int)($number / 1000000000)) . " milyar " . bilangan((int)fmod($number, 1000000000));
        } else {
            $temp = bilangan((int)($number / 1000000000000)) . " triliun " . bilangan((int)fmod($number, 1000000000000));
        }

        return trim($temp);
    }
}


if (!function_exists('terbilang')) {
    function terbilang($angka)
    {
        return ucwords(bilangan($angka)) . ' Rupiah';
    }
}
