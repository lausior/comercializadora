<?php

require_once __DIR__ . '/../vendor/autoload.php';


/**
 * PDF genérico para exportar un listado (Empresas, Usuarios,
 * Clientes...) como una tabla simple: cabecera con el título
 * y la fecha, una fila de encabezados de columna, y una fila
 * por cada registro.
 *
 * FPDF (la versión clásica de setasign/fpdf) no entiende UTF-8,
 * así que todo el texto que se le pase debe pasar antes por
 * utf8_decode() - lo hace internamente TablaListado(), no hace
 * falta hacerlo en cada script que use esta clase.
 */
class ListadoPDF extends FPDF
{
    public string $tituloDocumento = 'Listado';

    function Header(): void
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 9, utf8_decode($this->tituloDocumento), 0, 1, 'C');

        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 6, utf8_decode('Generado el ' . date('d/m/Y H:i')), 0, 1, 'C');
        $this->SetTextColor(0, 0, 0);

        $this->Ln(4);
    }

    function Footer(): void
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 10, utf8_decode('Página ' . $this->PageNo() . '/{nb}'), 0, 0, 'C');
    }

    /**
     * @param string[] $cabeceras Nombres de columna
     * @param float[]  $anchos    Ancho de cada columna en mm (debe sumar el ancho útil de la página)
     * @param array[]  $filas     Una fila por registro, con tantos valores como columnas
     */
    function TablaListado(array $cabeceras, array $anchos, array $filas): void
    {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(230, 236, 245);

        foreach ($cabeceras as $indice => $cabecera) {
            $this->Cell($anchos[$indice], 8, utf8_decode($cabecera), 1, 0, 'C', true);
        }

        $this->Ln();

        $this->SetFont('Arial', '', 8);

        if (empty($filas)) {

            $this->Cell(array_sum($anchos), 10, utf8_decode('No hay resultados para exportar.'), 1, 1, 'C');

            return;

        }

        foreach ($filas as $fila) {

            foreach ($fila as $indice => $valor) {
                $this->Cell($anchos[$indice], 7, utf8_decode((string) $valor), 1, 0, 'L');
            }

            $this->Ln();

        }

    }
}


/**
 * A partir de la lista de ids que llega por POST (string separada
 * por comas), devuelve un array de enteros positivos únicos.
 * Usado por los tres scripts de exportación para no fiarse a
 * ciegas de lo que manda el cliente antes de volver a comprobar
 * permisos fila a fila.
 */
function idsDesdePost(string $clave): array
{
    if (empty($_POST[$clave])) {
        return [];
    }

    $ids = array_map('intval', explode(',', $_POST[$clave]));

    $ids = array_filter($ids, fn(int $id): bool => $id > 0);

    return array_values(array_unique($ids));
}


/**
 * Reordena $filas (resultado de un SELECT ... WHERE id IN (...),
 * en el orden que decida el motor de base de datos) para que
 * queden en el mismo orden que $ids.
 *
 * $ids ya viene en el orden que tenía la tabla en el navegador en
 * el momento de exportar - incluida la ordenación por columna que
 * haya aplicado el usuario (los botones ↕ reordenan ese mismo
 * array en el listado, ver obtenerFilasFiltradas() en cada .js) -
 * así que el PDF respeta ese orden en vez de imponer uno fijo.
 */
function ordenarSegunIds(array $filas, array $ids): array
{
    $filasPorId = [];

    foreach ($filas as $fila) {
        $filasPorId[(int) $fila['id']] = $fila;
    }

    $ordenadas = [];

    foreach ($ids as $id) {

        if (isset($filasPorId[$id])) {
            $ordenadas[] = $filasPorId[$id];
        }

    }

    return $ordenadas;
}
