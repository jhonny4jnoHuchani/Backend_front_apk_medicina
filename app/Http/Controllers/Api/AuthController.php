<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
            'device_id' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if ($user->estado === 'inactivo') {
            return response()->json([
                'message' => 'Usuario inactivo'
            ], 403);
        }

        // Primer login — registrar device_id
        if ($user->primer_login || !$user->device_id) {
            $user->update([
                'device_id'   => $request->device_id,
                'primer_login'=> false,
            ]);
        }

        $user->update(['ultimo_acceso' => now()]);

        $token = $user->createToken('apk-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'             => $user->id,
                'nombre_completo'=> $user->nombre_completo,
                'email'          => $user->email,
                'rol'            => $user->rol,
                'primer_login'   => $user->primer_login,
            ]
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    /**
     * Cambiar contraseña
     */
    public function cambiarPassword(Request $request)
    {
        $request->validate([
            'password_actual' => 'required|string',
            'password_nueva'  => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password_actual, $user->password)) {
            return response()->json([
                'message' => 'Contraseña actual incorrecta'
            ], 400);
        }

        $user->update([
            'password'    => Hash::make($request->password_nueva),
            'primer_login'=> false,
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada correctamente'
        ]);
    }

    /**
     * Perfil del usuario autenticado
     */
    public function perfil(Request $request)
    {
        return response()->json(
            $request->user()->load('docente')
        );
    }
    /**
     * PATCH /api/auth/reset-dispositivo/{id}
     * Resetea el vínculo de dispositivo de un usuario (docente que perdió/cambió de celular).
     * Deja primer_login=true y device_id=null para que en el próximo login
     * quede registrado el nuevo dispositivo. También revoca todos sus tokens
     * activos, para que el celular perdido/robado no pueda seguir usando la app.
     * Solo admin y supervisor pueden hacerlo.
     */
    public function resetDispositivo(Request $request, int $id): JsonResponse
    {
        if (!in_array($request->user()->rol, ['admin', 'supervisor'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permisos para resetear dispositivos.',
            ], 403);
        }

        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        if ($user->estado === 'inactivo') {
            return response()->json([
                'success' => false,
                'message' => 'No se puede resetear el dispositivo de un usuario inactivo.',
            ], 409);
        }

        DB::transaction(function () use ($user) {
            $user->update([
                'device_id'    => null,
                'primer_login' => true,
            ]);

            // Revoca todos los tokens activos: el dispositivo perdido/robado
            // deja de tener acceso inmediatamente.
            $user->tokens()->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo reseteado. El usuario deberá volver a iniciar sesión desde su nuevo dispositivo.',
        ]);
    }
}