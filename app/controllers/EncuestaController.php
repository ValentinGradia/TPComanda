<?php

require_once "./models/Encuesta.php";
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Encuesta as Encuesta;

class EncuestaController
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
        $puntaje_cocina = $parametros["puntaje_cocina"];
        $comentario = $parametros["comentario"];
        $nombre_cliente = $datos->nombre;
        $fecha_alta = date('Y-m-d H:i:s');

        $encuesta = new Encuesta();
        $encuesta->codigo_mesa = $codigo_mesa;
        $encuesta->puntaje_mesa = $puntaje_mesa;
        $encuesta->puntaje_restaurante = $puntaje_restaurante;
        $encuesta->puntaje_mozo = $puntaje_mozo;
        $encuesta->puntaje_cocina = $puntaje_cocina;
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

    public static function TraerMejoresReseñas($request, $response, $args)
    {
      $encuestas = Encuesta::all();

      $mejoresEncuestas = array();

      foreach($encuestas as $encuesta)
      {
        $puntaje_restaurante = $encuesta->puntaje_restaurante;
        $puntaje_cocina = $encuesta->puntaje_cocina;
        $puntaje_mesa = $encuesta->puntaje_mesa;
        $puntaje_mozo = $encuesta->puntaje_mozo;

        $promedio = ($puntaje_cocina + $puntaje_mesa + $puntaje_mozo + $puntaje_restaurante) / 4;

        if($promedio > 6)
        {
          array_push($mejoresEncuestas, $encuesta);
        }
      }

      $payload = json_encode(array('mejores encuestas' => $mejoresEncuestas));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

}