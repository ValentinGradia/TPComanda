<?php

require_once './models/Venta.php';
require_once './controllers/ProductoController.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Venta as Venta;
use \App\Models\Pedido as Pedido;
use App\Models\Producto;
use App\Models\Mesa as Mesa;

class VentaController
{
    public function CargarUno($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);
        $codigo_mesa = $pedido->codigo_mesa;
        $mesa = Mesa::find($codigo_mesa);
        $mesa->estado_mesa = "con cliente pagando";
        $mesa->save();

        $productos = ProductoController::TraerPorCodigoMesa($codigo_mesa);

        $cobro = 0;

        
        foreach($productos as $producto)
        {

            $cobro += $producto->cantidad * $producto->precio;
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

        $contador = [];

        foreach ($ventas as $venta) 
        {
            $codigo_mesa = $venta->codigo_mesa;

            if (isset($contador[$codigo_mesa])) 
            {
                $contador[$codigo_mesa]++;
            } 
            else
            {
                $contador[$codigo_mesa] = 1;
            }
        }

        $maxOcurrencias = 0;
        $codigoMesaMasRepetido = null;

        foreach ($contador as $codigo => $ocurrencias) 
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

        $arrayMesasMin = explode(',',$codigoMesaMasRepetido);

        $payload = json_encode(array("Las mesas que mas se usaron fueron" => $codigoMesaMasRepetido));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');

        
    }

    public static function TraerMesaMenosUsada($request, $response, $args)
    {
        $ventas = Venta::all();

        $contador = [];

        foreach ($ventas as $venta) 
        {
            $codigo_mesa = $venta->codigo_mesa;

            if (isset($contador[$codigo_mesa])) 
            {
                $contador[$codigo_mesa]++;
            } 
            else
            {
                $contador[$codigo_mesa] = 1;
            }
        }

        $minOcurrencias = PHP_INT_MAX;
        $codigoMesaMenosRepetido = null;

        foreach ($contador as $codigo => $ocurrencias) 
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

        $arrayMesasMin = explode(',',$codigoMesaMenosRepetido);


        $payload = json_encode(array("Las mesas que menos se usaron fueron" => $arrayMesasMin));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}