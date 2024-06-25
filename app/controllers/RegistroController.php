<?php

use \App\Models\Registro as Registro;

class RegistroController
{
    public static function TraerTodos($request, $response, $args)
    {
        $registros = Registro::all();

        $payload = json_encode(array("registros" => $registros));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}