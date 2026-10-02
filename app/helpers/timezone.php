<?php
/**
 * Helper de Zona Horaria - Costa Rica (America/Costa_Rica / UTC-6)
 * Toda la aplicación debe usar estas funciones para manejo de fechas.
 */

define('CR_TIMEZONE', 'America/Costa_Rica');

/**
 * Retorna la zona horaria de Costa Rica como objeto DateTimeZone
 */
function crTimezone(): DateTimeZone {
    return new DateTimeZone(CR_TIMEZONE);
}

/**
 * Retorna la fecha/hora actual en zona Costa Rica
 */
function crNow(): DateTime {
    return new DateTime('now', crTimezone());
}

/**
 * Retorna la fecha de hoy en Costa Rica (formato Y-m-d)
 */
function crToday(): string {
    return crNow()->format('Y-m-d');
}

/**
 * Retorna la fecha/hora actual en Costa Rica como string (Y-m-d H:i:s)
 */
function crNowStr(): string {
    return crNow()->format('Y-m-d H:i:s');
}

/**
 * Retorna el año actual en Costa Rica
 */
function crYear(): int {
    return (int)crNow()->format('Y');
}

/**
 * Retorna el mes actual en Costa Rica
 */
function crMonth(): int {
    return (int)crNow()->format('n');
}

/**
 * Retorna el inicio y fin de un mes dado en zona Costa Rica
 * @return array ['inicio' => 'Y-m-d 00:00:00', 'fin' => 'Y-m-d 23:59:59']
 */
function crMonthRange(int $year, int $month): array {
    $inicio = new DateTime("{$year}-{$month}-01 00:00:00", crTimezone());
    $fin = clone $inicio;
    $fin->modify('last day of this month')->setTime(23, 59, 59);
    return [
        'inicio' => $inicio->format('Y-m-d H:i:s'),
        'fin'    => $fin->format('Y-m-d H:i:s'),
    ];
}

/**
 * Formatea una fecha/string a zona Costa Rica
 */
function crFormat(string $datetime, string $format = 'd/m/Y'): string {
    $dt = new DateTime($datetime, crTimezone());
    return $dt->format($format);
}

/**
 * Formatea una fecha/string a zona Costa Rica con hora
 */
function crFormatFull(string $datetime): string {
    return crFormat($datetime, 'd/m/Y H:i');
}

/**
 * Retorna el nombre del mes en español
 */
function nombreMes(int $month): string {
    $meses = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
    ];
    return $meses[$month] ?? '';
}
