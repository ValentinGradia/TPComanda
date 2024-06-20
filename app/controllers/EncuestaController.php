<?php

require_once "./models/Encuesta.php";
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Encuesta as Encuesta;

class EncuestaController extends Encuesta implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);

        $codigo_mesa = $parametros["codigo_mesa"];
        $puntaje_mesa = $parametros['puntaje_mesa'];
        $puntaje_restaurante = $parametros['puntaje_restaurante'];
        $puntaje_mozo = $parametros["puntaje_mozo"];
        $puntaje_cocinero = $parametros["puntaje_cocinero"];
        $comentario = $parametros["comentario"];
        $nombre_cliente = $datos->nombre;
        $fecha_alta = date('Y-m-d H:i:s');

        $encuesta = new Encuesta();
        $encuesta->codigo_mesa = $codigo_mesa;
        $encuesta->puntaje_mesa = $puntaje_mesa;
        $encuesta->puntaje_restaurante = $puntaje_restaurante;
        $encuesta->puntaje_mozo = $puntaje_mozo;
        $encuesta->puntaje_cocinero = $puntaje_cocinero;
        $encuesta->comentario = $comentario;
        $encuesta->nombre_cliente = $nombre_cliente;
        $encuesta->fecha_alta = $fecha_alta;


        $encuesta->save();
        $payload = json_encode(array("mensaje" => "Encuesta creada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $id_encuesta = $params['id_encuesta'];
        $encuesta = Encuesta::find($id_encuesta);
        $payload = json_encode($encuesta);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Encuesta::all();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $encuesta = encuesta::find($parametros["id_registro"]);

        $encuesta->codigo_mesa = !empty($parametros["codigo_mesa"]) ? $parametros["codigo_mesa"] : $encuesta->codigo_mesa;
        $encuesta->puntaje_mesa  = !empty($parametros["puntaje_mesa"]) ? $parametros["puntaje_mesa"] : $encuesta->puntaje_mesa;
        $encuesta->puntaje_restaurante = !empty($parametros["puntaje_restaurante"]) ? $parametros["puntaje_restaurante"] : $encuesta->puntaje_restaurante;
        $encuesta->puntaje_mozo = !empty($parametros["puntaje_mozo"]) ? $parametros["puntaje_mozo"] : $encuesta->puntaje_mozo;
        $encuesta->puntaje_cocinero = !empty($parametros["puntaje_cocinero"]) ? $parametros["puntaje_cocinero"] : $encuesta->puntaje_cocinero;
        $encuesta->comentario = !empty($parametros["comentario"]) ? $parametros["comentario"] : $encuesta->comentario;
        $encuesta->nombre_cliente = !empty($parametros["nombre_cliente"]) ? $parametros["nombre_cliente"] : $encuesta->nombre_cliente;
        $encuesta->fecha_alta = !empty($parametros["fecha_alta"]) ? $parametros["fecha_alta"] : $encuesta->fecha_alta;
        $encuesta->fecha_baja = !empty($parametros["fecha_baja"]) ? $parametros["fecha_baja"] : $encuesta->fecha_baja;
        $encuesta->save();

        $payload = json_encode(array("mensaje" => "Encuesta modificada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $pedido = Encuesta::find($parametros["id_registro"]);

        $pedido->fecha_baja = date('Y-m-d H:i:s');
        $pedido->delete();
        
        $payload = json_encode(array("mensaje" => "Encuesta borrada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}