<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';
require_once "./models/Pdf.php";

use \App\Models\Producto as Producto;
use \App\Models\Mesa as Mesa;

class ProductoController implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $tipo = $parametros['tipo'];
        $nombre = $parametros["nombre"];
        $precio = $parametros["precio"];
        $cantidad = $parametros["cantidad"];

        $producto = new Producto();
        $producto->nombre = $nombre;
        $producto->tipo = $tipo;
        $producto->cantidad = $cantidad;
        $producto->precio = $precio;

        

        $producto->save();

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
        $producto->nombre = $datos[1];
        $producto->tipo = $datos[0];
        $producto->cantidad = $datos[3];
        $producto->precio = $datos[2];

        $producto->save();
      }

      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Lista cargada con exito"));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public static function DescargarCsv($request, $response, $args)
    {
      $productos = Producto::all();
      $ruta = "./Csv/productos.csv";

      $archivo = fopen($ruta, 'w');

      fputcsv($archivo, array('Id', 'nombre', 'tipo', 'cantidad', 'precio'));
      foreach($productos as $producto)  
      {
        fputcsv($archivo, array($producto->id_producto, $producto->nombre, $producto->tipo, $producto->cantidad, $producto->precio));
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
        $producto = Producto::find($id_producto);
        $payload = json_encode($producto);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }


    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::all();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public static function ExportarPDF($path = "./productos.pdf")
    {
        $pdf = new PDF();
        $pdf->AddPage();
        
        $productos = Producto::all();
  
        foreach ($productos as $producto) {
            $pdf->ChapterTitle($producto->nombre);
            $pdf->ChapterBody($producto->tipo . " " .  $producto->precio);
            $pdf->Ln();
        }
  
        $pdf->Output($path, 'F');
    }

    public function DescargarPDF($request, $response, $args)
    {
        self::ExportarPDF();
        $payload = json_encode(array("mensaje" => "Productos exportados a pdf con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $producto = Producto::find($parametros["id_producto"]);

        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);

        if($producto !== null)
        {
          $producto->tipo = !empty($parametros["tipo"]) ? $parametros["tipo"] : $producto->tipo;
          $producto->nombre = !empty($parametros["nombre"]) ? $parametros["nombre"] : $producto->nombre;
          $producto->precio = !empty($parametros["precio"]) ? $parametros["precio"] : $producto->precio;
          $producto->cantidad = !empty($parametros["cantidad"]) ? $parametros["cantidad"] : $producto->cantidad;
  
          $producto->save();
  
          $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        }
        else{$payload = json_encode(array("mensaje" => "Producto no encontrado"));}

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $producto = Producto::find($parametros["id_producto"]);

        if($producto !== null)
        {
          $producto->delete();
          $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
        }
        else{$payload = json_encode(array("mensaje" => "Producto no encontrado"));}

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}