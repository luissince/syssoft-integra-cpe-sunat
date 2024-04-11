<?php

namespace App\Http\Controllers;

use App\Models\Consulta;
use App\Src\SoapResult;
use App\Src\Sunat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpresaController extends Controller
{
    public function index()
    {
        return response()->json([
            "message" => "Laravel API"
        ]);
    }

    public function create(Request $request)
    {
        try {
            DB::beginTransaction();

            $path = "";

            if ($request->input('certificadoType') == 1 && $request->hasFile('certificado')) {
                $certificado = $request->file('certificado');

                $ext = $certificado->getClientOriginalExtension();
                $file_path = $request->input('txtNumDocumento') . "." . $ext;
                $path = "files/certificado/" . $file_path;

                $certificado->storeAs('files/certificado', $file_path);

                $pkcs12 = file_get_contents($certificado->path());
                $certificados = array();
                $respuesta = openssl_pkcs12_read($pkcs12, $certificados, $request->input('txtClaveCertificado'));

                if ($respuesta) {
                    $publicKeyPem  = $certificados['cert'];
                    $privateKeyPem = $certificados['pkey'];

                    Storage::put('files/certificado/private_key.pem', $privateKeyPem);
                    Storage::put('files/certificado/public_key.pem', $publicKeyPem);

                    chmod(Storage::path('files/certificado/private_key.pem'), 0777);
                    chmod(Storage::path('files/certificado/public_key.pem'), 0777);
                } else {
                    throw new Exception('Error al crear las llaves del certificado.');
                }
            } else {
                $path = $request->input('certificadoUrl');
            }

            DB::table('empresa')
                ->where('idEmpresa', $request->input('idEmpresa'))
                ->update([
                    'usuarioSolSunat' => $request->input('txtUsuarioSol'),
                    'claveSolSunat' => $request->input('txtClaveSol'),
                    'certificadoSunat' => $path,
                    'claveCertificadoSunat' => $request->input('txtClaveCertificado'),
                    'idApiSunat' => $request->input('txtIdApiSunat'),
                    'claveApiSunat' => $request->input('txtClaveApiSunat'),
                    'tipoEnvio' => ($request->input('cbSelectTipoEnvio') === "true")
                ]);

            DB::commit();

            return response()->json([
                "state" => 1,
                "message" => "Se modificó correctamente los datos."
            ], 201);
        } catch (Exception $ex) {
            if (isset($file_path)) {
                Storage::delete($file_path);
            }

            DB::rollBack();

            return response($ex->getMessage(),500);
        }
    }

    public function consultar(string $ruc,string $usuarioSol, string $claveSol, string $tipoComprobante, string $serie, string $numeracion)
    {
        $get['rucSol'] = $ruc;
        $get['userSol'] = $usuarioSol;
        $get['passSol'] = $claveSol;
        $get['ruc'] = $ruc;
        $get['tipo'] = $tipoComprobante;
        $get['serie'] = $serie;
        $get['numero'] = $numeracion;
        $get['cdr'] = '';

        $arguments = [
            $get["ruc"],
            $get["tipo"],
            $get["serie"],
            intval($get["numero"])
        ];

        $wdsl = Storage::path('wsdl/billConsultService.wsdl');

        $soapResult = new SoapResult($wdsl, implode('-', $arguments));
        $soapResult->sendGetStatusValid(Sunat::xmlGetValidServiceSunat($get));

        if ($soapResult->isSuccess()) {
            if ($soapResult->isAccepted()) {
                return response()->json([
                    "state" => $soapResult->isSuccess(),
                    "accepted" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "message" => $soapResult->getMessage()
                ]);
            } else {
                return response()->json([
                    "state" => $soapResult->isSuccess(),
                    "accepted" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "message" => $soapResult->getMessage(),
                ]);
            }
        } else {
            return response()->json(["message" => $soapResult->getMessage()], 500);
        }
    }

    public function sendConsulta(Request $request)
    {
        $consulta = new Consulta($request);

        $arguments = [
            $consulta->ruc,
            $consulta->tipoComprobante,
            $consulta->serie,
            $consulta->numeracion
        ];

        $wdsl = Storage::path('wsdl/billConsultService.wsdl');

        $soapResult = new SoapResult($wdsl, implode('-', $arguments));
        $soapResult->sendGetStatusValid(Sunat::xmlGetValidService($consulta));

        if ($soapResult->isSuccess()) {
            if ($soapResult->isAccepted()) {
                return response()->json([
                    "state" => $soapResult->isSuccess(),
                    "accepted" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "message" => $soapResult->getMessage()
                ]);
            } else {
                return response()->json([
                    "state" => $soapResult->isSuccess(),
                    "accepted" => $soapResult->isAccepted(),
                    "code" => $soapResult->getCode(),
                    "message" => $soapResult->getMessage(),
                ]);
            }
        } else {
            return response()->json(["message" => $soapResult->getMessage()], 500);
        }
    }
}
