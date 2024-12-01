<?php

namespace App\Src;

use App\Models\Certificado;
use App\Models\Consulta;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMDocument;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class Sunat
{

    private static $filename = "";

    public function __construct()
    {
    }

    public static function signDocumentXml(string $path, string $filename, Certificado $certificado)
    {
        $doc = new DOMDocument();
        $doc->load(Storage::path($path . $filename));

        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA1,
            array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'),
            array('force_uri' => true)
        );

        $privateKeyPath = $certificado->privateKey;
        $publicKeyPath = $certificado->publicKey;

        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, array('type' => 'private'));
        $objKey->loadKey($privateKeyPath, false);

        $objDSig->sign($objKey);
        $objDSig->add509Cert($publicKeyPath, true, false);
        $objDSig->appendSignature($doc->getElementsByTagName('ExtensionContent')->item(0));

        $xmlOutputPath = $path . $filename;
        Storage::put($xmlOutputPath, $doc->saveXML());

        self::$filename = $xmlOutputPath;
    }

    public static function getXmlSign()
    {
        $xml = Storage::get(self::$filename);
        $DOM = new DOMDocument('1.0', 'ISO-8859-1');
        $DOM->preserveWhiteSpace = FALSE;
        $DOM->loadXML($xml);
        return $DOM->saveXML();
    }

    public static function createZip($fileoutput, $fileinput, $filename)
    {
        $zip = new ZipArchive();
        $zip->open($fileoutput, ZipArchive::CREATE);
        $zip->addFile($fileinput, $filename);
        $zip->close();
    }

    public static function extractZip($fileintput, $directoryextract)
    {
        $zip = new ZipArchive();
        if ($zip->open($fileintput) === TRUE) {
            $result = array();
            for ($i = 0; $i < $zip->numFiles; $i++) {
                array_push($result, $zip->getNameIndex($i));
            }
            $zip->extractTo($directoryextract);
            $zip->close();
            return $result;
        } else {
            return false;
        }
    }

    public static function generateHashFile(string $algoritmo = 'sha256', string $path)
    {
        return hash_file($algoritmo, $path);
    }

    public static function generateBase64File(string $filename)
    {
        // return base64_encode(file_get_contents($filename));
        return base64_encode($filename);
    }

    public static function xmlSendBill($NumeroDocumento, $UsuarioSol, $ClaveSol, $filename, $fileencode)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken Id="ABC-123">
                    <wsse:Username>' . $NumeroDocumento . '' . $UsuarioSol . '</wsse:Username>
                    <wsse:Password>' . $ClaveSol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendBill>
                <fileName>' . $filename . '</fileName>
                <contentFile>' .  $fileencode  . '</contentFile>
            </ser:sendBill>
        </soapenv:Body>
        </soapenv:Envelope>';
    }


    public static function xmlSendSummary($NumeroDocumento, $UsuarioSol, $ClaveSol, $filename, $fileencode)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken Id="ABC-123">
                    <wsse:Username>' . $NumeroDocumento . '' . $UsuarioSol . '</wsse:Username>
                    <wsse:Password>' . $ClaveSol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:sendSummary>
                <fileName>' . $filename . '</fileName>
                <contentFile>' .  $fileencode  . '</contentFile>
            </ser:sendSummary>
        </soapenv:Body>
        </soapenv:Envelope>';
    }

    public static function xmlGetStatusTicket($NumeroDocumento, $UsuarioSol, $ClaveSol, $ticket)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken Id="ABC-123">
                    <wsse:Username>' . $NumeroDocumento . '' . $UsuarioSol . '</wsse:Username>
                    <wsse:Password>' . $ClaveSol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:getStatus>
                <ticket>' . $ticket . '</ticket>
            </ser:getStatus>
        </soapenv:Body>
        </soapenv:Envelope>';
    }

    public static function xmlGetStatus(Consulta $consulta)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $consulta->ruc . '' . $consulta->usuarioSol . '</wsse:Username>
                    <wsse:Password>' . $consulta->claveSol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:getStatus>
                <rucComprobante>' . $consulta->ruc . '</rucComprobante>
                <tipoComprobante>' . $consulta->tipoComprobante . '</tipoComprobante>
                <serieComprobante>' . $consulta->serie . '</serieComprobante>
                <numeroComprobante>' . $consulta->numeracion . '</numeroComprobante>
            </ser:getStatus>
        </soapenv:Body>
        </soapenv:Envelope>';
    }

    public static function xmlGetCdr(Consulta $consulta)
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ser="http://service.sunat.gob.pe" xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
        <soapenv:Header>
            <wsse:Security>
                <wsse:UsernameToken>
                    <wsse:Username>' . $consulta->ruc . '' . $consulta->usuarioSol . '</wsse:Username>
                    <wsse:Password>' . $consulta->claveSol . '</wsse:Password>
                </wsse:UsernameToken>
            </wsse:Security>
        </soapenv:Header>
        <soapenv:Body>
            <ser:getStatusCdr>
                <rucComprobante>' .  $consulta->ruc . '</rucComprobante>
                <tipoComprobante>' . $consulta->tipoComprobante . '</tipoComprobante>
                <serieComprobante>' .  $consulta->serie  . '</serieComprobante>
                <numeroComprobante>' .  $consulta->numeracion . '</numeroComprobante>
            </ser:getStatusCdr>
        </soapenv:Body>
        </soapenv:Envelope>';
    }
}
