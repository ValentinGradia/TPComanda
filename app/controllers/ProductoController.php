<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $id_producto = $parametros['id_producto'];
        $tipo = $parametros['tipo'];
        $nombre = $parametros["nombre"];
        $codigo_pedido = $parametros["codigo_pedido"];

        $producto = new Producto();
        $producto->id_producto = $id_producto;
        $producto->tipo = $tipo;
        $producto->nombre = $nombre;
        $producto->codigo_pedido = $codigo_pedido;

        $producto->CrearProducto();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id_producto = $args['id_producto'];
        $producto = Producto::ObtenerProducto($id_producto);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::ObtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        // $parametros = $request->getParsedBody();

        // $nombre = $parametros['nombre'];
        // Usuario::modificarUsuario($nombre);

        // $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        // $response->getBody()->write($payload);
        // return $response
        //   ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        // $parametros = $request->getParsedBody();

        // $usuarioId = $parametros['usuarioId'];
        // Usuario::borrarUsuario($usuarioId);

        // $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        // $response->getBody()->write($payload);
        // return $response
        //   ->withHeader('Content-Type', 'application/json');
    }
}