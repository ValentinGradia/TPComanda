<?php
include "Pedido.php";


class Mesa
{
    public $codigo_mesa;
    public $foto_mesa;
    public $estado_mesa;

    public function CrearMesa()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO mesas(codigo_mesa,estado_mesa) VALUES (:codigo_mesa,:estado_mesa)");

        $sql->bindValue(":codigo_mesa",$this->codigo_mesa, PDO::PARAM_INT);
        $sql->bindValue(":estado_mesa", $this->estado_mesa, PDO::PARAM_STR);

        $sql->execute();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa, estado_mesa FROM mesas");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    
    }

    public static function ObtenerMesa($codigo_mesa)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa, estado_mesa FROM mesas WHERE codigo_mesa = :codigo_mesa");
        $consulta->bindValue(':codigo_mesa', $codigo_mesa, PDO::PARAM_INT);

        $consulta->execute();

        return $consulta->fetchObject('Mesa');
    }

    public static function modificarMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET estado_mesa = :estado_mesa WHERE codigo_mesa = :codigo_mesa");
        $consulta->bindValue(":codigo_mesa", $mesa->codigo_mesa);
        $consulta->bindValue(":estado_mesa", $mesa->estado_mesa);

        $consulta->execute();
    }

    public static function borrarMesa($mesa)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE mesas SET fechaBaja = :fechaBaja WHERE codigo_mesa = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $mesa->codigo_mesa, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}