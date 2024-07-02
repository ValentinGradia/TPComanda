<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

require_once './interfaces/IApiCampos.php';
require_once './models/Producto.php';
require_once './models/DetallePedido.php';
require_once './models/Pedido.php';
require_once './middlewares/AutentificadorJWT.php';

use \App\Models\Pedido as Pedido;
use \App\Models\Producto as Producto;
use \App\Models\DetallePedido as DetallePedido;


class PedidoMW implements IApiCampos
{
    public static function ValidarCampos(Request $request, RequestHandler $handler)
    {

        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if(isset($params["codigo_mesa"], $params["codigo_pedido"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "campos invalidos"))); 
        }

        return $response;
    }

    public static function ValidarPedidoCorrespondiente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getQueryParams();
        $codigo_pedido = $params["codigo_pedido"];

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);

        $datos = AutentificadorJWT::ObtenerData($token);

        $nombre = $datos->nombre;

        $pedido = Pedido::find($codigo_pedido);

        if($pedido->nombre_cliente != $nombre)
        {
            $response->getBody()->write(json_encode(array("error" => "esa pedido no esta a tu nombre")));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;

    }

    public static function ValidarPedidoMesaCorrespondiente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getQueryParams();
        $codigo_pedido = $params["codigo_pedido"];
        $codigo_mesa = $params["codigo_mesa"];

        $pedido = Pedido::find($codigo_pedido);

        if($pedido->codigo_mesa != $codigo_mesa)
        {
            $response->getBody()->write(json_encode(array("error" => "esa pedido no esta en esa mesa")));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }

    public static function ValidarPedidoRepetido(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();
        $codigo_mesa = $params["codigo_mesa"];
        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::where('codigo_mesa', $codigo_mesa)->where('estado_pedido','en preparacion')->first();

        if($pedido !== null)
        {
            if($pedido->codigo_pedido != $codigo_pedido)
            {
                $response->getBody()->write(json_encode(array("error" => "esa mesa ya tiene asignada un pedido"))); 
            }
            else
            {
                $response = $handler->handle($request);
            }
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;

    }

    public static function ValidarPedidoEnPreparacion(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $queryParams = $request->getQueryParams();
        $bodyParams = $request->getParsedBody();
        $params = !empty($queryParams) ? $queryParams : $bodyParams;

        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);

        if($pedido->estado_pedido == "en preparacion")
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "el pedido no esta en preparacion"))); 
        }

        return $response;
    }

    public static function ValidarPedidoListo(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();

        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);

        if($pedido->estado_pedido == "servido")
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "el pedido no esta en preparacion"))); 
        }

        return $response;
    }

    public static function ValidarCodigoExistente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();
        $codigo_pedido = $params["codigo_pedido"];

        if(Pedido::find($codigo_pedido))
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

        if(Pedido::find($params["codigo_pedido"]))
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

        $productos = DetallePedido::where('codigo_pedido',$params["codigo_pedido"])->get();

        $flag = true;
        //validar que los productos esten listos para poder servir el pedido
        foreach($productos as $producto)
        {
            if($producto->codigo_pedido == $codigo_pedido)
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
            $pedido->save();
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "los productos aun no estan listos para servir")));
        }

        return $response;

    }
}