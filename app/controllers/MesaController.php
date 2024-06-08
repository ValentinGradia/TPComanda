<?php
require_once "./models/Mesa.php";
require_once './interfaces/IApiUsable.php';

class MesaController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo_mesa = $parametros['codigo_mesa'];
        $estado_mesa = $parametros['estado_mesa'];

        $mesa = new Mesa();
        $mesa->codigo_mesa = $codigo_mesa;
        $mesa->estado_mesa = $estado_mesa;
        $mesa->CrearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {

    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::ObtenerMesa($parametros["codigo_mesa"]);
        Mesa::modificarMesa($mesa);

        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::ObtenerMesa($parametros["codigo_mesa"]);
        Mesa::modificarMesa($mesa);

        $payload = json_encode(array("mensaje" => "Mesa eliminada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

