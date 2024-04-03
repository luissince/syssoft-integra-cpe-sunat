<?php

namespace App\Src;

use Exception;
use SoapFault;
use DOMDocument;
use App\Src\Sunat;
use App\Src\SoapBuilder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SoapResult
{
    private $wsdlURL;

    private $file;

    private $filename;

    private $success = false;

    private $accepted = false;

    private $hashCode = "";

    private $description = "";

    private $ticket = "";

    private $message = "";

    private $code = "";

    private $filebase64 = "";

    private $hashZip = "";


    public function __construct($wsdlURL, $filename)
    {
        $this->wsdlURL = $wsdlURL;
        $this->filename = $filename;
    }

    public function sendBill($xmlSend)
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
            $archivo = fopen(Storage::path("files/sunat/R-" . $this->filename . '.zip'), 'w+');
            fputs($archivo, $cdr);
            fclose($archivo);
            // chmod('../files/R-' . $this->filename . '.zip', 0777);

            // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
            $isExtract = Sunat::extractZip(
                Storage::path("files/sunat/R-" . $this->filename . '.zip'),
                Storage::path("files/sunat/")
            );

            if (!$isExtract) {
                throw new Exception("No se pudo extraer el contenido del archivo zip.");
            }

            // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
            $xml = Storage::get('files/sunat/R-' . $this->filename . '.xml');
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

            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path("files/sunat/R-" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/R-" . $this->filename . '.zip'));
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
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
            }

            $code = preg_replace('/[^0-9]/', '', $ex->faultcode);
            $message = $ex->faultstring;
            $this->setSuccess(false);
            $this->setCode($code);
            $this->setDescription($message);
        } catch (Exception $ex) {
            if (file_exists(Storage::path('files/sunat/' . $this->filename . '.xml'))) {
                unlink(Storage::path('files/sunat/' . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path('files/sunat/' . $this->filename . '.zip'))) {
                unlink(Storage::path('files/sunat/' . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path('files/sunat/R-' . $this->filename . '.zip'))) {
                unlink(Storage::path('files/sunat/R-' . $this->filename . '.zip'));
            }

            $this->setSuccess(false);
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

    public function sendGetStatus($xmlSend)
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
                $archivo = fopen(Storage::path("files/sunat/R-" . $this->filename . '.zip'), 'w+');
                fputs($archivo, $cdr);
                fclose($archivo);
                // chmod('../files/R-' . $this->filename . '.zip', 0777);

                // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
                $isExtract = Sunat::extractZip(
                    Storage::path("files/sunat/R-" . $this->filename . '.zip'),
                    Storage::path("files/sunat/")
                );
                if (!$isExtract) {
                    throw new Exception("No se pudo extraer el contenido del archivo zip.");
                }

                // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
                $xml = Storage::get('files/sunat/R-' . $this->filename . '.xml');
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
                $archivo = fopen(Storage::path("files/sunat/R-" . $this->filename . '.zip'), 'w+');
                fputs($archivo, $cdr);
                fclose($archivo);
                // chmod('../files/R-' . $this->filename . '.zip', 0777);

                // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
                $isExtract = Sunat::extractZip(
                    Storage::path("files/sunat/R-" . $this->filename . '.zip'),
                    Storage::path("files/sunat/")
                );
                if (!$isExtract) {
                    throw new Exception("No se pudo extraer el contenido del archivo zip.");
                }

                // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
                $xml = Storage::get('files/sunat/R-' . $this->filename . '.xml');
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
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                unlink(Storage::path("files/sunat/" .  $this->filename . '.xml'));
            }
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
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
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
            }
            if (file_exists(Storage::path("files/sunat/R-" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/R-" . $this->filename . '.zip'));
            }
            $this->setSuccess(false);
            $this->setCode("-1");
            $this->setDescription($ex->getMessage());
        }
    }

    public function sendGetStatusCdr($xmlSend)
    {
        try {
            $client = new SoapBuilder($this->wsdlURL, array('trace' => true));
            $client->SoapClientCall($xmlSend);
            $client->SoapCall("getStatusCdr");
            $result = $client->__getLastResponse();

            $DOM = new DOMDocument('1.0', 'utf-8');
            $DOM->preserveWhiteSpace = FALSE;

            if ($DOM->loadXML($result)) {
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

                $DocXML = $DOM->getElementsByTagName('content');
                $content = "";
                foreach ($DocXML as $Nodo) {
                    $content = $Nodo->nodeValue;
                }

                if ($content != "") {
                    $cdr = base64_decode($content);
                    $archivo = fopen('../../files/R-' . $this->filename . '.zip', 'w+');
                    fputs($archivo, $cdr);
                    fclose($archivo);
                    chmod('../../files/R-' . $this->filename . '.zip', 0777);

                    $isExtract = Sunat::extractZip('../../files/R-' . $this->filename . '.zip', '../../files/');
                    if (!$isExtract) {
                        throw new Exception("No se pudo extraer el contenido del archivo zip.");
                    }

                    $xml = file_get_contents('../../files/' . $isExtract[0]);
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
                    $this->setFile('R-' . $this->filename . '.zip');
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

    public function sendGuiaRemision(array $credenciales, array $uri, bool $tipoEnvio)
    {
        try {
            $accessToken = $this->getTokenApiSunat($credenciales, $tipoEnvio);

            if ($this->ticket) {
                $this->sendGetStatusGuiaRemision($accessToken, $tipoEnvio);
            } else {
                $this->sendApiSunatGuiaRemision($accessToken, implode("-", $uri), $tipoEnvio);
            }
        } catch (ResponseCurlException $ex) {
            $this->setSuccess(false);
            $this->setCode($ex->getCodigo());
            $this->setMessage($ex->getMensaje());
        } catch (Exception $ex) {
            $this->setSuccess(false);
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
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded'
        );

        $data = array(
            'grant_type' => 'password',
            'scope' => urlencode('https://api-cpe.sunat.gob.pe'),
            'client_id' => urlencode($credenciales["IdApiSunat"]),
            'client_secret' => urlencode($credenciales["ClaveApiSunat"]),
            'username' => $credenciales["NumeroDocumento"] . "" . $credenciales["UsuarioSol"],
            'password' => $credenciales["ClaveSol"],
        );

        $fields = "";
        $index = 0;
        foreach ($data as $key => $val) {
            $index++;
            $fields .= $index == count($data) ? $key . "=" . $val : $key . "=" . $val . "&";
        }

        $curl = curl_init();
        if ($tipoEnvio === false) {
            curl_setopt($curl, CURLOPT_URL, 'https://gre-test.nubefact.com/v1/clientessol/' . $credenciales["IdApiSunat"] . '/oauth2/token');
        } else {
            curl_setopt($curl, CURLOPT_URL, 'https://api-seguridad.sunat.gob.pe/v1/clientessol/' . $credenciales["IdApiSunat"] . '/oauth2/token/');
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            $result = (object)json_decode($response);
            return $result->access_token;
        } else {
            if ($response) {
                $result = (object)json_decode($response);

                $codigo = isset($result->cod) ? $result->cod : '';
                $mensaje = isset($result->msg) ? $result->msg : '';
                throw new ResponseCurlException($codigo, $mensaje, $http_code, null);
            } else {
                throw new Exception("Error desconocido al intentar obtener el token de acceso.");
            }
        }
    }

    private function sendApiSunatGuiaRemision(string $token, string $uri, $tipoEnvio)
    {
        $headers = array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token . ''
        );

        $data = array(
            'archivo' => array(
                'nomArchivo' => '' . $this->filename . '.zip',
                'arcGreZip' => $this->filebase64,
                'hashZip' => $this->hashZip,
            )
        );

        $data_string = json_encode($data);

        $curl = curl_init();
        if ($tipoEnvio === false) {
            curl_setopt($curl, CURLOPT_URL, 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/' . $uri . '');
        } else {
            curl_setopt($curl, CURLOPT_URL, 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/' . $uri . '');
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, '');
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 0);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($curl);

        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code == 200) {
            $result = (object)json_decode($response);
            $this->setTicket($result->numTicket);
            $this->setAccepted(true);
            $this->setCode("");
            $this->setMessage("La Guía de remisión se envío correctamente, estado en proceso verique en un par de minutos.");
            $this->setSuccess(true);
        } else {
            if ($response) {
                if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                    unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                }
                if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                    unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                }

                $result = (object)json_decode($response);

                $codigo = isset($result->cod) ? $result->cod : '';
                $mensaje = isset($result->exc) ? $result->exc : $result->msg;

                throw new ResponseCurlException($codigo, $mensaje, $http_code, null);
            } else {
                if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                    unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                }
                if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                    unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                }
                throw new Exception("Se presento una condicion inesperada que impidio completar el Request");
            }
        }
    }

    private function sendGetStatusGuiaRemision(string $token, bool $tipoEnvio)
    {
        try {
            $headers = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token . ''
            );

            $curl = curl_init();
            if ($tipoEnvio === false) {
                curl_setopt($curl, CURLOPT_URL, 'https://gre-test.nubefact.com/v1/contribuyente/gem/comprobantes/envios/' .  $this->ticket . '');
            } else {
                curl_setopt($curl, CURLOPT_URL, 'https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/envios/' .  $this->ticket . '');
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

            $response = curl_exec($curl);

            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($http_code == 200) {
                $result = (object)json_decode($response);
                if ($result->codRespuesta == "0") {
                    $cdr = base64_decode($result->arcCdr);

                    // $archivo = fopen('../files/R-' . $this->filename . '.zip', 'w+');
                    $archivo = fopen(Storage::path("files/sunat/R-" . $this->filename . '.zip'), 'w+');
                    fputs($archivo, $cdr);
                    fclose($archivo);
                    // chmod('../files/R-' . $this->filename . '.zip', 0777);

                    // $isExtract = Sunat::extractZip('../files/R-' . $this->filename . '.zip', '../files/');
                    $isExtract = Sunat::extractZip(
                        Storage::path("files/sunat/R-" . $this->filename . '.zip'),
                        Storage::path("files/sunat/")
                    );

                    if (!$isExtract) {
                        throw new Exception("No se pudo extraer el contenido del archivo zip.");
                    }

                    // $xml = file_get_contents('../files/R-' . $this->filename . '.xml');
                    $xml = Storage::get('files/sunat/R-' . $this->filename . '.xml');
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

                    $DocXML = $DOM->getElementsByTagName('DocumentDescription');
                    $hashCode = "";
                    foreach ($DocXML as $Nodo) {
                        $hashCode = $Nodo->nodeValue;
                    }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }
                    // if (file_exists('../files/R-' . $this->filename . '.zip')) {
                    //     unlink('../files/R-' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }
                    if (file_exists(Storage::path("files/sunat/R-" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/R-" . $this->filename . '.zip'));
                    }

                    $this->setAccepted(true);
                    $this->setCode($code);
                    $this->setMessage($description);
                    $this->setHashCode($hashCode);
                    $this->setSuccess(true);
                } else if ($result->codRespuesta == "98") {
                    // if (file_exists('../files/' . $this->filename . '.xml')) {
                    //     unlink('../files/' . $this->filename . '.xml');
                    // }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                    }
                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }

                    // $logFile = fopen("log.txt", 'a') or die("Error creando archivo");
                    // fwrite($logFile, "\n" . date("d/m/Y H:i:s") . "N° TICKET: " . $this->ticket . "\r\n") or die("Error escribiendo en el archivo");
                    // fclose($logFile);

                    // $logFile = fopen("log.txt", 'a') or die("Error creando archivo");
                    // fwrite($logFile, "\n" . date("d/m/Y H:i:s") . $response . "\r\n") or die("Error escribiendo en el archivo");
                    // fclose($logFile);

                    $this->setAccepted(true);
                    $this->setCode($result->codRespuesta);
                    $this->setMessage("El proceso de envío, consulte en un par de minutos nuevamente.");
                    $this->setSuccess(false);
                } else if ($result->codRespuesta == "99") {
                    // if (file_exists('../files/' . $this->filename . '.xml')) {
                    //     unlink('../files/' . $this->filename . '.xml');
                    // }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                    }
                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }

                    $code = $result->codRespuesta;
                    $message = "";
                    if (isset($result->error)) {
                        $code = $result->error->numError;
                        $message = $result->error->desError;
                    } else {
                        $message = "Se genero un problema, comuníquese con su proveedor del software.";
                    }

                    throw new Exception($message, $code);
                } else {
                    // if (file_exists('../files/' . $this->filename . '.xml')) {
                    //     unlink('../files/' . $this->filename . '.xml');
                    // }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                    }
                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }

                    throw new Exception("Se genero un problema, comuníquese con su proveedor del software.", $result->codRespuesta);
                }
            } else {
                if ($response) {
                    // if (file_exists('../files/' . $this->filename . '.xml')) {
                    //     unlink('../files/' . $this->filename . '.xml');
                    // }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                    }
                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }

                    $result = (object)json_decode($response);

                    $codigo = isset($result->cod) ? $result->cod : '';
                    $mensaje = isset($result->msg) ? $result->msg : '';
                    throw new ResponseCurlException($codigo, $mensaje, $http_code, null);
                } else {
                    // if (file_exists('../files/' . $this->filename . '.xml')) {
                    //     unlink('../files/' . $this->filename . '.xml');
                    // }
                    // if (file_exists('../files/' . $this->filename . '.zip')) {
                    //     unlink('../files/' . $this->filename . '.zip');
                    // }

                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
                    }
                    if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                        unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
                    }

                    throw new Exception("Se presento una condicion inesperada que impidio completar el
                    Request", -1);
                }
            }
        } catch (Exception $ex) {
            // if (file_exists('../files/' . $this->filename . '.xml')) {
            //     unlink('../files/' . $this->filename . '.xml');
            // }
            // if (file_exists('../files/' . $this->filename . '.zip')) {
            //     unlink('../files/' . $this->filename . '.zip');
            // }

            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.xml'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.xml'));
            }
            if (file_exists(Storage::path("files/sunat/" . $this->filename . '.zip'))) {
                unlink(Storage::path("files/sunat/" . $this->filename . '.zip'));
            }

            throw new Exception($ex->getMessage(), -1);
        }
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

    public function isSuccess()
    {
        return $this->success;
    }

    public function setSuccess($success)
    {
        $this->success = $success;
    }

    public function isAccepted()
    {
        return $this->accepted;
    }

    public function setAccepted($accepted)
    {
        $this->accepted = $accepted;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
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

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
}
