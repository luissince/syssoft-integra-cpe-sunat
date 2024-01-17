<?php

namespace App\Http\Controllers;

use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmpresaCotroller extends Controller
{

    public function index(Request $request)
    {
        $empresa = DB::select("SELECT 
            idEmpresa,
            documento,
            razonSocial,
            usuarioSolSunat,
            claveSolSunat,
            certificadoSunat,
            claveCertificadoSunat,
            idApiSunat,
            claveApiSunat
        FROM 
            empresa 
        LIMIT 1");
        return view('welcome', ["empresa" => $empresa[0]]);
    }

    public function create(Request $request){

        try {
            DB::beginTransaction();

            $path = "";

            if ($request->input('certificadoType') == 1 && $request->hasFile('certificado')) {
                $certificado = $request->file('certificado');

                $ext = $certificado->getClientOriginalExtension();
                $file_path = $request->input('txtNumDocumento') . "." . $ext;
                $path = "certificado/" . $file_path;

                $certificado->storeAs('certificado', $file_path);

                $pkcs12 = file_get_contents($certificado->path());
                $certificados = array();
                $respuesta = openssl_pkcs12_read($pkcs12, $certificados, $request->input('txtClaveCertificado'));

                if ($respuesta) {
                    $publicKeyPem  = $certificados['cert'];
                    $privateKeyPem = $certificados['pkey'];

                    Storage::put('certificado/private_key.pem', $privateKeyPem);
                    Storage::put('certificado/public_key.pem', $publicKeyPem);
                } else {
                    throw new Exception('Error al crear las llaves del certificado.');
                }
            } elseif ($request->input('certificadoType') == 2 && $request->filled('certificadoUrl')) {
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

            return response()->json([
                "state" => 0,
                "message" => $ex->getMessage()
            ], 500);
        }
    }

}
