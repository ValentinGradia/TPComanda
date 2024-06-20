<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Pedido as Pedido;

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo_mesa = $parametros['codigo_mesa'];
        $codigo_pedido = $parametros['codigo_pedido'];
        $estado_pedido = $parametros["estado_pedido"];
        $tiempo_inicio = date('Y-m-d H:i');
        $tiempo_estimado_entregado = $parametros["tiempo_estimado_entregado"];
        $nombre_cliente = $parametros["nombre_cliente"];

        $pedido = new Pedido();
        $pedido->codigo_mesa = $codigo_mesa;
        $pedido->codigo_pedido = $codigo_pedido;
        $pedido->estado_pedido = $estado_pedido;
        $pedido->tiempo_inicio = $tiempo_inicio;
        $pedido->tiempo_estimado_entregado = $tiempo_estimado_entregado;
        $pedido->nombre_cliente = $nombre_cliente;


        $pedido->save();
        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CargarCsv($request, $response, $args)
    {
      $params = $request->getUploadedFiles();
      $archivo = fopen($params["file"]->getFilePath(), 'r');

      while(($datos = fgetcsv($archivo)) !== false)
      {
        $pedido = new Pedido();
        $pedido->codigo_pedido = $datos[0];
        $pedido->codigo_mesa = $datos[1];
        $pedido->estado_pedido = $datos[2];
        $pedido->tiempo_inicio = $datos[3];
        $pedido->tiempo_estimado_entregado = $datos[4];
        $pedido->nombre_cliente = $datos[5];

        $pedido->save();
      }

      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Lista cargada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public static function DescargarCsv($request, $response, $args)
    {
      $pedidos = Pedido::obtenerTodos();
      $ruta = "./Csv/pedidos.csv";

      $archivo = fopen($ruta, 'w');

      fputcsv($archivo, array('codigo_pedido', 'codigo_mesa', 'estado', 'tiempo_inicio', 'tiempo_estimado_entregado','tiempo_entregado', 'nombre_cliente',
      'cobro','fecha_baja'));
      foreach($pedidos as $pedido)  
      {
        fputcsv($archivo, array($pedido->codigo_pedido, $pedido->codigo_mesa, $pedido->estado_pedido, $pedido->tiempo_inicio,$pedido->tiempo_estimado_entregado,
        $pedido->tiempo_entregado,$pedido->nombre_cliente,$pedido->nombre_cliente,$pedido->fecha_baja));
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
        $codigo_pedido = $params['codigo_pedido'];
        $pedido = Pedido::find($codigo_pedido);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTiempoRestante($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $codigo_pedido = $params['codigo_pedido'];
        $pedido = Pedido::obtenerPedido($codigo_pedido);
        $payload = json_encode($pedido);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::all();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $pedido = Pedido::find($parametros["codigo_pedido"]);

        $pedido->codigo_mesa = !empty($parametros["codigo_mesa"]) ? $parametros["codigo_mesa"] : $pedido->codigo_mesa;
        $pedido->estado_pedido = !empty($parametros["estado_pedido"]) ? $parametros["estado_pedido"] : $pedido->estado_pedido;
        $pedido->tiempo_inicio = !empty($parametros["tiempo_inicio"]) ? $parametros["tiempo_inicio"] : $pedido->tiempo_inicio;
        $pedido->tiempo_estimado_entregado = !empty($parametros["tiempo_estimado_entregado"]) ? $parametros["tiempo_estimado_entregado"] : $pedido->tiempo_estimado_entregado;
        $pedido->tiempo_entregado = !empty($parametros["tiempo_entregado"]) ? $parametros["tiempo_entregado"] : $pedido->tiempo_entregado;
        $pedido->nombre_cliente = !empty($parametros["nombre_cliente"]) ? $parametros["nombre_cliente"] : $pedido->nombre_cliente;
        $pedido->cobro = !empty($parametros["cobro"]) ? $parametros["cobro"] : $pedido->cobro;
        $pedido->fecha_baja = !empty($parametros["fecha_baja"]) ? $parametros["fecha_baja"] : $pedido->fecha_baja;
        $pedido->save();

        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $pedido = Pedido::find($parametros["codigo_pedido"]);

        $pedido->fecha_baja = date('Y-m-d H:i:s');
        $pedido->delete();
        
        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}