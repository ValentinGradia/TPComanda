<?php

include "Producto.php";


class Pedido 
{
    private $codigo_mesa;
    private $codigo_pedido;
    private $estado_pedido;
    private $fecha_inicio_pedido;
    private $fecha_cierre_pedido;
    private $nombre_cliente;
    private $sector;

    public function CrearPedido()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO pedidos(codigo_mesa,codigo_pedido,estado_pedido,fecha_inicio_pedido,nombre_cliente,
        fecha_cierre_pedido,sector) VALUES (:codigo_mesa,:codigo_pedido,:estado_pedido,:fecha_inicio_pedido,:nombre_cliente,:
        fecha_cierre_pedido,:sector)");

        $sql->bindValue(":codigo_mesa",$this->codigo_mesa, PDO::PARAM_INT);
        $sql->bindValue(":codigo_pedido", $this->codigo_pedido, PDO::PARAM_INT);
        $sql->bindValue(":estado_pedido", $this->estado_pedido, PDO::PARAM_STR);
        $sql->bindValue(":fecha_inicio_pedido",$this->fecha_inicio_pedido);
        $sql->bindValue(":fecha_cierre_pedido", $this->fecha_cierre_pedido);
        $sql->bindValue(":nombre_cliente", $this->nombre_cliente, PDO::PARAM_STR);
        $sql->bindValue(":sector", $this->sector, PDO::PARAM_STR);

        $sql->execute();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa,codigo_pedido,estado_pedido,fecha_inicio_pedido,nombre_cliente,
        fecha_cierre_pedido,sector FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    
    }

    public static function ObtenerPedido($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa,codigo_pedido,estado_pedido,fecha_inicio_pedido,nombre_cliente,
        fecha_cierre_pedido,sector FROM pedidos WHERE pedido = :pedido");
        $consulta->bindValue(':usuario', $pedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    // public static function modificarPedido($pedido)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $sql = $objAccesoDato->prepararConsulta("UPDATE pedidos SET codigo_mesa=:codigo_mesa, estado_pedido=:estado_pedido,
    //     fecha_inicio_pedido=:fecha_inicio_pedido, nombre_cliente=:nombre_cliente, fecha_cierre_pedido=:fecha_cierre_pedido, sector=:sector
    //     WHERE codigo_pedido = :codigo_pedido");

    //     $sql->bindValue(":codigo_mesa",$pedido->codigo_mesa, PDO::PARAM_INT);
    //     $sql->bindValue(":codigo_pedido", $pedido->codigo_pedido, PDO::PARAM_INT);
    //     $sql->bindValue(":estado_pedido", $pedido->estado_pedido, PDO::PARAM_STR);
    //     $sql->bindValue(":fecha_inicio_pedido",$pedido->fecha_inicio_pedido);
    //     $sql->bindValue(":fecha_cierre_pedido", $pedido->fecha_cierre_pedido);
    //     $sql->bindValue(":nombre_cliente", $pedido->nombre_cliente, PDO::PARAM_STR);
    //     $sql->bindValue(":sector", $pedido->sector, PDO::PARAM_STR);

    //     $sql->execute();
    // }

    // public static function borrarPedido($mesa)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
    //     $fecha = new DateTime(date("d-m-Y"));
    //     $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
    //     $consulta->execute();
    // }
}