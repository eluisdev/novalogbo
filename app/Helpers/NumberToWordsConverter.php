<?php

namespace App\Helpers;

class NumberToWordsConverter
{
    private static $UNIDADES = [
        '', 'UN ', 'DOS ', 'TRES ', 'CUATRO ', 'CINCO ', 'SEIS ', 'SIETE ', 'OCHO ', 'NUEVE ', 'DIEZ ',
        'ONCE ', 'DOCE ', 'TRECE ', 'CATORCE ', 'QUINCE ', 'DIECISÉIS ', 'DIECISIETE ', 'DIECIOCHO ', 'DIECINUEVE ', 'VEINTE '
    ];

    private static $DECENAS = [
        'VEINTI', 'TREINTA ', 'CUARENTA ', 'CINCUENTA ', 'SESENTA ', 'SETENTA ', 'OCHENTA ', 'NOVENTA '
    ];

    private static $CENTENAS = [
        'CIENTO ', 'DOSCIENTOS ', 'TRESCIENTOS ', 'CUATROCIENTOS ', 'QUINIENTOS ',
        'SEISCIENTOS ', 'SETECIENTOS ', 'OCHOCIENTOS ', 'NOVECIENTOS '
    ];

    public static function convertToWords($number, $currency = 'DÓLARES AMERICANOS')
    {
        $number = self::cleanNumber($number);
        $parts = explode('.', $number);
        $entero = $parts[0];
        $decimals = isset($parts[1]) ? $parts[1] : '00';

        $converted = ($entero == '0') ? 'CERO ' : self::convertIntegerPart($entero);
        $result = trim($converted) . ' ' . strtoupper($currency) . ' ' . $decimals . '/100';

        return $result;
    }

    private static function cleanNumber($number)
    {
        $clean = preg_replace('/[^0-9.]/', '', $number);
        $parts = explode('.', $clean);
        $entero = $parts[0] ?? '0';
        $decimal = isset($parts[1]) ? substr($parts[1], 0, 2) : '00';
        return $entero . '.' . str_pad($decimal, 2, '0');
    }

    private static function convertIntegerPart($number)
    {
        $number = ltrim($number, '0');
        if (strlen($number) > 9) return 'NÚMERO DEMASIADO GRANDE';

        $output = '';
        $millones = floor($number / 1000000);
        $miles = floor(($number % 1000000) / 1000);
        $cientos = $number % 1000;

        if ($millones > 0) {
            $output .= self::convertGroup($millones);
            $output .= ($millones == 1) ? 'MILLÓN ' : 'MILLONES ';
        }

        if ($miles > 0) {
            $output .= self::convertGroup($miles) . 'MIL ';
        }

        if ($cientos > 0) {
            $output .= self::convertGroup($cientos);
        }

        return $output;
    }

    private static function convertGroup($n)
    {
        $output = '';
        $n = intval($n);

        if ($n == 100) return 'CIEN ';

        if ($n >= 100) {
            $output = self::$CENTENAS[floor($n / 100) - 1];
            $n %= 100;
        }

        if ($n >= 20 && $n < 30) {
            $output .= self::$DECENAS[0] . self::$UNIDADES[$n % 10];
        } elseif ($n >= 30) {
            $output .= self::$DECENAS[floor($n / 10) - 1];
            if ($n % 10 > 0) $output .= 'Y ' . self::$UNIDADES[$n % 10];
        } else {
            $output .= self::$UNIDADES[$n];
        }

        return $output;
    }
}
