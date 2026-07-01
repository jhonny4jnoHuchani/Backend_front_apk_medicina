<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    /**
     * Roles que pueden gestionar el CRUD completo de docentes.
     */
    private const ROLES_PERMITIDOS = ['admin', 'supervisor'];

    /**
     * Verifica que el usuario autenticado tenga permiso para gestionar docentes.
     * Devuelve una respuesta 403 si no tiene permiso, o null si puede continuar.
     */
    private function verificarPermiso(Request $request): ?JsonResponse
    {
        if (!in_array($request->user()->rol, self::ROLES_PERMITIDOS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para gestionar docentes.',
            ], 403);
        }

        return null;
    }

    /**
     * GET /api/docentes
     * Lista los docentes. Permite filtrar por estado y buscar por nombre/ci/email.
     */
    public function index(Request $request): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $query = Docente::with('user');

        // Filtro por estado del docente (activo/inactivo). Por defecto solo activos.
        $estado = $request->query('estado', 'activo');
        if (in_array($estado, ['activo', 'inactivo'], true)) {
            $query->where('estado', $estado);
        }
        // Si se pasa estado=todos, no se filtra.

        // Búsqueda simple por nombre, ci o email del usuario relacionado.
        if ($busqueda = $request->query('buscar')) {
            $query->whereHas('user', function ($q) use ($busqueda) {
                $q->where('nombre_completo', 'like', "%{$busqueda}%")
                  ->orWhere('ci', 'like', "%{$busqueda}%")
                  ->orWhere('email', 'like', "%{$busqueda}%");
            });
        }

        $docentes = $query->orderBy('id', 'desc')->paginate(
            $request->query('per_page', 15)
        );

        return response()->json([
            'success' => true,
            'data'    => $docentes,
        ]);
    }

    /**
     * POST /api/docentes
     * Crea el User (rol=docente) y su perfil Docente en una sola transacción.
     */
    public function store(Request $request): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $validator = Validator::make($request->all(), [
            // Datos del usuario
            'nombre_completo' => ['required', 'string', 'max:150'],
            'email'           => ['required', 'email', 'max:150', 'unique:users,email'],
            'ci'              => ['required', 'string', 'max:20', 'unique:users,ci'],
            'password'        => ['required', 'string', 'min:8'],

            // Datos del docente
            'departamento'    => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();

        try {
            $docente = DB::transaction(function () use ($datos) {
                $user = User::create([
                    'nombre_completo' => $datos['nombre_completo'],
                    'email'           => $datos['email'],
                    'ci'              => $datos['ci'],
                    'password'        => $datos['password'], // se hashea vía cast 'hashed'
                    'rol'             => 'docente',
                    'estado'          => 'activo',
                    'primer_login'    => true,
                ]);

                return Docente::create([
                    'id_user'      => $user->id,
                    'departamento' => $datos['departamento'],
                    'estado'       => 'activo',
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Docente creado correctamente.',
                'data'    => $docente->load('user'),
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al crear el docente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/docentes/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json([
                'success' => false,
                'message' => 'Docente no encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $docente,
        ]);
    }

    /**
     * PUT/PATCH /api/docentes/{id}
     * Actualiza datos del docente y, si vienen, datos del usuario asociado.
     * No permite cambiar password ni device_id desde aquí (eso va por endpoints propios de auth).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json([
                'success' => false,
                'message' => 'Docente no encontrado.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_completo' => ['sometimes', 'string', 'max:150'],
            'email'           => [
                'sometimes', 'email', 'max:150',
                Rule::unique('users', 'email')->ignore($docente->id_user),
            ],
            'ci' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('users', 'ci')->ignore($docente->id_user),
            ],
            'departamento' => ['sometimes', 'string', 'max:100'],
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $datos = $validator->validated();

        try {
            DB::transaction(function () use ($datos, $docente) {
                $datosUsuario = array_intersect_key(
                    $datos,
                    array_flip(['nombre_completo', 'email', 'ci'])
                );

                if (!empty($datosUsuario)) {
                    $docente->user->update($datosUsuario);
                }

                if (array_key_exists('departamento', $datos)) {
                    $docente->update(['departamento' => $datos['departamento']]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Docente actualizado correctamente.',
                'data'    => $docente->fresh('user'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el docente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * DELETE /api/docentes/{id}
     * Eliminación lógica: marca estado='inactivo' en Docente y en su User.
     * No borra el registro físicamente.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json([
                'success' => false,
                'message' => 'Docente no encontrado.',
            ], 404);
        }

        if ($docente->estado === 'inactivo') {
            return response()->json([
                'success' => false,
                'message' => 'El docente ya se encuentra inactivo.',
            ], 409);
        }

        try {
            DB::transaction(function () use ($docente) {
                $docente->update(['estado' => 'inactivo']);
                $docente->user()->update(['estado' => 'inactivo']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Docente desactivado correctamente.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al desactivar el docente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * PATCH /api/docentes/{id}/reactivar
     * Reactiva un docente previamente desactivado (contraparte de destroy).
     * Añádelo a routes/api.php si lo necesitas:
     * Route::patch('/docentes/{id}/reactivar', [DocenteController::class, 'reactivar']);
     */
    public function reactivar(Request $request, int $id): JsonResponse
    {
        if ($denegado = $this->verificarPermiso($request)) {
            return $denegado;
        }

        $docente = Docente::with('user')->find($id);

        if (!$docente) {
            return response()->json([
                'success' => false,
                'message' => 'Docente no encontrado.',
            ], 404);
        }

        DB::transaction(function () use ($docente) {
            $docente->update(['estado' => 'activo']);
            $docente->user()->update(['estado' => 'activo']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Docente reactivado correctamente.',
            'data'    => $docente->fresh('user'),
        ]);
    }
}