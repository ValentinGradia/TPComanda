<?php

require_once './models/Usuario.php';

class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }

    public static function Loguear($request, $response, $args)
    {
        $params = $request->getParsedBody();
        $id = $params["id_usuario"];
        $nombre = $params["nombre"];
        $clave = $params["clave"];

        $usuario = Usuario::obtenerUsuario($id);
        if($usuario !== null && password_verify($clave, $usuario->clave))
        {
            
        }
    }
}