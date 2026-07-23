<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Paralelo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ParaleloController extends Controller
{
    /**
     * GET /api/paralelos
     */
    public function index(Request $request): JsonResponse
    {
        $query = Paralelo::query();

        if ($estado = $request->query('estado')) {
            $query->where('estado', $estado);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('grado')->orderBy('paralelo')->paginate(15),
        ]);
    }

    /**
     * POST /api/paralelos
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'grado'      => 'required|integer|min:1|max:10',
            'paralelo'   => 'required|string|max:10',
            'capacidad'  => 'integer|min:1|max:100',
        ]);

        $paralelo = Paralelo::create($data + ['estado' => 'activo']);

        return response()->json([
            'success' => true,
            'message' => 'Paralelo creado.',
            'data'    => $paralelo,
        ], 201);
    }

    /**
     * GET /api/paralelos/{id}
     */
    public function show(int $id): JsonResponse
    {
        $paralelo = Paralelo::find($id);

        if (!$paralelo) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        return response()->json(['success' => true, 'data' => $paralelo]);
    }

    /**
     * PUT/PATCH /api/paralelos/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $paralelo = Paralelo::find($id);

        if (!$paralelo) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        $data = $request->validate([
            'grado'      => 'sometimes|integer|min:1|max:10',
            'paralelo'   => 'sometimes|string|max:10',
            'capacidad'  => 'integer|min:1|max:100',
            'estado'     => 'in:activo,inactivo,suspendido',
        ]);

        $paralelo->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Paralelo actualizado.',
            'data'    => $paralelo,
        ]);
    }

    /**
     * DELETE /api/paralelos/{id} (baja lógica)
     */
    public function destroy(int $id): JsonResponse
    {
        $paralelo = Paralelo::find($id);

        if (!$paralelo) {
            return response()->json(['success' => false, 'message' => 'No encontrado.'], 404);
        }

        $paralelo->update(['estado' => 'inactivo']);

        return response()->json(['success' => true, 'message' => 'Paralelo desactivado.']);
    }
}