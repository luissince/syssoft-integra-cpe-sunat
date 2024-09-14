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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SunatHelper
{

    public function __construct()
    {
    }

    public static function sendBillToSunat(string $fileName, DOMDocument $xml, string $idVenta, $empresa)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        // Storage::disk('files')->put($path, $xml->saveXML());
        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentSunat($fileNameXml);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
            $fileNameXml
        );

        if (DB::table('empresa')->where('tipoEnvio', 1)->get()->isEmpty()) {
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
            Sunat::generateBase64File(Storage::get('files/sunat/' . $fileName . '.zip'))
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

    public static function sendBill(string $fileName, DOMDocument $xml, Empresa $empresa, Certificado $certificado)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentXml($fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
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
            Sunat::generateBase64File(Storage::get('files/sunat/' . $fileName . '.zip'))
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

    public static function sendSumaryToSunat(string $fileName, DOMDocument $xml, string $idVenta, $empresa, int $correlativo, DateTime $currentDate)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        // Storage::disk('files')->put($path, $xml->saveXML());
        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentSunat($fileNameXml);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
            $fileNameXml
        );

        if (DB::table('empresa')->where('tipoEnvio', 1)->get()->isEmpty()) {
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
            Sunat::generateBase64File(Storage::get('files/sunat/' . $fileName . '.zip'))
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

    public static function sendSumary(string $fileName, DOMDocument $xml, Empresa $empresa, Certificado $certificado, int $correlativo, DateTime $currentDate)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentXml($fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
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
            Sunat::generateBase64File(Storage::get('files/sunat/' . $fileName . '.zip'))
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

    public static function getStatusToSunat($idVenta, $venta, $empresa, $fileName)
    {
        if (DB::table('empresa')->where('tipoEnvio', 1)->get()->isEmpty()) {
            $wdsl = Storage::path('wsdl/desarrollo/billService.wsdl');
        } else {
            $wdsl = Storage::path('wsdl/produccion/billService.wsdl');
        }

        $soapResult = new SoapResult($wdsl, $fileName);
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

    public static function getStatus(Venta $venta, Empresa $empresa, string $fileName)
    {
        if ($empresa->tipoEnvio === false) {
            $wdsl = Storage::path('wsdl/desarrollo/billService.wsdl');
        } else {
            $wdsl = Storage::path('wsdl/produccion/billService.wsdl');
        }

        $soapResult = new SoapResult($wdsl, $fileName);
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

    public static function sendDespatchAdviceSunat(string $fileName, DOMDocument $xml, $idGuiaRemision, $guiaRemision, $empresa)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        // Storage::disk('files')->put($path, $xml->saveXML());
        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentSunat($fileNameXml);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
            $fileNameXml
        );

        $soapResult = new SoapResult('', $fileName);
        $soapResult->setConfigGuiaRemision(Storage::path("files/sunat/" . $fileName . '.zip'));
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
            false
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

            DB::table("guiaRemision")
                ->where('idGuiaRemision', $idGuiaRemision)
                ->update($updateData);

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getMessage()
            ];
        } else {
            if ($soapResult->getCode() == "1033") {
                $updateData = [
                    "xmlSunat" => "0",
                    "xmlDescripcion" => $soapResult->getMessage(),
                ];

                DB::table("guiaRemision")
                    ->where('idGuiaRemision', $idGuiaRemision)
                    ->update($updateData);

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getMessage()
                ];
            } else {
                return response()->json([
                    "message" => $soapResult->getMessage()
                ], 500);
            }
        }

        return response()->json($responseData);
    }

    public static function sendDespatchAdvice(string $fileName, DOMDocument $xml, GuiaRemision $guiaRemision, Empresa $empresa, Certificado $certificado)
    {
        $fileNameXml  = $fileName . '.xml';
        $path = 'sunat/' . $fileNameXml;

        Storage::put('files/' . $path, $xml->saveXML());
        Sunat::signDocumentXml($fileNameXml, $certificado);

        Sunat::createZip(
            Storage::path("files/sunat/" . $fileName . '.zip'),
            Storage::path("files/sunat/" . $fileNameXml),
            $fileNameXml
        );

        $soapResult = new SoapResult('', $fileName);
        $soapResult->setConfigGuiaRemision(Storage::path("files/sunat/" . $fileName . '.zip'));
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
            $empresa->tipoEnvio
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

    public static function getStatusDespatchAdviceToSunat(string $fileName, $idGuiaRemision, $guiaRemision, $empresa, $ticket)
    {
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
            false
        );

        if ($soapResult->isSuccess()) {
            $updateData = [
                "xmlSunat" => $soapResult->getCode(),
                "xmlDescripcion" => $soapResult->getMessage(),
            ];

            if ($soapResult->isAccepted()) {
                $updateData["codigoHash"] = $soapResult->getHashCode();
            }

            DB::table("guiaRemision")
                ->where('idGuiaRemision', $idGuiaRemision)
                ->update($updateData);

            $responseData = [
                "state" => $soapResult->isSuccess(),
                "accept" => $soapResult->isAccepted(),
                "code" => $soapResult->getCode(),
                "description" => $soapResult->getMessage()
            ];
        } else {
            if ($soapResult->getCode() == "1033") {
                $updateData = [
                    "xmlSunat" => "0",
                    "xmlDescripcion" => $soapResult->getMessage(),
                ];

                DB::table("guiaRemision")
                    ->where('idGuiaRemision', $idGuiaRemision)
                    ->update($updateData);

                $responseData = [
                    "state" => false,
                    "code" => $soapResult->getCode(),
                    "description" => $soapResult->getMessage()
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
            $empresa->tipoEnvio
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
            if ($soapResult->getError()) {
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
