@extends('layouts.app')

@section('title', 'SysSoft Integra - Cpe Sunat')

@section('content')

    <div class="app-title">
        <div>
            <h1>Configurar datos SUNAT</h1>
        </div>
    </div>

    <div class="tile mb-4">

        <div class="overlay d-none" id="divOverlayEmpresa">
            <div class="m-loader mr-4">
                <svg class="m-circular" viewBox="25 25 50 50">
                    <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="4"
                        stroke-miterlimit="10"></circle>
                </svg>
            </div>
            <h4 class="l-text text-white" id="lblTextOverlayEmpresa">Cargando información...</h4>
        </div>

        <div class="tile-body">

            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-text"> R.U.C: <i class="fa fa-fw fa-asterisk text-danger"></i></label>
                            <div class="form-group">
                                <input id="txtNumDocumento" class="form-control" type="text" placeholder="R.U.C."
                                    value="{{ $empresa->documento }}" disabled />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <label class="form-text"> Razón Social: <i class="fa fa-fw fa-asterisk text-danger"></i></label>
                            <div class="form-group">
                                <input id="txtRazonSocial" class="form-control" type="text" placeholder="Razón Social"
                                    {{ $empresa->razonSocial }} disabled />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <label class="form-text"> Usuario Sol (Sunat):</label>
                    <div class="form-group">
                        <input id="txtUsuarioSol" class="form-control" type="text" placeholder="Usuario Sol"
                            value="{{ $empresa->usuarioSolSunat }}" />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-text"> Contraseña Sol (Sunat):</label>
                    <div class="form-group">
                        <div class="input-group">
                            <input id="txtClaveSol" class="form-control" type="password" placeholder="Password SOL"
                                value="{{ $empresa->claveSolSunat }}" />
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" id="btnMirarClaveSol"><i
                                        class="fa fa-eye"></i></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <div class="col-md-6">
                    <label class="form-text">Id (Api Sunat):</label>
                    <div class="form-group">
                        <input id="txtIdApiSunat" class="form-control" type="text"
                            placeholder="Contraseña de tu Certificado" value="{{ $empresa->idApiSunat }}" />
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-text">Clave (Api Sunat):</label>
                    <div class="form-group">
                        <div class="input-group">
                            <input id="txtClaveApiSunat" class="form-control" type="password"
                                placeholder="Contraseña de tu Certificado" value="{{ $empresa->claveApiSunat }}" />
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" id="btnMirarApiSunat"><i
                                        class="fa fa-eye"></i></label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">

                <div class="col-md-6">
                    <label class="form-text"> Seleccionar Archivo (.p12, .pfx u otros):</label>
                    <div class="form-group d-flex">
                        <input type="file" class="form-control d-none" id="fileCertificado">
                        <div class="input-group">
                            <label class="form-control" for="fileCertificado"
                                id="lblNameCertificado">{{ $empresa->certificadoSunat }}</label>
                            <div class="input-group-append">
                                <label for="fileCertificado" class="btn btn-info" type="button"
                                    id="btnReloadCliente">Subir</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-text"> Contraseña de tu Certificado:</label>
                    <div class="form-group">
                        <div class="input-group">
                            <input id="txtClaveCertificado" class="form-control" type="password"
                                placeholder="Contraseña de tu Certificado"
                                value="{{ $empresa->claveCertificadoSunat }}" />
                            <div class="input-group-append">
                                <button class="btn btn-info" type="button" id="btnMirarClaveCert"><i
                                        class="fa fa-eye"></i></label>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-text text-left text-danger">Todos los campos marcados con <i
                                class="fa fa-fw fa-asterisk text-danger"></i> son obligatorios</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group text-right">
                        <button class="btn btn-success" type="button" id="btnGuardar"><i class="fa fa-save"></i>
                            Guardar</button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('script')
    <script>
        @php
            $idEmpresa = $empresa->idEmpresa;
        @endphp

        let tools = new Tools();
        let idEmpresa = "{{ $idEmpresa }}";
        let txtNumDocumento = $("#txtNumDocumento");
        let txtRazonSocial = $("#txtRazonSocial");

        let txtUsuarioSol = $("#txtUsuarioSol");
        let txtClaveSol = $("#txtClaveSol");
        let lblNameCertificado = $("#lblNameCertificado");
        let fileCertificado = $("#fileCertificado");
        let txtClaveCertificado = $("#txtClaveCertificado");
        let txtIdApiSunat = $("#txtIdApiSunat");
        let txtClaveApiSunat = $("#txtClaveApiSunat");
        $(document).ready(function() {

            $("#fileCertificado").on('change', function(event) {
                if (event.target.files.length > 0) {
                    lblNameCertificado.empty();
                    lblNameCertificado.html(event.target.files[0].name);
                }
            });

            $("#btnMirarClaveSol").click(function() {
                const fieldType = txtClaveSol.attr('type');

                if (fieldType === 'password') {
                    txtClaveSol.attr('type', 'text');
                } else {
                    txtClaveSol.attr('type', 'password');
                }
            });

            $("#btnMirarApiSunat").click(function() {
                const fieldType = txtClaveApiSunat.attr('type');

                if (fieldType === 'password') {
                    txtClaveApiSunat.attr('type', 'text');
                } else {
                    txtClaveApiSunat.attr('type', 'password');
                }
            });

            $("#btnMirarClaveCert").click(function() {
                const fieldType = txtClaveCertificado.attr('type');

                if (fieldType === 'password') {
                    txtClaveCertificado.attr('type', 'text');
                } else {
                    txtClaveCertificado.attr('type', 'password');
                }
            });

            $("#btnGuardar").keypress(function(event) {
                if (event.keyCode == 13) {
                    crudEmpresa();
                }
                event.preventDefault();
            });

            $("#btnGuardar").click(function() {
                crudEmpresa();
            });
        });

        function crudEmpresa() {
            var formData = new FormData();
            formData.append("idEmpresa", idEmpresa);
            formData.append("txtNumDocumento", txtNumDocumento.val());
            formData.append("txtUsuarioSol", txtUsuarioSol.val());
            formData.append("txtClaveSol", txtClaveSol.val());
            formData.append("certificadoUrl", lblNameCertificado.html());
            formData.append("certificadoType", fileCertificado[0].files.length);
            formData.append("certificado", fileCertificado[0].files[0]);
            formData.append("txtClaveCertificado", txtClaveCertificado.val());
            formData.append("txtIdApiSunat", txtIdApiSunat.val());
            formData.append("txtClaveApiSunat", txtClaveApiSunat.val());

            tools.ModalDialog("Mi Empresa", "¿Está seguro de continuar?", function(accept) {
                if (accept) {
                    $.ajax({
                        url: "api/create",
                        method: "POST",
                        data: formData,
                        contentType: false,
                        processData: false,
                        beforeSend: function() {
                            tools.ModalAlertInfo("Mi Empresa", "Procesando petición..");
                        },
                        success: function(result) {
                            tools.ModalAlertSuccess("Mi Empresa", result.message);
                        },
                        error: function(error) {
                            tools.ModalAlertError("Mi Empresa", "Se produjo un error: " + error
                                .responseText);
                        }
                    });
                }
            });

        }
    </script>
@endsection
