<?php
require_once './models/DetallePedido.php';
require_once './interfaces/IApiUsable.php';
require_once "./models/Pdf.php";

use \App\Models\DetallePedido as DetallePedido;
use \App\Models\Producto as Producto;
use \App\Models\Pedido as Pedido;

class DetallePedidoController
{
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $producto = Producto::find($parametros["id_producto"]);

        $header = $request->getHeaderLine('Authorization');

        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);

        if($producto !== null)
        {
            $producto->estado_producto = !empty($parametros["estado_producto"]) ? $parametros["estado_producto"] : $producto->estado_producto;
            $producto->tiempo_preparacion = !empty($parametros["tiempo_preparacion"]) ? $parametros["tiempo_preparacion"] : $producto->tiempo_preparacion;  
            $producto->id_empleado = $datos->Id_usuario;

            $producto->save();

            $payload = json_encode(array("mensaje" => "Producto modificado con exito"));

        }
        else{$payload = json_encode(array("mensaje" => "Producto no encontrado"));}

        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json');
    }

}