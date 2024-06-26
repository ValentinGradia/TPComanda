<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

use \App\Models\Mesa as Mesa;
use \App\Models\Pedido as Pedido;
use App\Models\Producto as Producto;

require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiCampos.php';
require_once './middlewares/AutentificadorJWT.php';

class MesaMW implements IApiCampos
{
    public static function ValidarCampos(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();
        if(isset($params["codigo_mesa"], $params["estado_mesa"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "campos invalidos"))); 
        }

        return $response;
    }

    public static function ValidarMesaOcupada(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);
        $id_usuario = $datos->Id_usuario;

        $codigo_mesa = $params["codigo_mesa"];

        $mesa = Mesa::where('codigo_mesa',$codigo_mesa)->first();

        if($mesa->estado_mesa == 'con cliente esperando pedido')
        {
            $producto = Producto::where('codigo_mesa',$codigo_mesa)->where('estado_producto','pendiente')->first();
    
            if($producto->id_cliente != $id_usuario)
            {
                $response->getBody()->write(json_encode(array("error" => "esa mesa ya esta ocupada"))); 
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

    public static function CambiarEstadoMesa(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();

        $header = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $header)[1]);
        $datos = AutentificadorJWT::ObtenerData($token);

        $codigo_mesa = $params["codigo_mesa"];
        $codigo_pedido = $params["codigo_pedido"];
        $estado_mesa = $params["estado_mesa"];
        $rol = $datos->rol;


        $mesa = Mesa::find($codigo_mesa);
        $pedido = Pedido::find($codigo_pedido);

        //validar que el codigo del pedido este asociado a la mesa
        if($pedido->codigo_mesa != $codigo_mesa)
        {
            $response->getBody()->write(json_encode(array("error" => "ese pedido no pertenece a esa mesa"))); 
        }
        else
        {
            //validaciones de los estados de las mesas
            if($rol == "mozo")
            {
                //validar que el pedido este listo 
                if($pedido->estado_pedido == "listo para servir")
                {
                    //para que el estado de la mesa sea con cliente comiendo la mesa previamente tiene que estar con cliente esperando pedido
                    if($mesa->estado_mesa == "con cliente esperando pedido" && $estado_mesa == "con cliente comiendo" )
                    {
                        //se entrega el pedido
                        $pedido->tiempo_entregado = date('Y-m-d H:i');
                        $pedido->estado_pedido = "servido";
                        $pedido->save();
                        $response = $handler->handle($request);
                    }
                    else
                    {
                        $response->getBody()->write(json_encode(array("error" => "verifique el estado mesa ingresado"))); 
                    }
                }
                else if ($pedido->estado_pedido == "servido")
                {
                    if($mesa->estado_mesa == "con cliente comiendo" && $estado_mesa == "con cliente pagando")
                    {
                        $response = $handler->handle($request);
                    } 
                    else if($estado_mesa == "cerrada")
                    {
                        $response->getBody()->write(json_encode(array("error" => "no tiene los permisos para cerrar la mesa"))); 
                    }
                }
                else
                {
                    $response->getBody()->write(json_encode(array("error" => "el pedido aun no esta listo")));
                }
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "no tiene permisos"))); 
            }
        }


        return $response;

    }

    public static function ValidarClientePagando(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();
        $codigo_pedido = $params["codigo_pedido"];

        $pedido = Pedido::find($codigo_pedido);
        $codigo_mesa = $pedido->codigo_mesa;

        $mesa = Mesa::find($codigo_mesa);

        if($mesa->estado_mesa != 'con cliente pagando')
        {
            $response->getBody()->write(json_encode(array("error" => "aun el cliente no pidio la cuenta"))); 
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }

    public static function ValidarEstadoMesa(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();
        $codigo_mesa = $params["codigo_mesa"];

        $mesa = Mesa::find($codigo_mesa);

        if($mesa->estado_mesa == "con cliente esperando pedido" || $mesa->estado_mesa == "con cliente comiendo")
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "esa mesa no espera un pedido"))); 
        }

        return $response;
    }

    public static function ValidarCodigoExistente(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();
        $params = $request->getParsedBody();

        if(Mesa::find($params["codigo_mesa"]))
        {
            $response->getBody()->write(json_encode(array("error" => "esa mesa ya existe")));
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

        if(Mesa::find($params["codigo_mesa"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "codigo de mesa no existente")));
        }

        return $response;
    }
}