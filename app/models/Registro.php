<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Registro extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id_registro';
    protected $table = 'registros';
    public $incrementing = true;
    public $timestamps = false;

    const DELETED_AT = 'fecha_baja';

    protected $fillable = [
        'id_usuario', 'fecha_logueo', 'fecha_baja'
    ];
}
