<?php

require_once './models/Venta.php';
require_once './models/DetallePedido.php';
require_once './controllers/ProductoController.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Venta as Venta;
use \App\Models\Pedido as Pedido;
use App\Models\Producto as Producto;
use App\Models\DetallePedido as DetallePedido;
use App\Models\Mesa as Mesa;
use App\Models\Encuesta as Encuesta;

use function PHPSTORM_META\map;

class VentaController
{
    public function CargarUno($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);
        $codigo_mesa = $pedido->codigo_mesa;

        $detallePedidos = DetallePedido::where('codigo_pedido',$codigo_pedido)->get();

        $cobro = 0;

        foreach($detallePedidos as $productos)
        {
            $producto = Producto::find($productos->id_producto);

            $cobro += $producto->precio * $producto->cantidad;
        }

        $pedido->cobro = $cobro;
        $pedido->save();


        $venta = new Venta([
            'codigo_pedido' => $codigo_pedido,
            'codigo_mesa' => $codigo_mesa,
            'cobro' => $cobro,
            'fecha_venta' => date('Y-m-d H:i:s')
        ]);

        $venta->save();

        $payload = json_encode(array("mensaje" => "Venta creada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

        
    }

    public static function TraerMesaMasUsada($request, $response, $args)
    {
        $ventas = Venta::all();

        $mesasUsos = $ventas->groupBy('codigo_mesa')->map(function($ventas){
            return $ventas->count();
        });

        $maxOcurrencias = 0;
        $codigoMesaMasRepetido = null;

        foreach ($mesasUsos as $codigo => $ocurrencias) 
        {
            if ($ocurrencias > $maxOcurrencias)
            {
                $maxOcurrencias = $ocurrencias;
                $codigoMesaMasRepetido = $codigo;
            }
            else if($ocurrencias == $maxOcurrencias)
            {
                $codigoMesaMasRepetido = $codigoMesaMasRepetido . ",$codigo";
            }
        }

        $payload = json_encode(array("Las mesas que mas se usaron fueron" => $codigoMesaMasRepetido));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

        
    }

    public static function TraerMesaMenosUsada($request, $response, $args)
    {
        $ventas = Venta::all();

        $mesasUsos = $ventas->groupBy('codigo_mesa')->map(function($ventas){
            return $ventas->count();
        });

        $mesasQueFueronUsadas = array_keys($mesasUsos->toArray());

        $mesaNoUsada = Mesa::whereNotIn('codigo_mesa',$mesasQueFueronUsadas)->first();

        if($mesaNoUsada == null)
        {

            $minOcurrencias = PHP_INT_MAX;
            $codigoMesaMenosRepetido = null;
    
            foreach ($mesasUsos as $codigo => $ocurrencias) 
            {
                if ($ocurrencias < $minOcurrencias)
                {
                    $minOcurrencias = $ocurrencias;
                    $codigoMesaMenosRepetido = $codigo;
                }
                else if($ocurrencias == $minOcurrencias)
                {
                    $codigoMesaMenosRepetido = $codigoMesaMenosRepetido . ",$codigo";
                }
            }

            $payload = json_encode(array("Las mesas que menos se usaron fueron" => $codigoMesaMenosRepetido));
        }
        else
        {
            $payload = json_encode(array("Las mesas que menos se usaron fueron" => $mesaNoUsada));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerMesaMasFacturo($request, $response, $args)
    {
        $ventas = Venta::all();

        $mesasFacturas = $ventas->groupBy('codigo_mesa')->map(function ($ventas) {
            return $ventas->sum('cobro');
        });

        $maxCobro = 0;
        $codigoMesaMasFacturo = null;

        foreach ($mesasFacturas as $codigo => $cobros) 
        {
            if ($cobros > $maxCobro)
            {
                $maxCobro = $cobros;
                $codigoMesaMasFacturo = $codigo;
            }
            else if($cobros == $maxCobro)
            {
                $codigoMesaMasFacturo = $codigoMesaMasFacturo . ",$codigo";
            }
        }

        $payload = json_encode(array("Las mesas que mas facturaron fueron" => $codigoMesaMasFacturo));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }

    public static function TraerMesaMenosFacturo($request, $response, $args)
    {
        $ventas = Venta::all();

        $mesasFacturas = $ventas->groupBy('codigo_mesa')->map(function ($ventas) {
            return $ventas->sum('cobro');
        });

        $minCobro = PHP_INT_MAX;
        $codigoMesaMenosFacturo = null;

        foreach ($mesasFacturas as $codigo => $cobros) 
        {
            if ($cobros < $minCobro)
            {
                $minCobro = $cobros;
                $codigoMesaMenosFacturo = $codigo;
            }
            else if($cobros == $minCobro)
            {
                $codigoMesaMenosFacturo = $codigoMesaMenosFacturo . ",$codigo";
            }
        }

        $payload = json_encode(array("Las mesas que menos facturaron fueron" => $codigoMesaMenosFacturo));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }

    public static function TraerMesaMayorCobro($request, $response, $args)
    {
        $ventas = Venta::all();

        $cobrosTotal = $ventas->groupBy('codigo_mesa')->map(function($ventas){
            return $ventas->pluck('cobro');
        }); //pluck obtiene los valores de todos los cobros por separado

        $mayorCobro = PHP_INT_MIN;
        $codigo = null;
        
        foreach($cobrosTotal as $codigo_mesa => $cobros)
        {
            foreach($cobros as $cobro)
            {
                if($cobro > $mayorCobro)
                {
                    $mayorCobro = $cobro;
                    $codigo = $codigo_mesa;
                }
                else if($cobro == $mayorCobro)
                {
                    $codigo = $codigo . ",$codigo_mesa";
                }
            }
        }
    }

    public static function TraerMesaMenorCobro($request, $response, $args)
    {
        $ventas = Venta::all();

        $cobrosTotal = $ventas->groupBy('codigo_mesa')->map(function($ventas){
            return $ventas->pluck('cobro');
        }); //pluck obtiene los valores de todos los cobros por separado

        $minCobro = PHP_INT_MAX;
        $codigo = null;
        
        foreach($cobrosTotal as $codigo_mesa => $cobros)
        {
            foreach($cobros as $cobro)
            {
                if($cobro < $minCobro)
                {
                    $minCobro = $cobro;
                    $codigo = $codigo_mesa;
                }
                else if($cobro == $minCobro)
                {
                    $codigo = $codigo . ",$codigo_mesa";
                }
            }
        }


        $payload = json_encode(array("las mesas que tuvieron menor importe fueron" => $codigo));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerMesaFacturadaEntreFechas($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $fechaMin = $params["fechaMin"];
        $fechaMax = $params["fechaMax"];

        $ventas = Venta::whereBetween('fecha_venta', [$fechaMin, $fechaMax])->get();

        $payload = json_encode(array("facturas de mesas entre fechas dadas" => $ventas));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

    }

    public static function TraerMesasMejoresReseñas($request, $response, $args)
    {
        $encuestas = Encuesta::where('puntaje_mesa','>',6)->get();

        $payload = json_encode(array("mesas con mejor reseña" => $encuestas));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function TraerMesasPeoresReseñas($request, $response, $args)
    {
        $encuestas = Encuesta::where('puntaje_mesa','<',5)->get();

        $payload = json_encode(array("mesas con peor reseña" => $encuestas));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}