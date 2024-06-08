<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;


class MesaMW 
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
}