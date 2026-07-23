<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class MateriaController extends Controller
{
    /**
     * GET /api/materias
     */
    public function index(Request $request): JsonResponse
    {
        $query = Materia::query();

        if ($busqueda = $request->query('buscar')) {
            $query->where('nombre_materia', 'like', "%{$busqueda}%")
                  ->orWhere('codigo', 'like', "%{$busqueda}%");
        }

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('id', 'desc')->paginate(15),
        ]);
    }

    /**
     * POST /api/materias
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nombre_materia' => 'required|string|max:200',
            'codigo'         => 'required|string|max:30|unique:materia,codigo',
            'creditos'       => 'integer|min:0|max:10',
            'nivel'          => 'integer|min:1|max:10',
            'modalidad'      => 'in:semestral,anual',
        ]);

        $materia = Materia::create($data + ['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Materia creada.',
            'data'    => $materia,
        ], 201);
    }

    /**
     * GET /api/materias/{id}
     */
    public function show(int $id): JsonResponse
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        return response()->json(['success' => true, 'data' => $materia]);
    }

    /**
     * PUT/PATCH /api/materias/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        $data = $request->validate([
            'nombre_materia' => 'sometimes|string|max:200',
            'codigo'         => ['sometimes', 'string', 'max:30', Rule::unique('materia', 'codigo')->ignore($id)],
            'creditos'       => 'integer|min:0|max:10',
            'nivel'          => 'integer|min:1|max:10',
            'modalidad'      => 'in:semestral,anual',
            'estado'         => 'in:activo,inactivo',
        ]);

        $materia->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Materia actualizada.',
            'data'    => $materia,
        ]);
    }

    /**
     * DELETE /api/materias/{id} (baja lógica)
     */
    public function destroy(int $id): JsonResponse
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['success' => false, 'message' => 'No encontrada.'], 404);
        }

        $materia->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Materia desactivada.']);
    }
}