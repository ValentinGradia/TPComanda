<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

require_once './interfaces/IApiCampos.php';


class ProductoMW implements IApiCampos
{
    public static function ValidarCampos(Request $request, RequestHandler $handler)
    {

        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if(isset($params["tipo"], $params["nombre"], $params["precio"], $params["cantidad"], $params["estado_producto"]))
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "campos invalidos"))); 
        }

        return $response;
    }

    public static function ValidarTipo(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();

        if($params["tipo"] === "cerveza" || $params["tipo"] === "trago" || $params["tipo"] === "comida")
        {
            $response = $handler->handle($request);
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "tipo de producto invalido"))); 
        }

        return $response;

    }
}