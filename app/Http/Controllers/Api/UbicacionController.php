<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ubicacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UbicacionController extends Controller
{
    /**
     * GET /api/ubicaciones
     * Listar ubicaciones (filtro por estado y tipo)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Ubicacion::query();

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);        // filtrar activo/inactivo
        }

        if ($tipo = $request->query('tipo')) {
            $query->where('tipo', $tipo);            // filtrar aula/lab/auditorio/exterior
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('nombre_lugar')->paginate(15),
        ]);
    }

    /**
     * POST /api/ubicaciones
     * Crear nueva ubicación con polígono de coordenadas
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_lugar'      => 'required|string|max:150',
            'tipo'              => 'required|in:aula,laboratorio,auditorio,exterior',
            'edificio_campus'   => 'nullable|string|max:100',
            'coordenadas' => 'nullable|array',            // array de puntos [{"lat":x, "lon":y}]
            'coordenadas.*.lat' => 'required_with:coordenadas|numeric',
            'coordenadas.*.lon' => 'required_with:coordenadas|numeric',
            'tolerancia_metros' => 'integer|min:10|max:500',     // margen extra en metros
        ]);

        $ubicacion = Ubicacion::create($data + ['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación creada.',
            'data'    => $ubicacion,
        ], 201);
    }

    /**
     * GET /api/ubicaciones/{id}
     * Ver detalle de una ubicación
     */
    public function show(int $id): JsonResponse
    {
        $ubicacion = Ubicacion::find($id);

        if (!$ubicacion) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        return response()->json(['success' => true, 'data' => $ubicacion]);
    }

    /**
     * PUT /api/ubicaciones/{id}
     * Editar ubicación (nombre, tipo, coordenadas, tolerancia, estado)
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $ubicacion = Ubicacion::find($id);

        if (!$ubicacion) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        $data = $request->validate([
            'nombre_lugar'      => 'sometimes|string|max:150',
            'tipo'              => 'sometimes|in:aula,laboratorio,auditorio,exterior',
            'edificio_campus'   => 'nullable|string|max:100',
            'coordenadas' => 'nullable|array',
            'coordenadas.*.lat' => 'required_with:coordenadas|numeric',
            'coordenadas.*.lon' => 'required_with:coordenadas|numeric',
            'tolerancia_metros' => 'integer|min:10|max:500',
            'estado'            => 'in:activo,inactivo',
        ]);

        $ubicacion->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Ubicación actualizada.',
            'data'    => $ubicacion,
        ]);
    }

    /**
     * DELETE /api/ubicaciones/{id}
     * Desactivar ubicación (baja lógica, no borra)
     */
    public function destroy(int $id): JsonResponse
    {
        $ubicacion = Ubicacion::find($id);

        if (!$ubicacion) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        $ubicacion->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Ubicación desactivada.']);
    }
}