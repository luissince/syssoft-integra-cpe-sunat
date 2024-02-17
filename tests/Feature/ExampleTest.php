<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

    /**
     * Prueba para la ruta de la guía de remisión.
     *
     * @return void
     */
    public function testGuiaRemisionRoute()
    {
        // Envía una solicitud GET a la ruta de la guía de remisión
        $response = $this->get('/api/guiaremision/GR0004');

        // Verifica que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);

        // Verifica que la respuesta JSON sea exactamente la esperada
        $response->assertExactJson([
            "state" => true,
            "accept" => true,
            "code" => "0",
            "description" => "ACEPTADA"
        ]);
    }

    //curl  http://localhost:9000/api/boleta/VT0007
    public function testFacturaRoute()
    {
        // Envía una solicitud GET a la ruta de la guía de remisión
        $response = $this->get('/api/boleta/VT0007');

        // Verifica que la respuesta sea exitosa (código 200)
        $response->assertStatus(200);

        // Verifica que la respuesta JSON sea exactamente la esperada
        $response->assertExactJson([
            "state" => true,
            "accept" => true,
            "code" => "0",
            "description" => "La Factura numero F001-2, ha sido aceptada"
        ]);

        // para subir
    }
}
