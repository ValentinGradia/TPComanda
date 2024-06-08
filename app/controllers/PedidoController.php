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