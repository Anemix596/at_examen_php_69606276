<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;


class Prestamos extends Model
{
    use HasFactory;

    protected $table = 'prestamos';

    protected $fillable = ['libro_id', 'cliente_id', 'fecha_prestamo', 'dias_prestamo', 'fecha_devolucion', 'estado'];

    public function libro()
    {
        return $this->belongsTo(Libro::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
