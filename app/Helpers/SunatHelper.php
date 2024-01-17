<?php

namespace App\Helpers;

use App\Src\SoapResult;
use App\Src\Sunat;
use DateTime;
use DOMDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SunatHelper
{

    public function __construct()
    {
    }

    public static function sendBillToSunat(string $fileName, DOMDocument $xml, $idVenta, $empresa)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'files/' . $fileNameXml;

        Storage::disk('public')->put($path, $xml->saveXML());
        Sunat::signDocument($fileNameXml);

        Sunat::createZip(
            Storage::path("public/files/" . $fileName . '.zip'),
            Storage::path("public/files/" . $fileNameXml),
            $fileNameXml
        );

        $soapResult = new SoapResult(Storage::path('wsdl/billService.wsdl'), $fileName);
        $soapResult->sendBill(Sunat::xmlSendBill(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $fileName . '.zip',
            Sunat::generateBase64File(Storage::get('public/files/' . $fileName . '.zip'))
        ));

        if ($soapResult->isSuccess()) {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getDescription(),
            ];

            if ($soapResult->isAccepted()) {
                $updateData["codigoHash"] = $soapResult->getHashCode();
                $updateData["xmlGenerado"] = Sunat::getXmlSign();
            }

            DB::table("venta")
                ->where('idVenta', $idVenta)
                ->update($updateData);

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getDescription(),
            ];
        } else {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getDescription(),
            ];

            if ($soapResult->getCode() == "1033") {
                $updateData["xmlSunat"] = "0";
            }

            DB::table("venta")
                ->where('idVenta', $idVenta)
                ->update($updateData);

            if ($soapResult->getCode() == "1033") {
                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                ];
            } else {
                return response()->json(["message" => $soapResult->getDescription()], 500);
            }
        }

        return response()->json($responseData);
    }

    public static function sendSumaryToSunat(string $fileName, DOMDocument $xml, string $idVenta, $empresa, int $correlativo, DateTime $currentDate)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'files/' . $fileNameXml;

        Storage::disk('public')->put($path, $xml->saveXML());
        Sunat::signDocument($fileNameXml);

        Sunat::createZip(
            Storage::path("public/files/" . $fileName . '.zip'),
            Storage::path("public/files/" . $fileNameXml),
            $fileNameXml
        );

        $soapResult = new SoapResult(Storage::path('wsdl/billService.wsdl'), $fileName);
        $soapResult->sendSumary(Sunat::xmlSendSummary(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $fileName . '.zip',
            Sunat::generateBase64File(Storage::get('public/files/' . $fileName . '.zip'))
        ));

        $updateData = [
            "xmlSunat" => $soapResult->getCode(),
            "xmlDescripcion" => $soapResult->getDescription(),
            "correlativo" => $correlativo,
            "fechaCorrelativo" => $currentDate->format('Y-m-d'),
        ];

        if ($soapResult->isSuccess()) {
            if ($soapResult->isAccepted()) {
                $updateData["ticketConsultaSunat"] = $soapResult->getTicket();

                DB::table("venta")
                    ->where('idVenta', $idVenta)
                    ->update($updateData);

                $responseData = [
                    "state" => $soapResult->isSuccess(),
                    "accept" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                ];
            } else {
                DB::table("venta")
                    ->where('idVenta', $idVenta)
                    ->update($updateData);

                $responseData = [
                    "state" => $soapResult->isSuccess(),
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                ];
            }
        } else {
            DB::table("venta")
                ->where('idVenta', $idVenta)
                ->update($updateData);

            return response()->json(["message" => $soapResult->getDescription()], 500);
        }

        return response()->json($responseData);
    }

    public static function getStatusToSunat($idVenta, $venta, $empresa)
    {
        $date = new DateTime($venta->fechaCorrelativo);
        $fileName = $empresa->documento . '-RC-' . $date->format('Ymd') . '-' . $venta->correlativo;

        $soapResult = new SoapResult(Storage::path('wsdl/billService.wsdl'), $fileName);
        $soapResult->setTicket($venta->ticketConsultaSunat);
        $soapResult->sendGetStatus(Sunat::xmlGetStatus(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $soapResult->getTicket()
        ));

        $updateData = [
            "xmlSunat" => "",
            "xmlDescripcion" => $soapResult->getDescription(),
        ];

        if ($soapResult->isSuccess()) {
            if (!$soapResult->isAccepted()) {
                if ($soapResult->getCode() == "2987"  || $soapResult->getCode() == "1032") {
                    $updateData["xmlSunat"] = "0";
                }
            }

            DB::table("venta")
                ->where('idVenta', $idVenta)
                ->update($updateData);

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getDescription()
            ];
        } else {
            return response()->json(["message" => $soapResult->getDescription()], 500);
        }

        return response()->json($responseData);
    }
}
