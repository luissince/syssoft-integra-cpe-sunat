<?php

namespace App\Src;

use App\Exceptions\ResponseCurlException;
use Exception;
use SoapFault;
use DOMDocument;
use App\Src\Sunat;
use App\Src\SoapBuilder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;


class SoapResult
{
    private string $wsdlURL;

    private string $file;

    private string $filename;

    private bool $success = false;

    private bool $accepted = false;

    private bool $error = false;

    private string $hashCode = "";

    private string $description = "";

    private string $ticket = "";

    private string $message = "";

    private string $code = "";

    private string $filebase64 = "";

    private string $hashZip = "";

    public function __construct($wsdlURL, $filename)
    {
        $this->wsdlURL = $wsdlURL;
        $this->filename = $filename;
    }

    public function sendBill($xmlSend, string $path)
    {
        try {
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("sendBill");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;
            $DOM->loadXML($result);

            $DocXML = $DOM->getElementsByTagName('applicationResponse');
            $response = "";
            foreach ($DocXML as $Nodo) {
                $response = $Nodo->nodeValue;
            }

            if ($response == "" || $response == null) {
                throw new Exception("No se pudo obtener el contenido del nodo applicationResponse.");
            }

            $cdr = base64_decode($response);
            // $archivo = fopen('../files/R-' . $this->filename . '.zip', 'w+');
            $archivo = fopen(Storage::path($path . "R-" . $this->filename . '.zip'), 'w+');
            fputs($archivo, $cdr);
            fclose($archivo);
            // chmod('../files/R-' . $this->filename . '.zip', 0777);

            // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
            $isExtract = Sunat::extractZip(
                Storage::path($path . "R-" . $this->filename . '.zip'),
                Storage::path($path)
            );

            if (!$isExtract) {
                throw new Exception("No se pudo extraer el contenido del archivo zip.");
            }

            // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
            $xml = Storage::get($path . 'R-' . $this->filename . '.xml');
            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;
            $DOM->loadXML($xml);

            $DocXML = $DOM->getElementsByTagName('ResponseCode');
            $code = "";
            foreach ($DocXML as $Nodo) {
                $code = $Nodo->nodeValue;
            }

            $DocXML = $DOM->getElementsByTagName('Description');
            $description = "";
            foreach ($DocXML as $Nodo) {
                $description = $Nodo->nodeValue;
            }

            $DocXML = $DOM->getElementsByTagName('DigestValue');
            $hashCode = "";
            foreach ($DocXML as $Nodo) {
                $hashCode = $Nodo->nodeValue;
            }

            // Storage::path("public/files/R-" . $this->filename . '.zip');
            // if (file_exists('../files/' . $this->filename . '.zip')) {
            //     unlink('../files/' . $this->filename . '.zip');
            // }
            // if (file_exists('../files/R-' . $this->filename . '.zip')) {
            //     unlink('../files/R-' . $this->filename . '.zip');
            // }

            if (file_exists(Storage::path($path  . $this->filename . '.zip'))) {
                unlink(Storage::path($path  . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path($path . "R-" . $this->filename . '.zip'))) {
                unlink(Storage::path($path . "R-" . $this->filename . '.zip'));
            }

            if ($code == "0") {
                $this->setAccepted(true);
            } else {
                $this->setAccepted(false);
            }
            $this->setCode($code);
            $this->setDescription($description);
            $this->setHashCode($hashCode);
            $this->setSuccess(true);
        } catch (SoapFault $ex) {
            if (file_exists(Storage::path($path . $this->filename . '.xml'))) {
                unlink(Storage::path($path . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path($path . $this->filename . '.zip'))) {
                unlink(Storage::path($path . $this->filename . '.zip'));
            }

            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setDescription($message);
        } catch (Exception $ex) {
            if (file_exists(Storage::path($path . $this->filename . '.xml'))) {
                unlink(Storage::path($path . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path($path . $this->filename . '.zip'))) {
                unlink(Storage::path($path . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path($path . 'R-' . $this->filename . '.zip'))) {
                unlink(Storage::path($path . 'R-' . $this->filename . '.zip'));
            }

            $this->setSuccess(false);
            $this->setError(true);
            $this->setCode("-1");
            $this->setDescription($ex->getMessage());
        }
    }

    public function sendSumary($xmlSend)
    {
        try {
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("sendSummary");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;
            $DOM->loadXML($result);

            $DocXML = $DOM->getElementsByTagName('ticket');
            $ticket = "";
            foreach ($DocXML as $Nodo) {
                $ticket = $Nodo->nodeValue;
            }

            if ($ticket != "") {
                $this->setAccepted(true);
                $this->setSuccess(true);
                $this->setTicket($ticket);
                $this->setDescription("Resumen diario o Comunicación de baja aceptada, consulte el estado en un par de minutos.");
            } else {
                $this->setAccepted(false);
                $this->setSuccess(true);
                $this->setTicket($ticket);
                $this->setDescription("No se pudo obtener el número de ticket, intente en un par de minutos.");
            }
        } catch (SoapFault $ex) {
            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setDescription($message);
        } catch (Exception $ex) {
            $this->setSuccess(false);
            $this->setCode('-1');
            $this->setDescription($ex->getMessage());
        }
    }

    public function sendGetStatusTicket($xmlSend, string $path)
    {
        try {
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("getStatus");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;
            $DOM->loadXML($result);

            $DocXML = $DOM->getElementsByTagName('statusCode');
            $statusCode = "";
            foreach ($DocXML as $Nodo) {
                $statusCode = $Nodo->nodeValue;
            }

            $DocXML = $DOM->getElementsByTagName('content');
            $content = "";
            foreach ($DocXML as $Nodo) {
                $content = $Nodo->nodeValue;
            }

            if ($statusCode == "0") {
                $cdr = base64_decode($content);
                // $archivo = fopen('../files/R-' . $this->filename . '.zip', 'w+');
                $archivo = fopen(Storage::path($path . "R-" . $this->filename . '.zip'), 'w+');
                fputs($archivo, $cdr);
                fclose($archivo);
                // chmod('../files/R-' . $this->filename . '.zip', 0777);

                // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
                $isExtract = Sunat::extractZip(
                    Storage::path($path . "R-" . $this->filename . '.zip'),
                    Storage::path($path)
                );
                if (!$isExtract) {
                    throw new Exception("No se pudo extraer el contenido del archivo zip.");
                }

                // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
                $xml = Storage::get($path . 'R-' . $this->filename . '.xml');
                $DOM = new DOMDocument('1.0', 'utf-8');
                $DOM->preserveWhiteSpace = FALSE;
                $DOM->loadXML($xml);

                $DocXML = $DOM->getElementsByTagName('ResponseCode');
                $code = "";
                foreach ($DocXML as $Nodo) {
                    $code = $Nodo->nodeValue;
                }

                $DocXML = $DOM->getElementsByTagName('Description');
                $description = "";
                foreach ($DocXML as $Nodo) {
                    $description = $Nodo->nodeValue;
                }

                $this->setCode($code);
                $this->setDescription($description);
                $this->setSuccess(true);
                $this->setAccepted(true);
            } else if ($statusCode == "98") {

                $this->setCode($statusCode);
                $this->setDescription("El proceso de envío, consulte en un par de minutos nuevamente.");
                $this->setSuccess(true);
                $this->setAccepted(false);
            } else if ($statusCode == "99") {

                $cdr = base64_decode($content);
                // $archivo = fopen('../files/R-' . $this->filename . '.zip', 'w+');
                $archivo = fopen(Storage::path($path . "R-" . $this->filename . '.zip'), 'w+');
                fputs($archivo, $cdr);
                fclose($archivo);
                // chmod('../files/R-' . $this->filename . '.zip', 0777);

                // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
                $isExtract = Sunat::extractZip(
                    Storage::path($path . "R-" . $this->filename . '.zip'),
                    Storage::path($path)
                );
                if (!$isExtract) {
                    throw new Exception("No se pudo extraer el contenido del archivo zip.");
                }

                // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
                $xml = Storage::get($path . 'R-' . $this->filename . '.xml');
                $DOM = new DOMDocument('1.0', 'utf-8');
                $DOM->preserveWhiteSpace = FALSE;
                $DOM->loadXML($xml);

                $DocXML = $DOM->getElementsByTagName('ResponseCode');
                $code = "";
                foreach ($DocXML as $Nodo) {
                    $code = $Nodo->nodeValue;
                }

                $DocXML = $DOM->getElementsByTagName('Description');
                $description = "";
                foreach ($DocXML as $Nodo) {
                    $description = $Nodo->nodeValue;
                }

                $this->setCode($code);
                $this->setDescription($description);
                $this->setSuccess(true);
                $this->setAccepted(false);
            } else {
                $this->setCode($statusCode);
                $this->setDescription($content);
                $this->setSuccess(true);
                $this->setAccepted(false);
            }
        } catch (SoapFault $ex) {
            if (file_exists(Storage::path($path . $this->filename . '.xml'))) {
                unlink(Storage::path($path .  $this->filename . '.xml'));
            }
            if (file_exists(Storage::path($path . $this->filename . '.zip'))) {
                unlink(Storage::path($path . $this->filename . '.zip'));
            }
            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setDescription($message);
        } catch (Exception $ex) {
            // if (file_exists('../files/' . $this->filename . '.xml')) {
            //     unlink('../files/' . $this->filename . '.xml');
            // }
            // if (file_exists('../files/' . $this->filename . '.zip')) {
            //     unlink('../files/' . $this->filename . '.zip');
            // }
            // if (file_exists('../files/R-' . $this->filename . '.zip')) {
            //     unlink('../files/R-' . $this->filename . '.zip');
            // }
            if (file_exists(Storage::path($path . $this->filename . '.xml'))) {
                unlink(Storage::path($path . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path($path . $this->filename . '.zip'))) {
                unlink(Storage::path($path . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path($path . "R-" . $this->filename . '.zip'))) {
                unlink(Storage::path($path . "R-" . $this->filename . '.zip'));
            }
            $this->setSuccess(false);
            $this->setCode("-1");
            $this->setDescription($ex->getMessage());
        }
    }

    public function sendGetStatusValid($xmlSend)
    {
        try {
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("getStatus");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;

            if ($DOM->loadXML($result)) {
                Storage::put('tmp/status.xml', $DOM->saveXML());
                $DocXML = $DOM->getElementsByTagName('statusCode');
                $code = "";
                foreach ($DocXML as $Nodo) {
                    $code = $Nodo->nodeValue;
                }

                $DocXML = $DOM->getElementsByTagName('statusMessage');
                $message = "";
                foreach ($DocXML as $Nodo) {
                    $message = $Nodo->nodeValue;
                }

                if ($code == "0001") {
                    $this->setAccepted(true);
                } else {
                    $this->setAccepted(false);
                }
                $this->setCode($code);
                $this->setMessage($message);
                $this->setSuccess(true);
            } else {
                throw new Exception("No se pudo obtener el xml de respuesta.");
            }
        } catch (SoapFault $ex) {
            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setMessage($message);
        } catch (Exception $ex) {
            $this->setSuccess(false);
            $this->setCode("-1");
            $this->setMessage($ex->getMessage());
        }
    }

    public function sendGetCdrValid($xmlSend)
    {
        try {
            $path = 'files/cdr/';
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("getStatusCdr");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;

            if ($DOM->loadXML($result)) {

                Storage::put($path . $this->filename . '.xml', $DOM->saveXML());

                $DocXML = $DOM->getElementsByTagName('ResponseCode');
                $code = "";
                foreach ($DocXML as $Nodo) {
                    $code = $Nodo->nodeValue;
                }

                $DocXML = $DOM->getElementsByTagName('statusMessage');
                $message = "";
                foreach ($DocXML as $Nodo) {
                    $message = $Nodo->nodeValue;
                }

                $DocXML = $DOM->getElementsByTagName('content');
                $content = "";
                foreach ($DocXML as $Nodo) {
                    $content = $Nodo->nodeValue;
                }

                if ($content != "") {
                    $cdr = base64_decode($content);

                    Storage::put($path . $this->filename . '.zip', $cdr);

                    Sunat::extractZip(Storage::path($path . $this->filename . '.zip'),  Storage::path($path));

                    $xml = Storage::get($path . 'R-' . $this->filename . '.xml');
                    $DOM = new DOMDocument('1.0', 'utf-8');
                    $DOM->preserveWhiteSpace = FALSE;
                    $DOM->loadXML($xml);

                    $DocXML = $DOM->getElementsByTagName('ResponseCode');
                    $code = "";
                    foreach ($DocXML as $Nodo) {
                        $code = $Nodo->nodeValue;
                    }

                    $DocXML = $DOM->getElementsByTagName('Description');
                    $description = "";
                    foreach ($DocXML as $Nodo) {
                        $description = $Nodo->nodeValue;
                    }

                    $this->setAccepted(true);
                    $this->setCode($code);
                    $this->setMessage($message);
                    $this->setDescription($description);
                    $this->setSuccess(true);
                    $this->setFile($DOM->saveXML());
                } else {
                    $this->setAccepted(false);
                    $this->setCode($code);
                    $this->setMessage($message);
                    $this->setSuccess(true);
                }
            } else {
                throw new Exception("No se pudo obtener el xml de respuesta.");
            }
        } catch (SoapFault $ex) {
            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setMessage($message);
        } catch (Exception $ex) {
            $this->setSuccess(false);
            $this->setCode("-1");
            $this->setMessage($ex->getMessage());
        }
    }

    public function sendDespatchAdvice(array $credenciales, array $uri, bool $tipoEnvio, string $path)
    {
        try {
            $accessToken = $this->getTokenApiSunat($credenciales, $tipoEnvio);

            if ($this->ticket) {
                $this->getStatusDespatchArchive($accessToken, $tipoEnvio, $path);
            } else {
                $this->sendDocumentDespatchAdvice($accessToken, implode("-", $uri), $tipoEnvio);
            }
        } catch (ResponseCurlException $ex) {
            $this->setSuccess(false);
            $this->setCode($ex->getCode());
            $this->setMessage($ex->getMessage());
        } catch (Exception $ex) {
            $this->setSuccess(false);
            $this->setError(true);
            $this->setCode($ex->getCode());
            $this->setMessage($ex->getMessage());
        } finally {
            // if (file_exists(Storage::path("public/files/" . $this->filename . '.xml'))) {
            //     unlink(Storage::path("public/files/" . $this->filename . '.xml'));
            // }
            // if (file_exists(Storage::path("public/files/" . $this->filename . '.zip'))) {
            //     unlink(Storage::path("public/files/" . $this->filename . '.zip'));
            // }
        }
    }

    private function getTokenApiSunat(array $credenciales, bool $tipoEnvio)
    {

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */
        $url = $tipoEnvio === false
            ? 'https://gre-test.nubefact.com/v1/clientessol/' . $credenciales["IdApiSunat"] . '/oauth2/token'
            : 'https://api-seguridad.sunat.gob.pe/v1/clientessol/' . $credenciales["IdApiSunat"] . '/oauth2/token';


        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */
        $response = Http::asForm()
            ->post($url, [
                'grant_type' => 'password',
                'scope' => 'https://api-cpe.sunat.gob.pe',
                'client_id' => trim($credenciales["IdApiSunat"]),
                'client_secret' => trim($credenciales["ClaveApiSunat"]),
                'username' => trim($credenciales["NumeroDocumento"]) . trim($credenciales["UsuarioSol"]),
                'password' => $credenciales["ClaveSol"],
            ]);

        /*
        |--------------------------------------------------------------------------
        | BODY VACÍO
        |--------------------------------------------------------------------------
        */
        if (!$response->body()) {

            throw new Exception(
                'Respuesta vacía'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */
        $result = $response->json();

        /*
        |--------------------------------------------------------------------------
        | JSON INVÁLIDO
        |--------------------------------------------------------------------------
        */
        if (!is_array($result)) {

            throw new Exception(
                'JSON inválido'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR HTTP
        |--------------------------------------------------------------------------
        */
        if ($response->failed()) {

            throw new ResponseCurlException(
                (string)($result['cod'] ?? '0'),
                (string)($result['msg'] ?? 'Error desconocido')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | PROCESO DE RESPUESTA INVALIDO
        |--------------------------------------------------------------------------
        */
        if (
            isset($result['cod']) ||
            isset($result['msg'])
        ) {

            throw new ResponseCurlException(
                (string)($result['cod'] ?? '0'),
                (string)($result['msg'] ?? 'Error desconocido')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA EXITOSA
        |--------------------------------------------------------------------------
        */
        if (isset($result['access_token'])) {

            return $result['access_token'];
        }
    }

    private function sendDocumentDespatchAdvice(string $token, string $uri, bool $tipoEnvio)
    {

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */
        $url = $tipoEnvio === false
            ? 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/' . $uri
            : 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/' . $uri;


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */
        $data = [
            'archivo' => [
                'nomArchivo' => $this->filename . '.zip',
                'arcGreZip' => $this->filebase64,
                'hashZip' => $this->hashZip,
            ]
        ];

        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */
        $response = Http::withToken($token)
            ->acceptJson()
            ->post($url, $data);


        /*
        |--------------------------------------------------------------------------
        | BODY VACÍO
        |--------------------------------------------------------------------------
        */
        if (!$response->body()) {

            throw new Exception(
                "Respuesta vacía SUNAT"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */
        $result = $response->json();

        if (!is_array($result)) {

            throw new Exception(
                "JSON inválido SUNAT"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR HTTP
        |--------------------------------------------------------------------------
        */
        if ($response->failed()) {

            throw new ResponseCurlException(
                (string)($result['cod'] ?? $response->status()),
                (string)($result['msg'] ?? 'Error HTTP'),
                $response->status()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR TICKET
        |--------------------------------------------------------------------------
        */
        if (!isset($result['numTicket'])) {

            throw new Exception(
                "SUNAT no devolvió numTicket"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */
        $this->setTicket($result['numTicket']);
        $this->setAccepted(true);
        $this->setCode("");
        $this->setMessage(
            "La Guía de remisión se envió correctamente, estado en proceso. Verifique nuevamente en unos minutos."
        );
        $this->setSuccess(true);
    }

    private function getStatusDespatchArchive(string $token, bool $tipoEnvio, string $path)
    {

        /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */
        $url = $tipoEnvio === false
            ? 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/envios/' . $this->ticket
            : 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/envios/' . $this->ticket;

        /*
        |--------------------------------------------------------------------------
        | REQUEST
        |--------------------------------------------------------------------------
        */
        $response = Http::withToken($token)
            ->acceptJson()
            ->get($url);

        /*
        |--------------------------------------------------------------------------
        | BODY VACÍO
        |--------------------------------------------------------------------------
        */
        if (!$response->body()) {

            throw new Exception(
                'Respuesta vacía'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */
        $result = $response->json();


        /*
        |--------------------------------------------------------------------------
        | JSON INVÁLIDO
        |--------------------------------------------------------------------------
        */
        if (!is_array($result)) {

            throw new Exception(
                'JSON inválido'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | JSON
        |--------------------------------------------------------------------------
        */
        $result = $response->json();

        if (!is_array($result)) {

            throw new Exception(
                "JSON inválido"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR HTTP
        |--------------------------------------------------------------------------
        */
        if ($response->failed()) {

            throw new ResponseCurlException(
                (string)($result['cod'] ?? '0'),
                (string)($result['msg'] ?? 'Error desconocido')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDAR codRespuesta
        |--------------------------------------------------------------------------
        */
        if (!isset($result['codRespuesta'])) {

            throw new Exception(
                "SUNAT no devolvió codRespuesta"
            );
        }

        /*
        |--------------------------------------------------------------------------
        | EN PROCESO
        |--------------------------------------------------------------------------
        */
        if ($result['codRespuesta'] === "98") {

            $this->setAccepted(true);
            $this->setSuccess(false);
            $this->setCode($result['codRespuesta']);
            $this->setMessage(
                "El proceso de envío, consulte nuevamente en unos minutos."
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | ERROR FUNCIONAL
        |--------------------------------------------------------------------------
        */
        if ($result['codRespuesta'] === "99") {

            throw new ResponseCurlException(
                (string)($result['error']['numError'] ?? ''),
                (string)($result['error']['desError'] ?? 'Error SUNAT'),
                $response->status()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ÉXITO
        |--------------------------------------------------------------------------
        */
        if ($result['codRespuesta'] === "0") {

            $cdr = base64_decode($result['arcCdr']);

            /*
            |--------------------------------------------------------------------------
            | ZIP
            |--------------------------------------------------------------------------
            */
            Storage::put(
                $path . "R-" . $this->filename . '.zip',
                $cdr
            );

            /*
            |--------------------------------------------------------------------------
            | EXTRAER ZIP
            |--------------------------------------------------------------------------
            */
            $isExtract = Sunat::extractZip(
                Storage::path($path . "R-" . $this->filename . '.zip'),
                Storage::path($path)
            );

            if (!$isExtract) {

                throw new Exception(
                    "No se pudo extraer el archivo zip."
                );
            }

            /*
            |--------------------------------------------------------------------------
            | XML
            |--------------------------------------------------------------------------
            */
            $xml = Storage::get(
                $path . 'R-' . $this->filename . '.xml'
            );

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = false;
            $DOM->loadXML($xml);

            /*
            |--------------------------------------------------------------------------
            | RESPONSE CODE
            |--------------------------------------------------------------------------
            */
            $code = '';

            foreach (
                $DOM->getElementsByTagName('ResponseCode')
                as $node
            ) {
                $code = $node->nodeValue;
            }

            /*
            |--------------------------------------------------------------------------
            | DESCRIPTION
            |--------------------------------------------------------------------------
            */
            $description = '';

            foreach (
                $DOM->getElementsByTagName('Description')
                as $node
            ) {
                $description = $node->nodeValue;
            }

            /*
            |--------------------------------------------------------------------------
            | HASH
            |--------------------------------------------------------------------------
            */
            $hashCode = '';

            foreach (
                $DOM->getElementsByTagName('DocumentDescription')
                as $node
            ) {
                $hashCode = $node->nodeValue;
            }

            /*
            |--------------------------------------------------------------------------
            | ELIMINAR ZIPS
            |--------------------------------------------------------------------------
            */
            if (
                file_exists(
                    Storage::path($path . $this->filename . '.zip')
                )
            ) {
                unlink(
                    Storage::path($path . $this->filename . '.zip')
                );
            }

            if (
                file_exists(
                    Storage::path($path . "R-" . $this->filename . '.zip')
                )
            ) {
                unlink(
                    Storage::path($path . "R-" . $this->filename . '.zip')
                );
            }

            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */
            $this->setAccepted(true);
            $this->setSuccess(true);
            $this->setCode($code);
            $this->setMessage($description);
            $this->setHashCode($hashCode);

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | RESPUESTA DESCONOCIDA
        |--------------------------------------------------------------------------
        */
        throw new Exception(
            "Respuesta inesperada SUNAT"
        );
    }

    public function setConfigGuiaRemision(string $filezip)
    {
        if (!File($filezip)) {
            throw new \Exception('El archivo ZIP no existe.');
        }
        $zipContent = File::get($filezip);

        $this->filebase64 = base64_encode($zipContent);
        $this->hashZip = hash('sha256', $zipContent);
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success)
    {
        $this->success = $success;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted)
    {
        $this->accepted = $accepted;
    }

    public function setError(bool $error)
    {
        $this->error = $error;
    }

    public function getError(): bool
    {
        return $this->error;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getHashCode()
    {
        return $this->hashCode;
    }

    public function setHashCode($hashCode)
    {
        $this->hashCode = $hashCode;
    }

    public function getTicket()
    {
        return $this->ticket;
    }

    public function setTicket($ticket)
    {
        $this->ticket = $ticket;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getFileName()
    {
        return $this->filename;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
}
