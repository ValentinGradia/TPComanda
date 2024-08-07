<?php
require_once './models/Usuario.php';
require_once './models/DetallePedido.php';
require_once './interfaces/IApiUsable.php';
require_once "./models/Pdf.php";

use App\Models\DetallePedido as DetallePedido;
use \App\Models\Usuario as Usuario;
use \App\Models\Producto as Producto;
use \App\Models\Pedido as Pedido;

class UsuarioController implements IApiUsable
{

  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];
    $estado = 'activo';

    $usr = new Usuario();
    $usr->nombre = $nombre;
    $usr->clave = password_hash($clave, PASSWORD_DEFAULT);
    $usr->rol = $rol;
    $usr->estado = $estado;
    $usr->save(); //metodo save seria como el insert en sql

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function CargarCsv($request, $response, $args)
  {
    $params = $request->getUploadedFiles();
    $archivo = fopen($params["file"]->getFilePath(), 'r');

    while(($datos = fgetcsv($archivo)) !== false)
    {
      $usuario = new Usuario();
      $usuario->nombre = $datos[0];
      $usuario->clave = password_hash($datos[1],PASSWORD_DEFAULT);
      $usuario->rol = $datos[2];
      $usuario->estado = $datos[3];

      $usuario->save();
    }

    fclose($archivo);
    $payload = json_encode(array("mensaje" => "Lista cargada con exito"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function DescargarCsv($request, $response, $args)
  {
    $usuarios = Usuario::all();
    $ruta = "./Csv/usuarios.csv";

    $archivo = fopen($ruta, 'w');

    fputcsv($archivo, array('Id', 'nombre', 'clave', 'rol', 'estado', 'fecha_baja'));
    foreach($usuarios as $usuario)  
    {
      fputcsv($archivo, array($usuario->id_usuario, $usuario->nombre, $usuario->clave, $usuario->rol, $usuario->estado,
      $usuario->fecha_baja));
    }

    fclose($archivo);
    $payload = json_encode(array("mensaje" => "Archivo cargado con exito"));
    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
    
  }

  public function TraerUno($request, $response, $args)
  {
    $parametros = $request->getQueryParams();
    $id_usuario = $parametros['id_usuario'];

    $usuario = Usuario::find($id_usuario); //el find se usa exclusivamente para buscar por claves primarias (id)
    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }


  public static function TraerOperaciones($request, $response, $args)
  {
    $params = $request->getQueryParams();
    $id_usuario = $params["id_usuario"];
    $usuario = Usuario::find($id_usuario);

    $operaciones = 0;
    if($usuario->rol !== "mozo")
    {
      $productos = DetallePedido::where('id_empleado',$id_usuario)->get();
      $operaciones = count($productos);
    }
    else
    {
      $pedidos = Pedido::where('id_mozo',$id_usuario)->get();
      $operaciones = count($pedidos);
    }

    $payload = json_encode(array("La cantidad de operaciones que hizo el usuario ingresado fue: " => $operaciones));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function TraerOperacionesPorSector($request, $response, $args)
  {
    $params = $request->getQueryParams();
    $sector = $params["sector"];

    $operaciones = array();

    switch($sector)
    {
      case "cocina":
        $productos = Producto::where('tipo','comida')->get();

        $ids = array();

        foreach($productos as $producto)
        {
          array_push($ids, $producto->id_producto);
        }

        $productosSector = DetallePedido::whereIn('id_producto',$ids)->get();

        foreach($productosSector as $producto)
        {
          $id_empleado = $producto->id_empleado;
          if(isset($operaciones[$id_empleado]))
          {
            $operaciones[$id_empleado]++;
          }
          else
          {
            $operaciones[$id_empleado] = 1;
          }
        }
        break;
      case "barra":
          $productos = Producto::where('tipo','trago')->get();

          $ids = array();

        foreach($productos as $producto)
        {
          
          array_push($ids, $producto->id_producto);
        }

        $productosSector = DetallePedido::whereIn('id_producto',$ids)->get();

        foreach($productosSector as $producto)
        {
          $id_empleado = $producto->id_empleado;
          if(isset($operaciones[$id_empleado]))
          {
            $operaciones[$id_empleado]++;
          }
          else
          {
            $operaciones[$id_empleado] = 1;
          }
        }
        break;
      case "patio trasero":
          $productos = Producto::where('tipo','cerveza')->get();
          $ids = array();
          foreach($productos as $producto)
          {
            
            array_push($ids, $producto->id_producto);
          }

          $productosSector = DetallePedido::whereIn('id_producto',$ids)->get();

          foreach($productosSector as $producto)
          {
            $id_empleado = $producto->id_empleado;
            if(isset($operaciones[$id_empleado]))
            {
              $operaciones[$id_empleado]++;
            }
            else
            {
              $operaciones[$id_empleado] = 1;
            }
          }
          break;
    }

    $payload = json_encode(array("Operaciones por sector (id_empleado : cantidad operaciones) " => $operaciones));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');


  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::all(); //el all te trae todos los usuarios
    $payload = json_encode(array("listaUsuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public static function ExportarPDF($path = "./usuarios.pdf")
  {
      $pdf = new PDF();
      $pdf->AddPage();
      
      $usuarios = Usuario::all();

      foreach ($usuarios as $usuario) {
          $pdf->ChapterTitle($usuario->nombre);
          $pdf->ChapterBody($usuario->email . " " .  $usuario->rol . " " . $usuario->estado);
          $pdf->Ln();
      }

      $pdf->Output($path, 'F');
  }

  public function DescargarPDF($request, $response, $args)
  {
      self::ExportarPDF();
      $payload = json_encode(array("mensaje" => "Usuarios exportados a pdf con exito"));

      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $id_usuario = $parametros["id_usuario"];

    // Conseguimos el objeto con where
    //$usuario = Usuario::where('id_usuario', $id_usuario)->first(); //where se usa para filtros mas complejos
    //con find
    $usuario = Usuario::find($id_usuario);

    if ($usuario !== null) {
      // Seteamos un nuevo usuario
      $usuario->nombre = !empty($parametros["nombre"]) ? $parametros["nombre"] : $usuario->nombre;
      $usuario->clave = !empty($parametros["clave"]) ? password_hash($parametros["clave"],PASSWORD_DEFAULT) : $usuario->clave;
      $usuario->rol = !empty($parametros["rol"]) ? $parametros["rol"] : $usuario->rol;
      // Guardamos en base de datos
      $usuario->save();
      $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    } else {
      $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_usuario = $parametros["id_usuario"];

    $usuario = Usuario::find($id_usuario);
    $usuario->estado = "inactivo";
    $usuario->save();

    if($usuario !== null)
    {
      $usuario->estado = "inactivo";
      $usuario->fecha_baja = date('Y-m-d H:i:s');
      $usuario->delete();
    }
    
    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}