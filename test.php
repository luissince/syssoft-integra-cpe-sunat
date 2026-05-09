<?php
// URL de la API
$url = "https://cpe-sunat.syssoftintegra.com/api/v1/facturar";

// Datos a enviar en la solicitud POST
$data = [
    "venta" => [
        "idVenta" => "VT0007",
        "comprobante" => "BOLETA",
        "codigoVenta" => "03",
        "serie" => "B001",
        "numeracion" => 12744,
        "idSucursal" => "SC0001",
        "tipoDoc" => "SIN DOCUMENTO",
        "codigoCliente" => "0",
        "documento" => "00000000",
        "informacion" => "PUBLICO GENERAL",
        "direccion" => "",
        "usuario" => "ALEJANDRO MAGNO2",
        "fecha" => "2025-03-22",
        "hora" => "12:58:45",
        "fechaCorrelativo" => null,
        "correlativo" => null,
        "ticketConsultaSunat" => null,
        "idFormaPago" => "FP0001",
        "estado" => 1,
        "simbolo" => "S/",
        "codiso" => "PEN",
        "moneda" => "SOLES"
    ],
    "empresa" => [
        "documento" => "10764233889",
        "codigo" => "6",
        "razonSocial" => "LARA SERNA LUIS ALEXANDER",
        "nombreEmpresa" => "SYSSOFT-INTEGRA",
        "usuarioSolSunat" => "MODDATOS",
        "claveSolSunat" => "MODDATOS",
        "certificadoPem" => "-----BEGIN CERTIFICATE-----\nMIIEJTCCAw2gAwIBAgIUEc/rkdncoYvw5E0Wa1BeGYrFxdwwDQYJKoZIhvcNAQEL\nBQAwgaExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMaW1hMRMwEQYDVQQHDApNaXJh\nZmxvcmVzMRwwGgYDVQQKDBNTeXNTb2Z0SW50ZWdyYSBTLkEuMREwDwYDVQQLDAhT\naXN0ZW1hczEUMBIGA1UEAwwLMTA3NjQyMzM4ODkxJzAlBgkqhkiG9w0BCQEWGHN5\nc3NvZnRpbnRlZ3JhQGdtYWlsLmNvbTAeFw0yNTAzMjIxOTI5MjZaFw0yNjAzMjIx\nOTI5MjZaMIGhMQswCQYDVQQGEwJQRTENMAsGA1UECAwETGltYTETMBEGA1UEBwwK\nTWlyYWZsb3JlczEcMBoGA1UECgwTU3lzU29mdEludGVncmEgUy5BLjERMA8GA1UE\nCwwIU2lzdGVtYXMxFDASBgNVBAMMCzEwNzY0MjMzODg5MScwJQYJKoZIhvcNAQkB\nFhhzeXNzb2Z0aW50ZWdyYUBnbWFpbC5jb20wggEiMA0GCSqGSIb3DQEBAQUAA4IB\nDwAwggEKAoIBAQDrF0bd4lDd9zukc1oSZIeqntb3PqHPkYEjRfd+yJsCEHE5Lzjz\nqOeDUcPuGW6QcjnohrVvB2C1Za/YwQ6NcpNR+4R/SpOdGm0br2pt1guaTI4BhjvS\nrpRS9J6qeMAjKp0jWNIICJBrXGgzCHBWCqqY5SkzrQrv8uasqZzKvt5kJbTFP/sn\nc1SdPrxvU2gqSHb3GyX3fR24RiZncpc40jku+HN9KVU8bDzW4CIV4jOu8TH4rZ7P\n4f6rgRuPC/hUXmy1trVDplS8wAJcVp+0+yspJG1NliIPB/FWLHyKnh6MVcVqIRvj\nG02XU7RrphYecMZehW88VZKddqyPVLWTcLSNAgMBAAGjUzBRMB0GA1UdDgQWBBTx\nsahsCSiG/cCAslFztjFVVgIxBDAfBgNVHSMEGDAWgBTxsahsCSiG/cCAslFztjFV\nVgIxBDAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQCcNWAFXnca\n/iIhCXgoKTBcrHu3cZZe3cguGDsPd3HiPlvOCg4r/uvJhrK/1/gJ6vE0ad+bg3ra\nasTTTr4t3AO/OGy6WJkO7KccfJHokaUBFTL0kpuOe2+5zX1egOzsZIRj5o6rEPQs\nLzLMnWU1LMEBht1h6M6/usfkE7ysR9UwXf5O3Qy3K+5SUuJnZ3CG3CF8uz3rdlrc\nET2JWdpgY+IZi++O/xi9Cp0Co1yfAfBVGVmTYWy4g9VIE2po0qkMOA9fnPdf+riv\nHB33ThVdE1GJekLTlACg5Em7i66+kaGY8rpZELnoMXarRZxC436gQ3uu9QcPqeWh\nX/gC7QXxs7vX\n-----END CERTIFICATE-----\n",
        "privatePem" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDrF0bd4lDd9zuk\nc1oSZIeqntb3PqHPkYEjRfd+yJsCEHE5LzjzqOeDUcPuGW6QcjnohrVvB2C1Za/Y\nwQ6NcpNR+4R/SpOdGm0br2pt1guaTI4BhjvSrpRS9J6qeMAjKp0jWNIICJBrXGgz\nCHBWCqqY5SkzrQrv8uasqZzKvt5kJbTFP/snc1SdPrxvU2gqSHb3GyX3fR24RiZn\ncpc40jku+HN9KVU8bDzW4CIV4jOu8TH4rZ7P4f6rgRuPC/hUXmy1trVDplS8wAJc\nVp+0+yspJG1NliIPB/FWLHyKnh6MVcVqIRvjG02XU7RrphYecMZehW88VZKddqyP\nVLWTcLSNAgMBAAECggEAOugGuFNcivnHppHG2IP7IIwTdjlp5y5g6ts9xDV07cP7\n8uW7wgYpGJUU4KTbuPL+Qp76eFsjZuCXetsJD/VNJ8Y7sX+Y1E1KWJ0QMHxpRNz4\n2jXt6IEZJl4oIbQHBOjJHhHD8wJeWaB6dYsgRtb+XzhQpiOucWhuV4ZahMzlwbbc\nCtzGBlK9gcN9q0VsjPNpuhQbSDAGcVnrSsjntm+er8VmhH6dXwgYEXJR45Ws3FzS\npDusVXhjhhRV6uW7nBY/uNLN5dQbahqp92FiGCtoGApZN3h6uJr3OBt39TgrX6Yy\nxsyWKLX1AY1APSqBvKv8OQRy/xtS4QguSBcAc6Ed2wKBgQD5cy7Up/G6RSKabGJD\nxosUV0VCY20K3Hg5VsfPLu2GTOFHMtAAnlp7sovhr+JRN9XGwkS/YXyOpNISwilT\n7NyngecZNAgf3pwx7rY/kGBWeEuKNOKp84PBv+DLVYWJkrJxPunSFxi07jqapG/c\n+d8BrFWYZPDReLERci5OtT0VHwKBgQDxQ5JhOrTa/jbQpZF7J2bimOKgrBbEakMB\n46ZeokoS/jZY3qXoSPXDJP5PaZWHAIwnJBJb2ur10GtkckElAOB4enoZR7dRLrRL\nfVfEyWxA7aN/Wd6AIHqH8jsSrzqCuQ7hcnIZgR4DulP6HhhVC4GQnLG+q1VKQwGm\nrUrPXnI00wKBgDco/UYRDSb/erNjHCeYk9Cfq7UOf2JTdlJXmj96RRPZlEdGOTCp\n06BezwfM+OK00hTtiH45dG2mjL2RKcphKjnwQ8YS92j0tN1lx+8uYd89IpchMq4a\nJxyE7ZSJCMpvIf5gxxup99CqjVL84a+foWyhSxwz1fy8D4uoEA1fjm5JAoGBAJul\npEHlxdeiOWrR5dE10kJNr6dIXkfI5gHKBAL5YCBwsE8VFKOOrj5/FzHURAscZ065\nDr8DtKFxHFdo/m4I6sfO/AZjJfjR0K8C5iSmbZhVtyzppYmzallaBJJBSdYb3WXE\nl6esjNiK1LJ/x+LV0XiiHmmLzzJhmCcXlTPDEprRAoGAfovuNOX+zzl2NT8Y2r+9\nHiy0WSM/rfpVPtExJHtdQV59aHSM20B6kAgvZkswWF7UbPUyYTdkPFqq9pc3CPQU\nNQM7cbHTEe0/HKZnj/wUTg1wMJ8lZEKCTkOA+gPUXA5PhOcI7+ZO3OBO1OLsNecR\nYIiJ7asEny7kO+kKOevDx74=\n-----END PRIVATE KEY-----\n",
        "idApiSunat" => "test-85e5b0ae-255c-4891-a595-0b98c65c9854",
        "claveApiSunat" => "test-Hty/M6QshYvPgItX2P0+Kw==",
        "tipoEnvio" => 0
    ],
    "sucursal" => [
        "direccion" => "Alisios 221-197, Lima 15034, Perú",
        "ubigeo" => "150112",
        "departamento" => "LIMA",
        "provincia" => "LIMA",
        "distrito" => "INDEPENDENCIA"
    ],
    "certificado" => [
        "privateKey" => "-----BEGIN PRIVATE KEY-----\nMIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDrF0bd4lDd9zuk\nc1oSZIeqntb3PqHPkYEjRfd+yJsCEHE5LzjzqOeDUcPuGW6QcjnohrVvB2C1Za/Y\nwQ6NcpNR+4R/SpOdGm0br2pt1guaTI4BhjvSrpRS9J6qeMAjKp0jWNIICJBrXGgz\nCHBWCqqY5SkzrQrv8uasqZzKvt5kJbTFP/snc1SdPrxvU2gqSHb3GyX3fR24RiZn\ncpc40jku+HN9KVU8bDzW4CIV4jOu8TH4rZ7P4f6rgRuPC/hUXmy1trVDplS8wAJc\nVp+0+yspJG1NliIPB/FWLHyKnh6MVcVqIRvjG02XU7RrphYecMZehW88VZKddqyP\nVLWTcLSNAgMBAAECggEAOugGuFNcivnHppHG2IP7IIwTdjlp5y5g6ts9xDV07cP7\n8uW7wgYpGJUU4KTbuPL+Qp76eFsjZuCXetsJD/VNJ8Y7sX+Y1E1KWJ0QMHxpRNz4\n2jXt6IEZJl4oIbQHBOjJHhHD8wJeWaB6dYsgRtb+XzhQpiOucWhuV4ZahMzlwbbc\nCtzGBlK9gcN9q0VsjPNpuhQbSDAGcVnrSsjntm+er8VmhH6dXwgYEXJR45Ws3FzS\npDusVXhjhhRV6uW7nBY/uNLN5dQbahqp92FiGCtoGApZN3h6uJr3OBt39TgrX6Yy\nxsyWKLX1AY1APSqBvKv8OQRy/xtS4QguSBcAc6Ed2wKBgQD5cy7Up/G6RSKabGJD\nxosUV0VCY20K3Hg5VsfPLu2GTOFHMtAAnlp7sovhr+JRN9XGwkS/YXyOpNISwilT\n7NyngecZNAgf3pwx7rY/kGBWeEuKNOKp84PBv+DLVYWJkrJxPunSFxi07jqapG/c\n+d8BrFWYZPDReLERci5OtT0VHwKBgQDxQ5JhOrTa/jbQpZF7J2bimOKgrBbEakMB\n46ZeokoS/jZY3qXoSPXDJP5PaZWHAIwnJBJb2ur10GtkckElAOB4enoZR7dRLrRL\nfVfEyWxA7aN/Wd6AIHqH8jsSrzqCuQ7hcnIZgR4DulP6HhhVC4GQnLG+q1VKQwGm\nrUrPXnI00wKBgDco/UYRDSb/erNjHCeYk9Cfq7UOf2JTdlJXmj96RRPZlEdGOTCp\n06BezwfM+OK00hTtiH45dG2mjL2RKcphKjnwQ8YS92j0tN1lx+8uYd89IpchMq4a\nJxyE7ZSJCMpvIf5gxxup99CqjVL84a+foWyhSxwz1fy8D4uoEA1fjm5JAoGBAJul\npEHlxdeiOWrR5dE10kJNr6dIXkfI5gHKBAL5YCBwsE8VFKOOrj5/FzHURAscZ065\nDr8DtKFxHFdo/m4I6sfO/AZjJfjR0K8C5iSmbZhVtyzppYmzallaBJJBSdYb3WXE\nl6esjNiK1LJ/x+LV0XiiHmmLzzJhmCcXlTPDEprRAoGAfovuNOX+zzl2NT8Y2r+9\nHiy0WSM/rfpVPtExJHtdQV59aHSM20B6kAgvZkswWF7UbPUyYTdkPFqq9pc3CPQU\nNQM7cbHTEe0/HKZnj/wUTg1wMJ8lZEKCTkOA+gPUXA5PhOcI7+ZO3OBO1OLsNecR\nYIiJ7asEny7kO+kKOevDx74=\n-----END PRIVATE KEY-----\n",
        "publicKey" => "-----BEGIN CERTIFICATE-----\nMIIEJTCCAw2gAwIBAgIUEc/rkdncoYvw5E0Wa1BeGYrFxdwwDQYJKoZIhvcNAQEL\nBQAwgaExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMaW1hMRMwEQYDVQQHDApNaXJh\nZmxvcmVzMRwwGgYDVQQKDBNTeXNTb2Z0SW50ZWdyYSBTLkEuMREwDwYDVQQLDAhT\naXN0ZW1hczEUMBIGA1UEAwwLMTA3NjQyMzM4ODkxJzAlBgkqhkiG9w0BCQEWGHN5\nc3NvZnRpbnRlZ3JhQGdtYWlsLmNvbTAeFw0yNTAzMjIxOTI5MjZaFw0yNjAzMjIx\nOTI5MjZaMIGhMQswCQYDVQQGEwJQRTENMAsGA1UECAwETGltYTETMBEGA1UEBwwK\nTWlyYWZsb3JlczEcMBoGA1UECgwTU3lzU29mdEludGVncmEgUy5BLjERMA8GA1UE\nCwwIU2lzdGVtYXMxFDASBgNVBAMMCzEwNzY0MjMzODg5MScwJQYJKoZIhvcNAQkB\nFhhzeXNzb2Z0aW50ZWdyYUBnbWFpbC5jb20wggEiMA0GCSqGSIb3DQEBAQUAA4IB\nDwAwggEKAoIBAQDrF0bd4lDd9zukc1oSZIeqntb3PqHPkYEjRfd+yJsCEHE5Lzjz\nqOeDUcPuGW6QcjnohrVvB2C1Za/YwQ6NcpNR+4R/SpOdGm0br2pt1guaTI4BhjvS\nrpRS9J6qeMAjKp0jWNIICJBrXGgzCHBWCqqY5SkzrQrv8uasqZzKvt5kJbTFP/sn\nc1SdPrxvU2gqSHb3GyX3fR24RiZncpc40jku+HN9KVU8bDzW4CIV4jOu8TH4rZ7P\n4f6rgRuPC/hUXmy1trVDplS8wAJcVp+0+yspJG1NliIPB/FWLHyKnh6MVcVqIRvj\nG02XU7RrphYecMZehW88VZKddqyPVLWTcLSNAgMBAAGjUzBRMB0GA1UdDgQWBBTx\nsahsCSiG/cCAslFztjFVVgIxBDAfBgNVHSMEGDAWgBTxsahsCSiG/cCAslFztjFV\nVgIxBDAPBgNVHRMBAf8EBTADAQH/MA0GCSqGSIb3DQEBCwUAA4IBAQCcNWAFXnca\n/iIhCXgoKTBcrHu3cZZe3cguGDsPd3HiPlvOCg4r/uvJhrK/1/gJ6vE0ad+bg3ra\nasTTTr4t3AO/OGy6WJkO7KccfJHokaUBFTL0kpuOe2+5zX1egOzsZIRj5o6rEPQs\nLzLMnWU1LMEBht1h6M6/usfkE7ysR9UwXf5O3Qy3K+5SUuJnZ3CG3CF8uz3rdlrc\nET2JWdpgY+IZi++O/xi9Cp0Co1yfAfBVGVmTYWy4g9VIE2po0qkMOA9fnPdf+riv\nHB33ThVdE1GJekLTlACg5Em7i66+kaGY8rpZELnoMXarRZxC436gQ3uu9QcPqeWh\nX/gC7QXxs7vX\n-----END CERTIFICATE-----\n"
    ],
    "detalle" => [
        [
            "producto" => "suerox sabor manzana 630 ml",
            "codigoMedida" => "NIU",
            "medida" => "UNIDAD",
            "categoria" => "PLASTICO",
            "precio" => 10,
            "cantidad" => 1,
            "idImpuesto" => "IM0002",
            "impuesto" => "IGV(18%)",
            "codigo" => "10",
            "porcentaje" => 18
        ],
        [
            "producto" => "suerox sabor mora azul-hierbabuena 630ml",
            "codigoMedida" => "NIU",
            "medida" => "UNIDAD",
            "categoria" => "PLASTICO",
            "precio" => 7,
            "cantidad" => 1,
            "idImpuesto" => "IM0002",
            "impuesto" => "IGV(18%)",
            "codigo" => "10",
            "porcentaje" => 18
        ]
    ],
    "cuotas" => []
];

// Inicializar cURL
$ch = curl_init();

// Configurar la URL y otras opciones
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

// Ejecutar la solicitud y obtener la respuesta
$response = curl_exec($ch);

// Verificar si hubo algún error
if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
} else {
    echo 'Respuesta: ' . $response;
}

// Cerrar el recurso cURL
curl_close($ch);
?>
