<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductoController extends Producto implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $tipo = $parametros['tipo'];
        $nombre = $parametros["nombre"];
        $precio = $parametros["precio"];
        $cantidad = $parametros["cantidad"];
        $estado_producto = $parametros["estado_producto"];
        $codigo_mesa = $parametros["codigo_mesa"];

        $producto = new Producto();
        $producto->tipo = $tipo;
        $producto->nombre = $nombre;
        $producto->precio = $precio;
        $producto->cantidad = $cantidad;
        $producto->estado_producto = $estado_producto;
        $producto->codigo_mesa = $codigo_mesa;

        //$producto->CrearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CargarCsv($request, $response, $args)
    {
      $params = $request->getUploadedFiles();
      $archivo = fopen($params["file"]->getFilePath(), 'r');

      while(($datos = fgetcsv($archivo)) !== false)
      {
        $producto = new Producto();
        $producto->tipo = $datos[0];
        $producto->nombre = $datos[1];
        $producto->precio = $datos[2];
        $producto->cantidad = $datos[3];
        $producto->estado_producto = $datos[4];
        $producto->codigo_mesa = $datos[5];

        $producto->CrearProducto();
      }

      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Lista cargada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public static function DescargarCsv($request, $response, $args)
    {
      $productos = Producto::obtenerTodos();
      $ruta = "./Csv/productos.csv";

      $archivo = fopen($ruta, 'w');

      fputcsv($archivo, array('Id', 'tipo', 'nombre', 'precio', 'cantidad', 'estado', 'codigo_mesa'));
      foreach($productos as $producto)  
      {
        fputcsv($archivo, array($producto->id_producto, $producto->tipo, $producto->nombre, $producto->precio, $producto->cantidad,
        $producto->estado_producto,$producto->codigo_mesa));
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
        $id_producto = $params['id_producto'];
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
        $parametros = $request->getParsedBody();

        $producto = Producto::ObtenerProducto($parametros["id_producto"]);

        $producto->tipo = !empty($parametros["tipo"]) ? $parametros["tipo"] : $producto->tipo;
        $producto->nombre = !empty($parametros["nombre"]) ? $parametros["nombre"] : $producto->nombre;
        $producto->precio = !empty($parametros["precio"]) ? $parametros["precio"] : $producto->precio;
        $producto->cantidad = !empty($parametros["cantidad"]) ? $parametros["cantidad"] : $producto->cantidad;
        $producto->estado_producto = !empty($parametros["estado_producto"]) ? $parametros["estado_producto"] : $producto->estado_producto;
        $producto->codigo_mesa = !empty($parametros["codigo_mesa"]) ? $parametros["codigo_mesa"] : $producto->codigo_mesa;

        Producto::modificarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $producto = Producto::ObtenerProducto($parametros["id_producto"]);
        Producto::borrarProducto($producto);

        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}