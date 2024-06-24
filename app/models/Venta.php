<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    protected $primaryKey = 'id_venta';
    protected $table = 'ventas';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'codigo_pedido','codigo_mesa','cobro','fecha_venta'
    ];
}