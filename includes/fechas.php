<?php

/**
 * Nombres de mes en español, para no depender de la
 * configuración regional del servidor (setlocale) ni de la
 * extensión intl, que puede no estar activada.
 */
const MESES_ES = [
    1  => 'enero',
    2  => 'febrero',
    3  => 'marzo',
    4  => 'abril',
    5  => 'mayo',
    6  => 'junio',
    7  => 'julio',
    8  => 'agosto',
    9  => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre',
];

function nombreMesEs(int $mes): string
{
    return MESES_ES[$mes] ?? '';
}

function fechaLargaEs(DateTimeInterface $fecha): string
{
    return $fecha->format('j') . ' de ' . nombreMesEs((int) $fecha->format('n')) . ' de ' . $fecha->format('Y');
}
