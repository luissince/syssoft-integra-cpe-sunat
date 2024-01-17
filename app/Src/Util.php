<?php

namespace App\Src;

class Util
{

    public static function calculateTax(float $porcentaje, float $valor)
    {
        return (float) ($valor * ($porcentaje / 100.00));
    }

    public static function calculateTaxBruto(float $impuesto, float $monto)
    {
        return $monto / (($impuesto + 100) * 0.01);
    }

    public static function formatNumber($numeracion, $length = 6)
    {
        return strlen($numeracion) > $length ? $numeracion : substr(str_repeat(0, $length) . $numeracion, -$length);
    }

    public static function roundingValue(float $valor, int $decimals = 2)
    {
        return number_format(round($valor, 2, PHP_ROUND_HALF_UP), $decimals, '.', '');
    }

    public static function round($value, $precision = 2)
    {
        return round($value, $precision, PHP_ROUND_HALF_UP);
    }
}
