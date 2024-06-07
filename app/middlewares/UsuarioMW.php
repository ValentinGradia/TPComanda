<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseClass;

class UsuarioMW
{
    private $perfil ='';

    public function __construct($perfil) 
    {
        $this->perfil = $perfil;
    }

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $this->VerificarRol($request, $handler);
    }

    public function VerificarRol(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getQueryParams();

        if($params["rol"] !== $this->perfil)
        {
            $response->getBody()->write(json_encode(array("Error" => "No sos ".$this->perfil)));
        }
        else
        {
            $response = $handler->handle($request);
        }

        return $response;
    }


    public static function ValidarRol(Request $request, RequestHandler $handler)
    {
        $response = new ResponseClass();

        $params = $request->getParsedBody();


        if(isset($params["rol"]))
        {
            $rol = $params["rol"];
            if($rol == "socio" || $rol == "bartender" || $rol == "mozo" || $rol == "candybar" ||
            $rol == "cocinero")
            {
                $response = $handler->handle($request);
            }
            else
            {
                $response->getBody()->write(json_encode(array("error" => "rol incorrecto")));
            }
        }
        else
        {
            $response->getBody()->write(json_encode(array("error" => "complete su rol")));
        }

        return $response;
    }
}