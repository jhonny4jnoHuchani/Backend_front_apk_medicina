<?php

namespace App\Helpers;

class GeocercaHelper
{
    /**
     * Verifica si un punto está dentro de un polígono.
     * Algoritmo Ray Casting.
     *
     * @param float $lat  Latitud del punto
     * @param float $lon  Longitud del punto
     * @param array $poligono  Array de puntos [['lat' => x, 'lon' => y], ...]
     * @param int $toleranciaMetros  Margen extra en metros (opcional)
     * @return bool
     */
    public static function puntoDentroDePoligono(float $lat, float $lon, array $poligono, int $toleranciaMetros = 0): bool
    {
        // Si hay tolerancia, agrandamos el polígono (simplificado: si está dentro o muy cerca)
        $dentro = self::rayCasting($lat, $lon, $poligono);
        
        if ($dentro) {
            return true;
        }
        
        // Si no está dentro, verificamos distancia al borde más cercano
        if ($toleranciaMetros > 0) {
            return self::distanciaAlBorde($lat, $lon, $poligono) <= $toleranciaMetros;
        }
        
        return false;
    }

    /**
     * Algoritmo Ray Casting: cuenta cuántas veces un rayo horizontal
     * cruza los bordes del polígono. Si es impar, está dentro.
     */
    private static function rayCasting(float $lat, float $lon, array $poligono): bool
    {
        $n = count($poligono);
        $dentro = false;

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $poligono[$i]['lat'];
            $yi = $poligono[$i]['lon'];
            $xj = $poligono[$j]['lat'];
            $yj = $poligono[$j]['lon'];

            if (($yi > $lon) != ($yj > $lon) &&
                ($lat < ($xj - $xi) * ($lon - $yi) / ($yj - $yi) + $xi)) {
                $dentro = !$dentro;
            }
        }

        return $dentro;
    }

    /**
     * Calcula la distancia más corta desde un punto a cualquier borde del polígono.
     * Retorna la distancia en metros.
     */
    private static function distanciaAlBorde(float $lat, float $lon, array $poligono): float
    {
        $distanciaMinima = PHP_FLOAT_MAX;
        $n = count($poligono);

        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $distancia = self::distanciaPuntoASegmento(
                $lat, $lon,
                $poligono[$i]['lat'], $poligono[$i]['lon'],
                $poligono[$j]['lat'], $poligono[$j]['lon']
            );
            $distanciaMinima = min($distanciaMinima, $distancia);
        }

        return $distanciaMinima;
    }

    /**
     * Distancia de un punto a un segmento de línea, en metros.
     */
    private static function distanciaPuntoASegmento(float $px, float $py, float $x1, float $y1, float $x2, float $y2): float
    {
        // Convertir grados a metros (aproximado, 1° ≈ 111320m en lat, variable en lon)
        $factorLat = 111320.0;
        $factorLon = 111320.0 * cos(deg2rad(($x1 + $x2) / 2));

        $dx = ($x2 - $x1) * $factorLat;
        $dy = ($y2 - $y1) * $factorLon;
        $px = ($px - $x1) * $factorLat;
        $py = ($py - $y1) * $factorLon;

        $segmentoLongitudCuadrado = $dx * $dx + $dy * $dy;

        if ($segmentoLongitudCuadrado == 0) {
            return sqrt($px * $px + $py * $py);
        }

        $t = max(0, min(1, ($px * $dx + $py * $dy) / $segmentoLongitudCuadrado));
        $proyeccionX = $t * $dx;
        $proyeccionY = $t * $dy;

        return sqrt(($px - $proyeccionX) * ($px - $proyeccionX) + ($py - $proyeccionY) * ($py - $proyeccionY));
    }
}