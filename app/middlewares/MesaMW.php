<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

use \App\Models\Mesa as Mesa;
use \App\Models\Pedido as Pedido;

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
                //si la mesa previamente esta cerrado y el estado de la mesa que pasa por postman es cliente esperando pedido accede
                if($mesa->estado_mesa == "cerrada" && $estado_mesa == "con cliente esperando pedido" )
                {
                    $response = $handler->handle($request);
                }
                //para que el estado de la mesa sea con cliente comiendo la mesa previamente tiene que estar con cliente esperando pedido
                else if($mesa->estado_mesa == "con cliente esperando pedido" && $estado_mesa == "con cliente comiendo" )
                {
                    //se entrega el pedido
                    $pedido->tiempo_entrega = $params["tiempo_entrega"];
                    $response = $handler->handle($request);
                }
                else if($mesa->estado_mesa == "con cliente comiendo" && $estado_mesa == "con cliente pagando")
                {
                    $response = $handler->handle($request);
                } 
                else if( $estado_mesa == "cerrada")
                {
                    $response->getBody()->write(json_encode(array("error" => "no tiene los permisos para cerrar la mesa"))); 
                }
                else
                {
                    $response->getBody()->write(json_encode(array("error" => "verifique el estado mesa ingresado"))); 
                }
            }
            else if($rol == "socio")
            {
                if($mesa->estado_mesa == "con cliente pagando" && $estado_mesa == "cerrada")
                {
                    $response = $handler->handle($request);
                }
                else
                {
                    $response->getBody()->write(json_encode(array("error" => "verifique el estado mesa ingresado"))); 
                }
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "no tiene permisos"))); 
            }
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