<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

require_once './models/Mesa.php';
require_once './interfaces/IApiCampos.php';

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
        $params = $request->getParsedBody();
        $codigo_mesa = $params["codigo_mesa"];

        $mesa = Mesa::ObtenerMesa($codigo_mesa);
        if($mesa->estado_mesa == "cerrada")
        {
            $mesa->estado_mesa = "con cliente esperando pedido";
            Mesa::modificarMesa($mesa);
        }

        $response = $handler->handle($request);
        return $response;

    }

    public static function ValidarEstadoMesa(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();
        $codigo_mesa = $params["codigo_mesa"];

        $mesa = Mesa::ObtenerMesa($codigo_mesa);

        if($mesa->estado_mesa == "con cliente esperando pedido")
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

        if(Mesa::ObtenerMesa($params["codigo_mesa"]))
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

        if(Mesa::ObtenerMesa($params["codigo_mesa"]))
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