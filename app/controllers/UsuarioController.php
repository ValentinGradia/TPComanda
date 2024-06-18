<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once './middlewares/AutentificadorJWT.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros["nombre"];
        $clave = $parametros["clave"];
        $rol = $parametros['rol'];

        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->clave = $clave;
        $usr->rol = $rol;
        $usr->crearUsuario();

        return self::CrearTokenUsuario($request, $response, $args);

    }

    public function CrearTokenUsuario($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $nombre = $parametros["nombre"];
      $clave = $parametros["clave"];
      $rol = $parametros['rol'];

      $datos = array('nombre' => $nombre, 'clave' => $clave, 'rol' => $rol);

      $token = AutentificadorJWT::CrearToken($datos);

      $payload = json_encode(array('jwt' => $token));

      $response->getBody()->write($payload);
      return $response
        ->withHeader('Content-Type', 'application/json');

    }

    public function TraerUno($request, $response, $args)
    {
        $params = $request->getQueryParams();
        $id_usuario = $params['id_usuario'];
        $usuario = Usuario::obtenerUsuario($id_usuario);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = Usuario::obtenerUsuario($parametros["id_usuario"]);
        $usuario->nombre = !empty($parametros["nombre"]) ? $parametros["nombre"] : $usuario->nombre;
        $usuario->clave = !empty($parametros["clave"]) ? $parametros["clave"] : $usuario->clave;
        $usuario->rol = !empty($parametros["rol"]) ? $parametros["rol"] : $usuario->rol;
        Usuario::modificarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $usuario = Usuario::obtenerUsuario($parametros["id_usuario"]);
        Usuario::borrarUsuario($usuario);

        $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
