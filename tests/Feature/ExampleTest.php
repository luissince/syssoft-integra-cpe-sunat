<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // public function testBoletaRoute()
    // {
    //     $response = $this->get('/api/boleta/VT0001');

    //     $response->assertStatus(200);

    //     // Verifica que la respuesta JSON sea exactamente la esperada
    //     // $response->assertExactJson([
    //     //     "state" => true,
    //     //     "accept" => true,
    //     //     "code" => "0",
    //     //     "description" => "La Factura numero F001-2, ha sido aceptada"
    //     // ]);
    // }

    // public function testFacturaRoute()
    // {
    //     $response = $this->get('/api/boleta/VT0002');

    //     $response->assertStatus(200);

    //     // Verifica que la respuesta JSON sea exactamente la esperada
    //     // $response->assertExactJson([
    //     //     "state" => true,
    //     //     "accept" => true,
    //     //     "code" => "0",
    //     //     "description" => "La Factura numero F001-2, ha sido aceptada"
    //     // ]);
    // }

    // public function testFacturaCreditoRoute()
    // {
    //     $response = $this->get('/api/boleta/VT0003');

    //     $response->assertStatus(200);

    //     // Verifica que la respuesta JSON sea exactamente la esperada
    //     // $response->assertExactJson([
    //     //     "state" => true,
    //     //     "accept" => true,
    //     //     "code" => "0",
    //     //     "description" => "La Factura numero F001-2, ha sido aceptada"
    //     // ]);
    // }

    // public function testGuiaRemisionRoute()
    // {
    //     $response = $this->get('/api/guiaremision/GR0004');

    //     $response->assertStatus(200);

    //     // Verifica que la respuesta JSON sea exactamente la esperada
    //     // $response->assertExactJson([
    //     //     "state" => true,
    //     //     "accept" => true,
    //     //     "code" => "0",
    //     //     "description" => "ACEPTADA"
    //     // ]);
    // }

    // public function testFacturaRoute()
    // {
    //     $response = $this->get('/api/boleta/VT0007');

    //     $response->assertStatus(200);

    //     // Verifica que la respuesta JSON sea exactamente la esperada
    //     // $response->assertExactJson([
    //     //     "state" => true,
    //     //     "accept" => true,
    //     //     "code" => "0",
    //     //     "description" => "La Factura numero F001-2, ha sido aceptada"
    //     // ]);
    // }

    // public function testConsultaComprobanteRoute(){
    //     $response = $this->get('/api/consultar/10764233889/LUIS2023/Qz0966lb/01/F001/1');

    //     error_log(json_encode($response));

    //     $response->assertStatus(200);
    // }

    // public function testFacturarRoute()
    // {
    //     $response = $this->json('POST', '/api/facturar', [
    //         "venta" => [
    //             "idVenta" => "VT0007",
    //             "comprobante" => "FACTURA",
    //             "codigoVenta" => "01",
    //             "serie" => "F001",
    //             "numeracion" => 2,
    //             "idSucursal" => "SC0001",
    //             "tipoDoc" => "RUC",
    //             "codigoCliente" => "6",
    //             "documento" => "10764233889",
    //             "informacion" => "luis alexander lara serna",
    //             "direccion" => "-",
    //             "usuario" => "ALEJANDRO MAGNO2",
    //             "fecha" => "2024-02-17",
    //             "hora" => "10:49:38",
    //             "fechaCorrelativo" => "2024-02-17",
    //             "correlativo" => 3,
    //             "ticketConsultaSunat" => "1708203059450",
    //             "idFormaPago" => "FP0001",
    //             "estado" => 1,
    //             "simbolo" => "S/",
    //             "codiso" => "PEN",
    //             "moneda" => "SOLES"
    //         ],
    //         "empresa" => [
    //             "documento" => "20547848307",
    //             "codigo" => "6",
    //             "razonSocial" => "EMPRESA UNIDA S.A.C.",
    //             "nombreEmpresa" => "syssoft",
    //             "usuarioSolSunat" => "MODDATOS",
    //             "claveSolSunat" => "MODDATOS",
    //             "idApiSunat" => "test-85e5b0ae-255c-4891-a595-0b98c65c9854",
    //             "claveApiSunat" => "test-Hty/M6QshYvPgItX2P0+Kw=="
    //         ],
    //         "sucursal" => [
    //             "direccion" => "AV. PROCERES DE LA INDEPENDEN NRO. 1775 INT. 307 URB. SAN HILARION LIMA LIMA SAN JUAN DE LURIGANCHO",
    //             "ubigeo" => "140111",
    //             "departamento" => "LIMA",
    //             "provincia" => "LIMA",
    //             "distrito" => "LINCE"
    //         ],
    //         "detalle" => [
    //             [
    //                 "producto" => "suerox sabor mora azul-hierbabuena 630ml",
    //                 "codigoMedida" => "NIU",
    //                 "medida" => "UNIDAD",
    //                 "categoria" => "PLASTICO",
    //                 "precio" => 7,
    //                 "cantidad" => 1,
    //                 "idImpuesto" => "IM0002",
    //                 "impuesto" => "IGV(18%)",
    //                 "codigo" => "10",
    //                 "porcentaje" => 18
    //             ],
    //             [
    //                 "producto" => "suerox sabor manzana 630 ml",
    //                 "codigoMedida" => "NIU",
    //                 "medida" => "UNIDAD",
    //                 "categoria" => "PLASTICO",
    //                 "precio" => 10,
    //                 "cantidad" => 1,
    //                 "idImpuesto" => "IM0002",
    //                 "impuesto" => "IGV(18%)",
    //                 "codigo" => "10",
    //                 "porcentaje" => 18
    //             ]
    //         ]

    //     ]);

    //     $response->assertStatus(200);
    // }

    // public function testResumenDiario(){
    //     $response = $this->get('/api/resumen/VT0003');
    //     $response->assertStatus(200);
    // }
}
