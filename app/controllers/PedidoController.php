<?php
require_once './models/Pedido.php';
require_once './models/DetallePedido.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';
require_once "./models/Pdf.php";

use \App\Models\Pedido as Pedido;
use \App\Models\Producto as Producto;
use \App\Models\DetallePedido as DetallePedido;
use App\Models\Mesa;
use Illuminate\Http\Request;
use Carbon\Carbon;
use \App\Models\Usuario as Usuario;
use Illuminate\Support\Arr;

class PedidoController  implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);

        $codigo_mesa = $parametros['codigo_mesa'];
        $codigo_pedido = $parametros['codigo_pedido'];
        $estado_pedido = 'en preparacion';
        $tiempo_inicio = date('Y-m-d H:i');
        $tiempo_estimado_entregado = $parametros["tiempo_estimado_entregado"];

        $json = $parametros["productos"];
        $data = (array)json_decode($json);
        $idProductos = $data["id_productos"];

        foreach($idProductos as $id)
        {
          $producto = Producto::find($id);
          $detallePedido = new DetallePedido([
            "id_producto" => $id,
            "codigo_pedido" => $codigo_pedido,
            "estado_producto" => "pendiente",
            "codigo_mesa" => $codigo_mesa,
          ]);

          $detallePedido->save();
        }
        

        $pedido = new Pedido();
        $pedido->codigo_mesa = $codigo_mesa;
        $pedido->codigo_pedido = $codigo_pedido;
        $pedido->estado_pedido = $estado_pedido;
        $pedido->tiempo_inicio = $tiempo_inicio;
        $pedido->tiempo_estimado_entregado = $tiempo_estimado_entregado;
        $pedido->id_mozo = $datos->Id_usuario;

        //cambiamos el estado de la mesa
        $mesa = Mesa::find($codigo_mesa);
        $mesa->estado_mesa = "con cliente esperando pedido";
        $mesa->save();


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
      $pedidos = Pedido::all();
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

    public static function TraerCancelados($request, $response, $args)
    {
      $pedidos = Pedido::withTrashed()->whereNotNull('fecha_baja')->get();

      $payload = json_encode(array("Pedidos cancelados: " => $pedidos));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');
    }
    public static function Estadisticas30Dias($request, $response, $args)
    {
      $fechaActual = Carbon::now()->format('Y-m-d H:i');
      
      $fechaHace30Dias = Carbon::now()->subDays(30)->format('Y-m-d H:i');
  
      $pedidos= Pedido::whereBetween('tiempo_entregado', [$fechaHace30Dias, $fechaActual])->get();

      $acumulador = 0;
      foreach ($pedidos as $pedido)
      {
          $tiempo_entregado = new DateTime($pedido->tiempo_entregado);
          if($tiempo_entregado >= $fechaHace30Dias)
          {
              $acumulador += $pedido->cobro;
          }
      }
      $promedio = $acumulador / 30;
  
      $payload = json_encode(array("El cobro promedio de los ultimos 30 dias fue: " => $promedio));
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerPedidosNoEntregadosATiempo($request, $response, $args)
    {
      $pedidos = Pedido::all();

      $pedidosNoEntregadosATiempo = array();

      foreach($pedidos as $pedido)
      {
        $tiempo_estimado_entregado = $pedido->tiempo_estimado_entregado;
        
        $tiempo_entregado = $pedido->tiempo_entregado;

        //validamos que el pedido haya sido entregado
        if($tiempo_entregado !== null)
        {
          if($tiempo_entregado > $tiempo_estimado_entregado)
          {
            array_push($pedidosNoEntregadosATiempo, $pedido);
          }

        }

      }

      $payload = json_encode(array("lista de pedidos que no fueron entregados en tiempo estipulado" => $pedidosNoEntregadosATiempo));
      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public static function TraerTiempoRestante($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $codigo_pedido = $params['codigo_pedido'];
        $codigo_mesa = $params['codigo_mesa'];
        $pedido = Pedido::where('codigo_pedido',$codigo_pedido)->where('codigo_mesa',$codigo_mesa)->first();

        $fechaHoraInicio = $pedido->tiempo_inicio;
        $fechaHoraInicioObj = new DateTime($fechaHoraInicio);
        $minutosInicio = $fechaHoraInicioObj->format('i');

        
        $fechaHoraEstimado = $pedido->tiempo_estimado_entregado;
        $fechaHoraEstimadoObj = new DateTime($fechaHoraEstimado);
        $minutosEstimado = $fechaHoraEstimadoObj->format('i');

        $demora = (int)$minutosEstimado - (int)$minutosInicio;
        
        $payload = json_encode(array("Tiempo demora es de" => $demora . ' minutos'));

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

    public static function ExportarPDF($path = "./pedidos.pdf")
    {
        $pdf = new PDF();
        $pdf->AddPage();
        
        $pedidos = Pedido::all();  
        foreach ($pedidos as $pedido) 
        {
            $pdf->ChapterTitle($pedido->codigo_pedido);
            $pdf->ChapterBody($pedido->nombre_cliente . " " .  $pedido->codigo_mesa);
            $pdf->Ln();
        }
  
        $pdf->Output($path, 'F');
    }

    public function DescargarPDF($request, $response, $args)
    {
        self::ExportarPDF();
        $payload = json_encode(array("mensaje" => "Pedidos exportados a pdf con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
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