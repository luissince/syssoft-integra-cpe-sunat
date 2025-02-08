<?php

namespace App\Helpers;

use App\Models\Certificado;
use App\Models\Empresa;
use App\Models\GuiaRemision\GuiaRemision;
use App\Models\Venta;
use App\Src\SoapResult;
use App\Src\Sunat;
use DateTime;
use DOMDocument;
use Illuminate\Support\Facades\Storage;

class SunatHelper
{

    public function __construct() {}

    public static function sendBill(string $fileName, DOMDocument $xml, Empresa $empresa, Certificado $certificado)
    {
        $fileNameXml  = $fileName . '.xml';
        // files/sunat/10764233889/
        $path = 'files/sunat/' . $empresa->documento . '/';

        Storage::put($path . $fileNameXml, $xml->saveXML());
        Sunat::signDocumentXml($path, $fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path($path . $fileName . '.zip'),
            Storage::path($path . $fileNameXml),
            $fileNameXml
        );

        if ($empresa->tipoEnvio === false) {
            $wdsl = Storage::path('wsdl/desarrollo/billService.wsdl');
        } else {
            $wdsl = Storage::path('wsdl/produccion/billService.wsdl');
        }

        $soapResult = new SoapResult($wdsl, $fileName);
        $soapResult->sendBill(Sunat::xmlSendBill(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $fileName . '.zip',
            Sunat::generateBase64File(Storage::get($path . $fileName . '.zip'))
        ), $path);

        if ($soapResult->isSuccess()) {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getDescription(),
            ];

            if ($soapResult->isAccepted()) {
                $updateData["codigoHash"] = $soapResult->getHashCode();
                $updateData["xmlGenerado"] = Sunat::getXmlSign();
            }

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getDescription(),
                "update" => $updateData
            ];

            return response()->json($responseData);
        } else {
            if ($soapResult->getError()) {
                return response()->json(["message" => $soapResult->getDescription()], 500);
            } else {
                $updateData = [
                    "xmlSunat" => $soapResult->getCode(),
                    "xmlDescripcion" => $soapResult->getDescription(),
                ];

                if ($soapResult->getCode() == "1033") {
                    $updateData["xmlSunat"] = "0";
                }

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                    "update" => $updateData
                ];

                return response()->json($responseData);
            }
        }
    }

    public static function sendSumary(string $fileName, DOMDocument $xml, Empresa $empresa, Certificado $certificado, int $correlativo, DateTime $currentDate)
    {
        $fileNameXml  = $fileName . '.xml';
        // files/sunat/10764233889/
        $path = 'files/sunat/' . $empresa->documento . '/';

        Storage::put($path . $fileNameXml, $xml->saveXML());
        Sunat::signDocumentXml($path, $fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path($path . $fileName . '.zip'),
            Storage::path($path . $fileNameXml),
            $fileNameXml
        );

        if ($empresa->tipoEnvio === false) {
            $wdsl = Storage::path('wsdl/desarrollo/billService.wsdl');
        } else {
            $wdsl = Storage::path('wsdl/produccion/billService.wsdl');
        }

        $soapResult = new SoapResult($wdsl, $fileName);
        $soapResult->sendSumary(Sunat::xmlSendSummary(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $fileName . '.zip',
            Sunat::generateBase64File(Storage::get($path . $fileName . '.zip'))
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

                $responseData = [
                    "state" => $soapResult->isSuccess(),
                    "accept" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                    "update" => $updateData
                ];
            } else {
                $responseData = [
                    "state" => $soapResult->isSuccess(),
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getDescription(),
                    "update" => $updateData
                ];
            }
        } else {
            return response()->json(["message" => $soapResult->getDescription(), "update" => $updateData,], 500);
        }

        return response()->json($responseData);
    }

    public static function getStatus(Venta $venta, Empresa $empresa, string $fileName)
    {
        // files/sunat/10764233889/
        $path = 'files/sunat/' . $empresa->documento . '/';

        if ($empresa->tipoEnvio === false) {
            $wdsl = Storage::path('wsdl/desarrollo/billService.wsdl');
        } else {
            $wdsl = Storage::path('wsdl/produccion/billService.wsdl');
        }

        $soapResult = new SoapResult($wdsl, $fileName);
        $soapResult->setTicket($venta->ticketConsultaSunat);
        $soapResult->sendGetStatusTicket(Sunat::xmlGetStatusTicket(
            $empresa->documento,
            $empresa->usuarioSolSunat,
            $empresa->claveSolSunat,
            $soapResult->getTicket()
        ), $path);

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

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getDescription(),
                "update" => $updateData
            ];
        } else {
            return response()->json(["message" => $soapResult->getDescription()], 500);
        }

        return response()->json($responseData);
    }

    public static function sendDespatchAdvice(string $fileName, DOMDocument $xml, GuiaRemision $guiaRemision, Empresa $empresa, Certificado $certificado)
    {
        $fileNameXml  = $fileName . '.xml';
        // files/sunat/10764233889/
        $path = 'files/sunat/' . $empresa->documento . '/';

        Storage::put($path . $fileNameXml, $xml->saveXML());
        Sunat::signDocumentXml($path, $fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path($path  . $fileName . '.zip'),
            Storage::path($path  . $fileNameXml),
            $fileNameXml
        );

        $soapResult = new SoapResult('', $fileName);
        $soapResult->setConfigGuiaRemision(Storage::path($path  . $fileName . '.zip'));
        $soapResult->sendGuiaRemision(
            [
                "NumeroDocumento" => $empresa->documento,
                "UsuarioSol" => $empresa->usuarioSolSunat,
                "ClaveSol" => $empresa->claveSolSunat,
                "IdApiSunat" => $empresa->idApiSunat,
                "ClaveApiSunat" => $empresa->claveApiSunat,
            ],
            [
                "numRucEmisor" => $empresa->documento,
                "codCpe" => $guiaRemision->codigo,
                "numSerie" => $guiaRemision->serie,
                "numCpe" => $guiaRemision->numeracion,
            ],
            $empresa->tipoEnvio,
            $path
        );

        if ($soapResult->isSuccess()) {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getMessage(),
            ];
            if ($soapResult->isAccepted()) {
                $updateData += [
                    "xmlGenerado" => Sunat::getXmlSign(),
                    "numeroTicketSunat" => $soapResult->getTicket()
                ];
            }

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getMessage(),
                "update" => $updateData
            ];
        } else {
            if ($soapResult->getCode() == "1033") {
                $updateData = [
                    "xmlSunat" => "0",
                    "xmlDescripcion" => $soapResult->getMessage(),
                ];

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getMessage(),
                    "update" => $updateData
                ];
            } else {
                return response()->json([
                    "message" => $soapResult->getMessage()
                ], 500);
            }
        }

        return response()->json($responseData);
    }

    public static function getStatusDespatchAdvice(string $fileName, GuiaRemision $guiaRemision, Empresa $empresa, string $ticket)
    {
        $path = 'files/sunat/' . $empresa->documento . '/';

        $soapResult = new SoapResult('', $fileName);
        $soapResult->setTicket($ticket);
        $soapResult->sendGuiaRemision(
            [
                "NumeroDocumento" => $empresa->documento,
                "UsuarioSol" => $empresa->usuarioSolSunat,
                "ClaveSol" => $empresa->claveSolSunat,
                "IdApiSunat" => $empresa->idApiSunat,
                "ClaveApiSunat" => $empresa->claveApiSunat,
            ],
            [
                "numRucEmisor" => $empresa->documento,
                "codCpe" => $guiaRemision->codigo,
                "numSerie" => $guiaRemision->serie,
                "numCpe" => $guiaRemision->numeracion,
            ],
            $empresa->tipoEnvio,
            $path
        );

        if ($soapResult->isSuccess()) {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getMessage(),
            ];

            if ($soapResult->isAccepted()) {
                $updateData["codigoHash"] = $soapResult->getHashCode();
            }

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getMessage(),
                "update" => $updateData
            ];

            return response()->json($responseData);
        } else {
            if ($soapResult->getCode() == "1032") {
                $updateData = [
                    "xmlSunat" => $soapResult->getCode(),
                    "xmlDescripcion" => $soapResult->getMessage(),
                ];

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getMessage(),
                    "update" => $updateData
                ];
            } else if ($soapResult->getError()) {
                return response()->json(["message" => $soapResult->getMessage()], 500);
            } else {
                $updateData = [
                    "xmlSunat" => $soapResult->getCode(),
                    "xmlDescripcion" => $soapResult->getMessage(),
                ];

                if ($soapResult->getCode() == "1033") {
                    $updateData["xmlSunat"] = "0";
                }

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getMessage(),
                    "update" => $updateData
                ];
            }
            return response()->json($responseData);
        }
    }
}
