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
}