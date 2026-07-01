<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
}