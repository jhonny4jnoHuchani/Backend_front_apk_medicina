<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Helpers\GeocercaHelper;
use App\Models\Marcado;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MarcadoController extends Controller
{
    /**
     * Valida que el usuario sea docente activo y que el horario le pertenezca,
     * esté activo, sea del día correcto y esté dentro de la geocerca.
     */
    private function validarDocenteYHorario(Request $request, int $horarioId): Horario|JsonResponse
    {
        $user = $request->user();
        $docente = $user->docente;

        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'No tienes perfil docente.'], 403);
        }

        if ($docente->estado !== 'activo') {
            return response()->json(['success' => false, 'message' => 'Docente inactivo.'], 403);
        }

        $horario = Horario::with(['paraleloMateria', 'ubicacion'])->find($horarioId);

        if (!$horario || $horario->estado !== 'activo') {
            return response()->json(['success' => false, 'message' => 'Horario no encontrado o inactivo.'], 404);
        }

        if ($horario->paraleloMateria->docente_id !== $docente->id) {
            return response()->json(['success' => false, 'message' => 'Este horario no te pertenece.'], 403);
        }

        $dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        $hoy = $dias[now()->dayOfWeek];

        if ($horario->dia_semana !== $hoy) {
            return response()->json(['success' => false, 'message' => 'Este horario no corresponde al día de hoy.'], 400);
        }

        if ($horario->ubicacion->estado !== 'activo') {
            return response()->json(['success' => false, 'message' => 'La ubicación no está disponible.'], 400);
        }

        $latitud = $request->input('latitud');
        $longitud = $request->input('longitud');

        if ($latitud && $longitud) {
            $poligono = $horario->ubicacion->coordenadas;
            $tolerancia = $horario->ubicacion->tolerancia_metros;

            if (!GeocercaHelper::puntoDentroDePoligono($latitud, $longitud, $poligono, $tolerancia)) {
                return response()->json(['success' => false, 'message' => 'Fuera de la geocerca permitida.'], 400);
            }
        }

        return $horario;
    }

    /**
     * Calcula minutos de retraso y adelanto comparando con una hora de referencia
     */
    private function calcularMinutos(Carbon $ahora, Carbon $horaReferencia): array
    {
        $minutosRetraso = 0;
        $minutosAdelanto = 0;

        $diferencia = round($ahora->diffInMinutes($horaReferencia, true), 2);

        if ($ahora->gt($horaReferencia)) {
            $minutosRetraso = $diferencia;
        } elseif ($ahora->lt($horaReferencia)) {
            $minutosAdelanto = $diferencia;
        }

        return [$minutosRetraso, $minutosAdelanto];
    }

    /**
     * GET /api/marcados/historial
     */
    public function historial(Request $request): JsonResponse
    {
        $docente = $request->user()->docente;

        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'No tienes perfil docente.'], 403);
        }

        $query = Marcado::with('horario.paraleloMateria.materia', 'horario.paraleloMateria.paralelo', 'ubicacion')
            ->where('docente_id', $docente->id)
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_marcado', 'desc');

        if ($fecha = $request->query('fecha')) {
            $query->where('fecha', $fecha);
        }

        if ($mes = $request->query('mes')) {
            $query->whereMonth('fecha', substr($mes, 5, 2))
                  ->whereYear('fecha', substr($mes, 0, 4));
        }

        if ($horarioId = $request->query('horario_id')) {
            $query->where('horario_id', $horarioId);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->paginate(20),
        ]);
    }

    /**
     * GET /api/marcados/hoy
     */
    public function hoy(Request $request): JsonResponse
    {
        $docente = $request->user()->docente;

        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'No tienes perfil docente.'], 403);
        }

        $marcados = Marcado::with('horario.paraleloMateria.materia', 'horario.paraleloMateria.paralelo', 'ubicacion')
            ->where('docente_id', $docente->id)
            ->where('fecha', now()->toDateString())
            ->orderBy('hora_marcado')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $marcados,
        ]);
    }

    /**
     * POST /api/marcados/entrada
     */
    public function entrada(Request $request): JsonResponse
    {
        $request->validate([
            'horario_id'      => 'required|integer|exists:horarios,id',
            'latitud'         => 'nullable|numeric',
            'longitud'        => 'nullable|numeric',
            'foto_constancia' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $resultado = $this->validarDocenteYHorario($request, $request->horario_id);
        if ($resultado instanceof JsonResponse) return $resultado;
        $horario = $resultado;

        $docente = $request->user()->docente;
        $hoy = now()->toDateString();

        $entradaExistente = Marcado::where('docente_id', $docente->id)
            ->where('horario_id', $horario->id)
            ->where('fecha', $hoy)
            ->where('tipo_marcado', 'entrada')
            ->exists();

        if ($entradaExistente) {
            return response()->json(['success' => false, 'message' => 'Ya marcaste entrada hoy para este horario.'], 409);
        }

        $foto = null;
        if ($request->hasFile('foto_constancia')) {
            $foto = $request->file('foto_constancia')->store('marcados', 'public');
        }

        $ahora = now();
        $horaInicioHorario = Carbon::parse($hoy . ' ' . $horario->hora_inicio);

        // Calcular retraso/adelanto comparando con hora_inicio
        [$minutosRetraso, $minutosAdelanto] = $this->calcularMinutos($ahora, $horaInicioHorario);

        $estadoAsistencia = $minutosRetraso > 0 ? 'retraso' : 'puntual';

        $marcado = Marcado::create([
            'docente_id'        => $docente->id,
            'horario_id'        => $horario->id,
            'ubicacion_id'      => $horario->ubicacion_id,
            'fecha'             => $hoy,
            'hora_marcado'      => $ahora->toTimeString(),
            'tipo_marcado'      => 'entrada',
            'latitud'           => $request->latitud,
            'longitud'          => $request->longitud,
            'foto_constancia'   => $foto,
            'estado'            => 'pendiente',
            'estado_asistencia' => $estadoAsistencia,
            'minutos_retraso'   => $minutosRetraso,
            'minutos_adelanto'  => $minutosAdelanto,
            'offline'           => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Entrada registrada correctamente.',
            'data'    => $marcado,
        ], 201);
    }

    /**
     * POST /api/marcados/salida
     */
    public function salida(Request $request): JsonResponse
    {
        $request->validate([
            'horario_id'      => 'required|integer|exists:horarios,id',
            'latitud'         => 'nullable|numeric',
            'longitud'        => 'nullable|numeric',
            'foto_constancia' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $resultado = $this->validarDocenteYHorario($request, $request->horario_id);
        if ($resultado instanceof JsonResponse) return $resultado;
        $horario = $resultado;

        $docente = $request->user()->docente;
        $hoy = now()->toDateString();

        $entrada = Marcado::where('docente_id', $docente->id)
            ->where('horario_id', $horario->id)
            ->where('fecha', $hoy)
            ->where('tipo_marcado', 'entrada')
            ->first();

        if (!$entrada) {
            return response()->json(['success' => false, 'message' => 'Primero debes marcar entrada.'], 400);
        }

        $salidaExistente = Marcado::where('docente_id', $docente->id)
            ->where('horario_id', $horario->id)
            ->where('fecha', $hoy)
            ->where('tipo_marcado', 'salida')
            ->exists();

        if ($salidaExistente) {
            return response()->json(['success' => false, 'message' => 'Ya marcaste salida hoy para este horario.'], 409);
        }

        $foto = null;
        if ($request->hasFile('foto_constancia')) {
            $foto = $request->file('foto_constancia')->store('marcados', 'public');
        }

        $ahora = now();
        $horaFinHorario = Carbon::parse($hoy . ' ' . $horario->hora_fin);

        // Calcular retraso/adelanto comparando con hora_fin
        [$minutosRetraso, $minutosAdelanto] = $this->calcularMinutos($ahora, $horaFinHorario);

        $marcado = Marcado::create([
            'docente_id'       => $docente->id,
            'horario_id'       => $horario->id,
            'ubicacion_id'     => $horario->ubicacion_id,
            'fecha'            => $hoy,
            'hora_marcado'     => $ahora->toTimeString(),
            'tipo_marcado'     => 'salida',
            'latitud'          => $request->latitud,
            'longitud'         => $request->longitud,
            'foto_constancia'  => $foto,
            'estado'           => 'pendiente',
            'minutos_retraso'  => $minutosRetraso,
            'minutos_adelanto' => $minutosAdelanto,
            'offline'          => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Salida registrada correctamente.',
            'data'    => $marcado,
        ], 201);
    }

    /**
     * POST /api/marcados/sync-offline
     */
    public function syncOffline(Request $request): JsonResponse
    {
        $request->validate([
            'marcados'                     => 'required|array',
            'marcados.*.horario_id'        => 'required|integer',
            'marcados.*.tipo_marcado'      => 'required|in:entrada,salida',
            'marcados.*.latitud'           => 'nullable|numeric',
            'marcados.*.longitud'          => 'nullable|numeric',
            'marcados.*.foto_base64'       => 'nullable|string',
            'marcados.*.fecha_dispositivo' => 'required|date',
        ]);

        $docente = $request->user()->docente;

        if (!$docente) {
            return response()->json(['success' => false, 'message' => 'No tienes perfil docente.'], 403);
        }

        $sincronizados = [];

        foreach ($request->marcados as $data) {
            $resultado = $this->validarDocenteYHorario($request, $data['horario_id']);
            if ($resultado instanceof JsonResponse) continue;

            $foto = null;
            if (!empty($data['foto_base64'])) {
                $imagen = base64_decode($data['foto_base64']);
                $nombre = 'marcados/offline_' . uniqid() . '.jpg';
                Storage::disk('public')->put($nombre, $imagen);
                $foto = $nombre;
            }

            $marcado = Marcado::create([
                'docente_id'             => $docente->id,
                'horario_id'             => $data['horario_id'],
                'ubicacion_id'           => $resultado->ubicacion_id,
                'fecha'                  => Carbon::parse($data['fecha_dispositivo'])->toDateString(),
                'hora_marcado'           => Carbon::parse($data['fecha_dispositivo'])->toTimeString(),
                'tipo_marcado'           => $data['tipo_marcado'],
                'latitud'                => $data['latitud'] ?? null,
                'longitud'               => $data['longitud'] ?? null,
                'foto_constancia'        => $foto,
                'estado'                 => 'pendiente',
                'offline'                => true,
                'sincronizacion_offline' => true,
                'fecha_dispositivo'      => $data['fecha_dispositivo'],
            ]);

            $sincronizados[] = $marcado;
        }

        return response()->json([
            'success'       => true,
            'message'       => count($sincronizados) . ' marcados sincronizados.',
            'sincronizados' => count($sincronizados),
            'data'          => $sincronizados,
        ]);
    }
}