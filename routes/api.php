<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/verificar-conexion', function () {
    return response()->json(['message' => 'Conexión exitosa desde Laravel']);
});

use App\Http\Controllers\LibroController;

Route::prefix('libros')->group(function () {
    Route::get('/', [LibroController::class, 'index']);
    Route::post('/', [LibroController::class, 'store']);
    Route::get('/{id}', [LibroController::class, 'show']);
    Route::put('/{id}', [LibroController::class, 'update']);
    Route::delete('/{id}', [LibroController::class, 'destroy']);
});

use App\Http\Controllers\AutorController;

Route::get('/autores', [AutorController::class, 'index']);
Route::post('/autores', [AutorController::class, 'store']);
Route::get('/autores/{id}', [AutorController::class, 'show']);
Route::put('/autores/{id}', [AutorController::class, 'update']);
Route::delete('/autores/{id}', [AutorController::class, 'destroy']);

use App\Http\Controllers\ClienteController;

Route::prefix('clientes')->group(function () {
    Route::get('/', [ClienteController::class, 'index']);
    Route::post('/', [ClienteController::class, 'store']);
    Route::get('/{id}', [ClienteController::class, 'show']);
    Route::put('/{id}', [ClienteController::class, 'update']);
    Route::delete('/{id}', [ClienteController::class, 'destroy']);
});


use App\Http\Controllers\PrestamosController;

Route::prefix('prestamos')->group(function () {
    Route::get('/', [PrestamosController::class, 'index']);
    Route::post('/', [PrestamosController::class, 'store']);
    Route::put('/{id}', [PrestamosController::class, 'update']);
    /* Route::get('/{id}', [PrestamosController::class, 'show']);
    Route::delete('/{id}', [PrestamosController::class, 'destroy']); */
    Route::get('/{id}/lote', [PrestamosController::class, 'obtenerLote']);
    Route::put('/{id}/finalizar', [PrestamosController::class, 'finalizarPrestamo']);
    Route::get('/primer-reporte', [PrestamosController::class, 'primer_reporte']);
    Route::get('/segundo-reporte', [PrestamosController::class, 'segundo_reporte']);
});
