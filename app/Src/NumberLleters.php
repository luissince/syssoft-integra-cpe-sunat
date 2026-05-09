<?php

namespace App\Src;

class NumberLleters
{


    public function __construct()
    {
    }

    private static function Unidades($num)
    {

        switch ($num) {
            case 1:
                return "UNO";
            case 2:
                return "DOS";
            case 3:
                return "TRES";
            case 4:
                return "CUATRO";
            case 5:
                return "CINCO";
            case 6:
                return "SEIS";
            case 7:
                return "SIETE";
            case 8:
                return "OCHO";
            case 9:
                return "NUEVE";
        }

        return "";
    }

    public static function Decenas($num)
    {

        $decena = floor($num / 10);
        $unidad = $num - ($decena * 10);

        switch ($decena) {
            case 1:
                switch ($unidad) {
                    case 0:
                        return "DIEZ";
                    case 1:
                        return "ONCE";
                    case 2:
                        return "DOCE";
                    case 3:
                        return "TRECE";
                    case 4:
                        return "CATORCE";
                    case 5:
                        return "QUINCE";
                    default:
                        return "DIECI" . self::Unidades($unidad);
                }
            case 2:
                switch ($unidad) {
                    case 0:
                        return "VEINTE";
                    default:
                        return "VEINTI" . self::Unidades($unidad);
                }
            case 3:
                return self::DecenasY("TREINTA", $unidad);
            case 4:
                return self::DecenasY("CUARENTA", $unidad);
            case 5:
                return self::DecenasY("CINCUENTA", $unidad);
            case 6:
                return self::DecenasY("SESENTA", $unidad);
            case 7:
                return self::DecenasY("SETENTA", $unidad);
            case 8:
                return self::DecenasY("OCHENTA", $unidad);
            case 9:
                return self::DecenasY("NOVENTA", $unidad);
            case 0:
                return self::Unidades($unidad);
        }
    }

    private static function DecenasY($strSin, $numUnidades)
    {
        if ($numUnidades > 0)
            return $strSin . " Y " . self::Unidades($numUnidades);

        return $strSin;
    }

    static function Centenas($num)
    {

        $centenas = floor($num / 100);
        $decenas = $num - ($centenas * 100);

        switch ($centenas) {
            case 1:
                if ($decenas > 0)
                    return "CIENTO " . self::Decenas($decenas);
                return "CIEN";
            case 2:
                return "DOSCIENTOS " . self::Decenas($decenas);
            case 3:
                return "TRESCIENTOS " . self::Decenas($decenas);
            case 4:
                return "CUATROCIENTOS " . self::Decenas($decenas);
            case 5:
                return "QUINIENTOS " . self::Decenas($decenas);
            case 6:
                return "SEISCIENTOS " . self::Decenas($decenas);
            case 7:
                return "SETECIENTOS " . self::Decenas($decenas);
            case 8:
                return "OCHOCIENTOS " . self::Decenas($decenas);
            case 9:
                return "NOVECIENTOS " . self::Decenas($decenas);
        }

        return self::Decenas($decenas);
    }


    static function Seccion($num, $divisor, $strSingular, $strPlural)
    {
        $cientos = floor($num / $divisor);
        $resto = $num - ($cientos * $divisor);

        $letras = "";

        if ($cientos > 0)
            if ($cientos > 1)
                $letras = self::Centenas($cientos) . " " . $strPlural;
            else
                $letras = $strSingular;


        if ($resto > 0)
            $letras .= "";


        return $letras;
    }

    function Miles($num)
    {
        $divisor = 1000;
        $cientos = floor($num / $divisor);
        $resto = $num - ($cientos * $divisor);

        $strMiles = self::Seccion($num, $divisor, "MIL", "MIL");
        $strCentenas = self::Centenas($resto);

        if ($strMiles == "")
            return $strCentenas;

        return $strMiles . " " . $strCentenas;
    }

    static function Millones($num)
    {
        $divisor = 1000000;
        $cientos = floor($num / $divisor);
        $resto = $num - ($cientos * $divisor);

        $strMillones = self::Seccion($num, $divisor, "UN MILLON", "MILLONES");
        $strMiles = self::Miles($resto);

        if ($strMillones == "")
            return $strMiles;

        return $strMillones . " " . $strMiles;

        //return Seccion(num, divisor, "UN MILLON", "MILLONES") + " " + Miles(resto);
    }

    public static function getResult($num, $moneda)
    {
        $numero = $num;
        $arnum = explode(".", $numero);

        if (count($arnum) == 0) {
            "0";
        } else {

            $num = 0;
            $decimal = "00";

            if (count($arnum) > 1) {
                $num = intval($arnum[0]);
                $decimal = intval($arnum[1]) < 10 ? "0" . intval($arnum[1]) : intval($arnum[1]);
            } else {
                $num = intval($arnum[0]);
                $decimal = "00";
            }

            if ($num == 0)
                return "CERO CON " . $decimal . "/100 " . strtoupper($moneda);

            if ($num == 1)
                return self::Millones($num) . " CON " . $decimal . "/100 " . strtoupper($moneda);
            else
                return self::Millones($num) . " CON " . $decimal . "/100 " . strtoupper($moneda);
        }
    }
}
