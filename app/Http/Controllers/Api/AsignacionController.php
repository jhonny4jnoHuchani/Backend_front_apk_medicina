<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParaleloMateria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AsignacionController extends Controller
{
    /**
     * GET /api/asignaciones?paralelo_id=1
     * Lista todas las materias de un paralelo, con y sin docente
     */
    public function index(Request $request): JsonResponse
    {
        $query = ParaleloMateria::with(['materia', 'paralelo', 'docente.user']);

        if ($paraleloId = $request->query('paralelo_id')) {
            $query->where('paralelo_id', $paraleloId);
        }

        if ($materiaId = $request->query('materia_id')) {
            $query->where('materia_id', $materiaId);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('id')->paginate(20),
        ]);
    }

    /**
     * POST /api/asignaciones
     * Asigna una materia a un paralelo (sin docente aún)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'materia_id'  => 'required|exists:materia,id',
            'paralelo_id' => 'required|exists:paralelos,id',
        ]);

        $existe = ParaleloMateria::where('materia_id', $data['materia_id'])
            ->where('paralelo_id', $data['paralelo_id'])
            ->exists();

        if ($existe) {
            return response()->json([
                'success' => false,
                'message' => 'Esta materia ya está asignada a este paralelo.',
            ], 409);
        }

        $asignacion = ParaleloMateria::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Materia asignada al paralelo.',
            'data'    => $asignacion->load(['materia', 'paralelo']),
        ], 201);
    }

    /**
     * PUT /api/asignaciones/{id}/asignar-docente
     * Asigna o cambia el docente de una materia en un paralelo
     */
    public function asignarDocente(Request $request, int $id): JsonResponse
    {
        $asignacion = ParaleloMateria::find($id);

        if (!$asignacion) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada.'], 404);
        }

        $data = $request->validate([
            'docente_id' => 'required|exists:docente,id',
        ]);

        $asignacion->update(['docente_id' => $data['docente_id']]);

        return response()->json([
            'success' => true,
            'message' => 'Docente asignado correctamente.',
            'data'    => $asignacion->load(['materia', 'paralelo', 'docente.user']),
        ]);
    }

    /**
     * DELETE /api/asignaciones/{id}/quitar-docente
     * Quita el docente (lo deja NULL)
     */
    public function quitarDocente(int $id): JsonResponse
    {
        $asignacion = ParaleloMateria::find($id);

        if (!$asignacion) {
            return response()->json(['success' => false, 'message' => 'Asignación no encontrada.'], 404);
        }

        $asignacion->update(['docente_id' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Docente removido de la asignación.',
            'data'    => $asignacion->load(['materia', 'paralelo']),
        ]);
    }

    /**
     * DELETE /api/asignaciones/{id}
     * Elimina la asignación materia-paralelo
     */
    public function destroy(int $id): JsonResponse
    {
        $asignacion = ParaleloMateria::find($id);

        if (!$asignacion) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        $asignacion->delete();

        return response()->json(['success' => true, 'message' => 'Asignación eliminada.']);
    }
}