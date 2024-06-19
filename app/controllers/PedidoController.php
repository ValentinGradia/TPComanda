<?php
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $codigo_mesa = $parametros['codigo_mesa'];
        $codigo_pedido = $parametros['codigo_pedido'];
        $estado_pedido = $parametros["estado_pedido"];
        $nombre_cliente = $parametros["nombre_cliente"];
        $tiempo_preparacion = $parametros["tiempo_preparacion"];

        $pedido = new Pedido();
        $pedido->codigo_mesa = $codigo_mesa;
        $pedido->codigo_pedido = $codigo_pedido;
        $pedido->estado_pedido = $estado_pedido;
        $pedido->nombre_cliente = $nombre_cliente;
        $pedido->tiempo_preparacion = $tiempo_preparacion;

        $pedido->crearPedido();
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
        $pedido->tiempo_preparacion = $datos[3];
        $pedido->tiempo_entrega = $datos[4];
        $pedido->nombre_cliente = $datos[5];

        $pedido->crearPedido();
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

      fputcsv($archivo, array('codigo_pedido', 'codigo_mesa', 'estado', 'tiempo_preparacion', 'tiempo_entrega', 'nombre_cliente'));
      foreach($pedidos as $pedido)  
      {
        fputcsv($archivo, array($pedido->codigo_pedido, $pedido->codigo_mesa, $pedido->estado_pedido, $pedido->tiempo_preparacion,
        $pedido->tiempo_entrega,$pedido->nombre_cliente));
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
        $pedido = Pedido::obtenerPedido($codigo_pedido);
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
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $pedido = Pedido::obtenerPedido($parametros["codigo_pedido"]);

        $pedido->codigo_mesa = !empty($parametros["codigo_mesa"]) ? $parametros["codigo_mesa"] : $pedido->codigo_mesa;
        $pedido->estado_pedido = !empty($parametros["estado_pedido"]) ? $parametros["estado_pedido"] : $pedido->estado_pedido;
        $pedido->tiempo_preparacion = !empty($parametros["tiempo_preparacion"]) ? $parametros["tiempo_preparacion"] : $pedido->tiempo_preparacion;
        $pedido->tiempo_entrega = !empty($parametros["tiempo_entrega"]) ? $parametros["tiempo_entrega"] : $pedido->tiempo_entrega;
        $pedido->nombre_cliente = !empty($parametros["nombre_cliente"]) ? $parametros["nombre_cliente"] : $pedido->nombre_cliente;
        Pedido::modificarPedido($pedido);

        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $pedido = Pedido::obtenerPedido($parametros["codigo_pedido"]);

        Pedido::borrarPedido($pedido);
        
        $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}