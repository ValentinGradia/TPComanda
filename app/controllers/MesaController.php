<?php
require_once "./models/Mesa.php";
require_once './interfaces/IApiUsable.php';

use \App\Models\Mesa as Mesa;

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
        $mesa->save();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CargarCsv($request, $response, $args)
    {
      $params = $request->getUploadedFiles();
      $archivo = fopen($params["file"]->getFilePath(), 'r');

      while(($datos = fgetcsv($archivo)) !== false)
      {
        $mesa = new Mesa();
        $mesa->codigo_mesa = $datos[0];
        $mesa->estado_mesa = $datos[1];

        $mesa->save();
      }

      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Lista cargada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public static function DescargarCsv($request, $response, $args)
    {
      $mesas = Mesa::obtenerTodos();
      $ruta = "./Csv/mesa.csv";

      $archivo = fopen($ruta, 'w');

      fputcsv($archivo, array('codigo_mesa','estado_mesa'));
      foreach($mesas as $mesa)  
      {
        fputcsv($archivo, array($mesa->codigo_mesa,$mesa->esado_mesa));
      }

      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Archivo cargado con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
      
    }

    public function TraerUno($request, $response, $args)
    {
      $params = $request->getQueryParams();
      $codigo_mesa = $params['codigo_mesa'];
      $mesa = Mesa::find($codigo_mesa);
      $payload = json_encode($mesa);

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::all();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::ObtenerMesa($parametros["codigo_mesa"]);

        $mesa->estado_mesa = !empty($parametros["estado_mesa"]) ? $parametros["estado_mesa"] : $mesa->estado_mesa;
        $mesa->save();

        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $mesa = Mesa::find($parametros["codigo_mesa"]);
        $mesa->fecha_baja = date('Y-m-d H:i:s');
        $mesa->delete();

        $payload = json_encode(array("mensaje" => "Mesa eliminada con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}

