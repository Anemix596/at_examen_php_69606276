<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Prestamos;
use App\Models\Libro;
use App\Models\Cliente;
use Carbon\Carbon;

class PrestamosController extends Controller
{

    public function index()
    {
        $prestamos = Prestamos::with('libro', 'cliente')->get();
        return response()->json($prestamos);
    }

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'libro_id' => 'required|exists:libros,id',
            'cliente_id' => 'required|exists:clientes,id',
            'fecha_prestamo' => 'required|date',
            'dias_prestamo' => 'required|integer'
        ]);

        // Obtener la fecha actual del servidor
        $fechaActual = now()->toDateString();

        // Calcular la fecha de devolución sumando los días de préstamo a la fecha de préstamo
        $fechaDevolucion = date('Y-m-d', strtotime($request->fecha_prestamo . ' + ' . $request->dias_prestamo . ' days'));

        // Determinar el estado del préstamo
        $estado = '';
        if ($fechaActual == $request->fecha_prestamo) {
            $estado = 'Activo';
        } elseif ($fechaActual < $request->fecha_prestamo) {
            $estado = 'No Comenzado';
        } else {
            $estado = 'Finalizado';
        }

        // Crear un nuevo préstamo
        $prestamo = new Prestamos();
        $prestamo->libro_id = $request->libro_id;
        $prestamo->cliente_id = $request->cliente_id;
        $prestamo->fecha_prestamo = $request->fecha_prestamo;
        $prestamo->dias_prestamo = $request->dias_prestamo;
        $prestamo->fecha_devolucion = $fechaDevolucion;
        $prestamo->estado = $estado;
        $prestamo->save();

        // Actualizar el lote del libro
        $libro = Libro::findOrFail($request->libro_id);
        $libro->lote -= 1;
        $libro->save();

        return response()->json(['message' => 'Préstamo registrado exitosamente'], 201);
    }


    public function obtenerLote($id)
    {
        if (!is_numeric($id)) {
            return response()->json(['error' => 'El ID del préstamo no es válido', $id], 400);
        }

        $libro = Libro::findOrFail($id);

        $lote = $libro->lote;

        return response()->json(['lote' => $lote]);
    }

    public function update(Request $request, $id)
    {
        $prestamo = Prestamos::findOrFail($id);

        // Validar y actualizar los datos del préstamo
        $request->validate([
            'fecha_devolucion' => 'required|date',
        ]);

        // Modificar el estado a "Finalizado"
        $request->merge(['estado' => 'Finalizado']);

        // Actualizar el préstamo
        $prestamo->update($request->all());

        // Incrementar el campo lote en la tabla libro
        $libro = Libro::findOrFail($prestamo->libro_id);
        $libro->increment('lote');

        return response()->json(['message' => 'Préstamo actualizado con éxito y lote incrementado']);
    }

    public function finalizarPrestamo($id)
    {
        try {
            // Buscar el préstamo por su ID
            $prestamo = Prestamos::findOrFail($id);

            // Actualizar el estado del préstamo a "Finalizado"
            $prestamo->estado = 'Finalizado';
            $prestamo->save();

            // Incrementar el campo lote en la tabla libro
            $libro = $prestamo->libro;
            $libro->lote++;
            $libro->save();

            return response()->json(['message' => 'Préstamo finalizado con éxito'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al finalizar el préstamo'], 500);
        }
    }

    public function primer_reporte()
    {
        $hoy = Carbon::now();

        $clientesConLibrosVencidos = Prestamos::where('fecha_devolucion', '<', $hoy)
            ->where('estado', 'Activo')
            ->with('cliente', 'libro')
            ->get()
            ->groupBy('cliente_id');
        return response()->json($clientesConLibrosVencidos);
    }

    public function segundo_reporte()
    {
        $prestamos = Prestamos::with('cliente', 'libro.autor')
            ->get()
            ->groupBy(function ($prestamo) {
                return Carbon::parse($prestamo->fecha_prestamo)->format('Y-m');
            })
            ->map(function ($group) {
                return $group->groupBy(function ($prestamo) {
                    return Carbon::parse($prestamo->fecha_prestamo)->weekOfYear;
                })->map(function ($subGroup) {
                    // Contar la cantidad de préstamos por autor
                    $autores = $subGroup->groupBy('libro.autor.name')->map->count();
                    // Encontrar el autor más popular (el que tiene más préstamos)
                    $autorMasPopular = $autores->sortDesc()->keys()->first();
                    // Devolver el autor más popular junto con los préstamos de esa semana
                    return [
                        'autor' => $autorMasPopular,
                        'prestamos' => $subGroup,
                    ];
                });
            });

        return response()->json($prestamos);
    }
}
