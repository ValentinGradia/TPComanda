<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

require_once './interfaces/IApiCampos.php';
require_once './models/Producto.php';
require_once './models/Pedido.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Producto as Producto;


class PedidoMW implements IApiCampos
{
    public static function ValidarCampos(Request $request, RequestHandler $handler)
    {

        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if(isset($params["codigo_mesa"], $params["codigo_pedido"], $params["estado_pedido"], $params["nombre_cliente"],
        $params["tiempo_preparacion"] ))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "campos invalidos"))); 
        }

        return $response;
    }

    public static function ValidarCodigoExistente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();

        if(Pedido::where('codigo_pedido',$params["codigo_pedido"]))
        {
            $response->getBody()->write(json_encode(array("error" => "esa codigo ya existe")));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }

    
    public static function ValidarCodigoNoExistente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $queryParams = $request->getQueryParams();
        $bodyParams = $request->getParsedBody();
        $params = !empty($queryParams) ? $queryParams : $bodyParams;

        if(Pedido::where('codigo_pedido',$params["codigo_pedido"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "codigo de pedido no existente")));
        }

        return $response;
    }

    public static function ValidarProductosListos(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();

        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);
        $codigo_mesa = $pedido->codigo_mesa;

        $productos = Producto::all();

        $flag = true;
        //validar que los productos esten listos para poder servir el pedido
        foreach($productos as $producto)
        {
            if($producto->codigo_mesa == $codigo_mesa)
            {
                if($producto->estado_producto !== "listo")
                {
                    $flag = false;
                    break;
                }
            }
        }

        if($flag)
        {
            $pedido->estado_pedido = "listo para servir";
            $pedido->tiempo_entregado = date('Y-m-d H:i');
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "los productos aun no estan listos para servir")));
        }

        return $response;

    }
}