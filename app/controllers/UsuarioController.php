<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

use \App\Models\Usuario as Usuario;

class UsuarioController implements IApiUsable
{

  public function CargarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $nombre = $parametros['nombre'];
    $clave = $parametros['clave'];
    $rol = $parametros['rol'];
    $estado = 'activo';

    $usr = new Usuario();
    $usr->nombre = $nombre;
    $usr->clave = password_hash($clave, PASSWORD_DEFAULT);
    $usr->rol = $rol;
    $usr->estado = $estado;
    $usr->save(); //metodo save seria como el insert en sql

    $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerUno($request, $response, $args)
  {
    $parametros = $request->getQueryParams();
    $id_usuario = $parametros['id_usuario'];

    $usuario = Usuario::find($id_usuario); //el find se usa exclusivamente para buscar por claves primarias (id)
    
    $payload = json_encode($usuario);

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function TraerTodos($request, $response, $args)
  {
    $lista = Usuario::all(); //el all te trae todos los usuarios
    $payload = json_encode(array("listaUsuarios" => $lista));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function ModificarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();
    
    $id_usuario = $parametros["id_usuario"];

    // Conseguimos el objeto con where
    //$usuario = Usuario::where('id_usuario', $id_usuario)->first(); //where se usa para filtros mas complejos
    //con find
    $usuario = Usuario::find($id_usuario);

    if ($usuario !== null) {
      // Seteamos un nuevo usuario
      $usuario->nombre = !empty($parametros["nombre"]) ? $parametros["nombre"] : $usuario->nombre;
      $usuario->clave = !empty($parametros["clave"]) ? password_hash($parametros["clave"],PASSWORD_DEFAULT) : $usuario->clave;
      $usuario->rol = !empty($parametros["rol"]) ? $parametros["rol"] : $usuario->rol;
      // Guardamos en base de datos
      $usuario->save();
      $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
    } else {
      $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }

  public function BorrarUno($request, $response, $args)
  {
    $parametros = $request->getParsedBody();

    $id_usuario = $parametros["id_usuario"];

    $usuario = Usuario::find($id_usuario);

    if($usuario !== null)
    {
      $usuario->estado = "inactivo";
      $usuario->fecha_baja = date('Y-m-d H:i');
      $usuario->delete();
    }
    
    $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  }
}