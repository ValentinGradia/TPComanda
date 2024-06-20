<?php

class Registro
{
    public $id_registro;
    public $id_usuario;
    public $fecha_logeo;
    
    public function CrearRegistroLogin()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO registros (id_usuario, fecha_logeo) VALUES (:id_usuario, :fecha_logeo)");

        $consulta->bindValue(':id_usuario', $this->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_logeo', $this->fecha_logeo);

        $consulta->execute();
    }

    public static function ObtenerTodos()
    {

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_registro, id_usuario, fecha_logeo FROM registros");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Registro');
    }

    public static function ObtenerRegistro($registro)
    {

        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id_registro, id_usuario, fecha_logeo FROM registros WHERE id_registro = :id_registro");
        $consulta->bindValue(':id_usuario', $registro->id_registro, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Registro');
    }

    public static function ModificarRegistro($registro)
    {

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE registros SET id_usuario=:id_usuario, fecha_logeo=:fecha_logeo WHERE id_registro = :id_registro");
        $consulta->bindValue(':id_registro', $registro->id_registro, PDO::PARAM_INT);
        $consulta->bindValue(':idUsuario', $registro->id_usuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaConexion', $registro->fecha_logeo, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function BorrarRegistro($registro)
    {

        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM registros WHERE id_registro = :id_registro");
        $consulta->bindValue(':id_registro', $registro->id_registro, PDO::PARAM_INT);
        $consulta->execute();
    }
    
}