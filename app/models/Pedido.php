<?php

require_once "Producto.php";
require_once "../app/db/AccesoDatos.php";

class Pedido 
{
    public $codigo_mesa;
    public $codigo_pedido;
    public $estado_pedido;
    public $tiempo_preparacion;
    public $tiempo_entrega;
    public $nombre_cliente;

    public function crearPedido()
    {
        $objetoAccesoDatos = AccesoDatos::obtenerInstancia();

        $sql = $objetoAccesoDatos->prepararConsulta("INSERT INTO pedidos(codigo_mesa,codigo_pedido,estado_pedido,tiempo_preparacion,nombre_cliente) VALUES (:codigo_mesa,:codigo_pedido,:estado_pedido,:tiempo_preparacion,:nombre_cliente)");

        $sql->bindValue(":codigo_mesa",$this->codigo_mesa, PDO::PARAM_INT);
        $sql->bindValue(":codigo_pedido", $this->codigo_pedido);
        $sql->bindValue(":estado_pedido", $this->estado_pedido, PDO::PARAM_STR);
        $sql->bindValue(":tiempo_preparacion",$this->tiempo_preparacion);
        $sql->bindValue(":nombre_cliente", $this->nombre_cliente, PDO::PARAM_STR);

        $sql->execute();
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa,codigo_pedido,estado_pedido,tiempo_preparacion,
        nombre_cliente FROM pedidos");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    
    }

    public static function obtenerPedido($codigo_pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT codigo_mesa,codigo_pedido,estado_pedido,tiempo_preparacion,
        nombre_cliente FROM pedidos WHERE codigo_pedido = :codigo_pedido");
        $consulta->bindValue(':codigo_pedido', $codigo_pedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $sql = $objAccesoDato->prepararConsulta("UPDATE pedidos SET codigo_mesa=:codigo_mesa, estado_pedido=:estado_pedido,
        tiempo_preparacion=:tiempo_preparacion,nombre_cliente=:nombre_cliente WHERE codigo_pedido = :codigo_pedido");

        $sql->bindValue(":codigo_mesa",$pedido->codigo_mesa, PDO::PARAM_INT);
        $sql->bindValue(":codigo_pedido", $pedido->codigo_pedido, PDO::PARAM_INT);
        $sql->bindValue(":estado_pedido", $pedido->estado_pedido, PDO::PARAM_STR);
        $sql->bindValue(":tiempo_preparacion",$pedido->tiempo_preparacion);
        $sql->bindValue(":nombre_cliente", $pedido->nombre_cliente, PDO::PARAM_STR);

        $sql->execute();
    }

    public static function borrarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET fechaBaja = :fechaBaja WHERE codigo_pedido = :id");
        $fecha = new DateTime(date("d-m-Y"));
        $consulta->bindValue(':id', $pedido->codigo_pedido, PDO::PARAM_INT);
        $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
        $consulta->execute();
    }
}