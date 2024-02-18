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


    public function testGuiaRemisionRoute()
    {
        $response = $this->get('/api/guiaremision/GR0004');

        $response->assertStatus(200);

        // Verifica que la respuesta JSON sea exactamente la esperada
        // $response->assertExactJson([
        //     "state" => true,
        //     "accept" => true,
        //     "code" => "0",
        //     "description" => "ACEPTADA"
        // ]);
    }

    public function testFacturaRoute()
    {
        $response = $this->get('/api/boleta/VT0007');

        $response->assertStatus(200);

        // Verifica que la respuesta JSON sea exactamente la esperada
        // $response->assertExactJson([
        //     "state" => true,
        //     "accept" => true,
        //     "code" => "0",
        //     "description" => "La Factura numero F001-2, ha sido aceptada"
        // ]);
    }

    public function testConsultaComprobanteRoute(){
        $response = $this->get('/api/consultar/10764233889/LUIS2023/Qz0966lb/01/F001/1');

        error_log(json_encode($response));

        $response->assertStatus(200);
    }
}
