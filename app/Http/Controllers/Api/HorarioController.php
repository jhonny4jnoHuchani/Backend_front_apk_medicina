<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HorarioController extends Controller
{
    /**
     * GET /api/horarios
     * Listar (filtro por paralelo_materia_id, docente_id, dia_semana, estado)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Horario::with(['paraleloMateria.materia', 'paraleloMateria.paralelo', 'paraleloMateria.docente.user', 'ubicacion']);

        if ($pmId = $request->query('paralelo_materia_id')) {
            $query->where('paralelo_materia_id', $pmId);
        }

        if ($docenteId = $request->query('docente_id')) {
            $query->whereHas('paraleloMateria', function ($q) use ($docenteId) {
                $q->where('docente_id', $docenteId);
            });
        }

        if ($dia = $request->query('dia_semana')) {
            $query->where('dia_semana', $dia);
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('dia_semana')->orderBy('hora_inicio')->paginate(15),
        ]);
    }

    /**
     * GET /api/horarios/hoy
     * Horarios del docente autenticado para el día actual
     */
    public function hoy(Request $request): JsonResponse
    {
        $user = $request->user();
        $docente = $user->docente;

        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'No tienes perfil docente.'], 403);
        }

        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $hoy = $dias[now()->dayOfWeek];  // 0=domingo, 1=lunes...

        $horarios = Horario::with(['paraleloMateria.materia', 'paraleloMateria.paralelo', 'ubicacion'])
            ->whereHas('paraleloMateria', function ($q) use ($docente) {
                $q->where('docente_id', $docente->id);
            })
            ->where('dia_semana', $hoy)
            ->where('estado', 'activo')
            ->orderBy('hora_inicio')
            ->get();

        // Agregar si ya marcó entrada/salida hoy (si ya existe MarcadoController)
        $horarios->each(function ($horario) use ($docente) {
            $horario->ya_marco_entrada = false;
            $horario->ya_marco_salida = false;
        });

        return response()->json([
            'success' => true,
            'dia'     => $hoy,
            'data'    => $horarios,
        ]);
    }

    /**
     * POST /api/horarios/crear
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'paralelo_materia_id' => 'required|exists:paralelo_materia,id',
            'ubicacion_id'        => 'required|exists:ubicacion,id',
            'dia_semana'          => 'required|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'         => 'required|date_format:H:i',
            'hora_fin'            => 'required|date_format:H:i|after:hora_inicio',
            'tipo_actividad'      => 'required|in:clase,laboratorio,tutoria,otro',
        ]);

        $horario = Horario::create($data + ['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Horario creado.',
            'data'    => $horario->load(['paraleloMateria.materia', 'paraleloMateria.paralelo', 'ubicacion']),
        ], 201);
    }

    /**
     * GET /api/horarios/{id}/ver
     */
    public function show(int $id): JsonResponse
    {
        $horario = Horario::with(['paraleloMateria.materia', 'paraleloMateria.paralelo', 'ubicacion'])->find($id);

        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        return response()->json(['success' => true, 'data' => $horario]);
    }

    /**
     * PUT /api/horarios/{id}/actualizar
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        $data = $request->validate([
            'ubicacion_id'   => 'sometimes|exists:ubicacion,id',
            'dia_semana'     => 'sometimes|in:lunes,martes,miercoles,jueves,viernes,sabado,domingo',
            'hora_inicio'    => 'sometimes|date_format:H:i',
            'hora_fin'       => 'sometimes|date_format:H:i|after:hora_inicio',
            'tipo_actividad' => 'sometimes|in:clase,laboratorio,tutoria,otro',
            'estado'         => 'in:activo,inactivo,suspendido',
        ]);

        $horario->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Horario actualizado.',
            'data'    => $horario->fresh(['paraleloMateria.materia', 'paraleloMateria.paralelo', 'ubicacion']),
        ]);
    }

    /**
     * DELETE /api/horarios/{id}/eliminar
     */
    public function destroy(int $id): JsonResponse
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        $horario->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Horario desactivado.']);
    }
}