<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

//require_once "../interfaces/IApiCampos.php";


class PedidoMW
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
}