<?php

require_once __DIR__ . '/../vendor/autoload.php';


/* =====================================================
   INFORME PDF DE LA COMPARATIVA (NG ASESORES)
   =====================================================
   Lo genera views/tarifas/exportar_comparativa_pdf.php a
   partir de prepararComparativaCliente() (includes/
   comparativa.php): el mismo cálculo que se ve en pantalla.

   FPDF solo entiende Windows-1252, así que todo el texto pasa
   por textoPdf() (iconv), que además conserva el símbolo "€"
   (utf8_decode(), que usan los listados, lo convertiría en
   "?").
========================================================= */


// -------------------------------------------------------
// Datos de la empresa que firma el informe
// -------------------------------------------------------
// FICTICIOS: simulación del diseño corporativo de NG
// Asesores. Sustituir por los reales cuando se tengan.

const EMPRESA_INFORME = [
    'nombre'       => 'NG Asesores',
    'razon_social' => 'NG Asesores Energéticos, S.L.',
    'eslogan'      => 'Asesoría energética para hogares y empresas',
    'cif'          => 'B-84512367',
    'direccion'    => 'Calle de Alcalá, 142, 3.º B',
    'cp_ciudad'    => '28009 Madrid',
    'telefono'     => '910 45 67 89',
    'email'        => 'contacto@ngasesores.es',
    'web'          => 'www.ngasesores.es',
];

// Colores corporativos (RGB): azul marino y verde de ahorro.
const COLOR_PDF_PRINCIPAL = [16, 47, 91];
const COLOR_PDF_ACENTO = [22, 132, 91];
const COLOR_PDF_ACENTO_SUAVE = [233, 247, 240];
const COLOR_PDF_GRIS = [110, 120, 135];
const COLOR_PDF_FONDO = [243, 246, 250];
const COLOR_PDF_LINEA = [214, 220, 229];


/**
 * Texto UTF-8 -> Windows-1252 para FPDF (con "€").
 */
function textoPdf(string $texto): string
{
    $convertido = iconv('UTF-8', 'windows-1252//TRANSLIT', $texto);

    return $convertido !== false ? $convertido : $texto;
}


class ComparativaPDF extends FPDF
{
    // Ancho útil de la página (A4 vertical con márgenes de 15 mm).
    public const ANCHO = 180;

    public string $referencia = '';


    function __construct()
    {
        parent::__construct('P', 'mm', 'A4');

        $this->SetMargins(15, 15, 15);
        $this->SetAutoPageBreak(true, 19);
        $this->AliasNbPages();
    }


    private function color(array $rgb, string $tipo = 'texto'): void
    {
        match ($tipo) {
            'relleno' => $this->SetFillColor(...$rgb),
            'linea'   => $this->SetDrawColor(...$rgb),
            default   => $this->SetTextColor(...$rgb),
        };
    }


    /**
     * Cabecera de todas las páginas: logo, nombre y datos de
     * contacto de la empresa.
     */
    function Header(): void
    {
        // Logo: cuadrado azul marino con "NG" y franja verde.
        $this->color(COLOR_PDF_PRINCIPAL, 'relleno');
        $this->Rect(15, 12, 20, 20, 'F');

        $this->color(COLOR_PDF_ACENTO, 'relleno');
        $this->Rect(15, 30, 20, 2, 'F');

        $this->SetFont('Helvetica', 'B', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(15, 15);
        $this->Cell(20, 12, 'NG', 0, 0, 'C');

        // Nombre y eslogan.
        $this->SetXY(39, 13);
        $this->SetFont('Helvetica', 'B', 19);
        $this->color(COLOR_PDF_PRINCIPAL);
        $this->Cell(80, 9, textoPdf(EMPRESA_INFORME['nombre']));

        $this->SetXY(39, 22);
        $this->SetFont('Helvetica', '', 8.5);
        $this->color(COLOR_PDF_GRIS);
        $this->Cell(80, 5, textoPdf(EMPRESA_INFORME['eslogan']));

        // Datos de contacto, a la derecha.
        $lineas = [
            EMPRESA_INFORME['razon_social'] . ' · CIF ' . EMPRESA_INFORME['cif'],
            EMPRESA_INFORME['direccion'] . ', ' . EMPRESA_INFORME['cp_ciudad'],
            'Tel. ' . EMPRESA_INFORME['telefono'] . ' · ' . EMPRESA_INFORME['email'],
            EMPRESA_INFORME['web'],
        ];

        $this->SetFont('Helvetica', '', 7.5);

        foreach ($lineas as $indice => $linea) {
            $this->SetXY(105, 13 + $indice * 4.2);
            $this->Cell(90, 4.2, textoPdf($linea), 0, 0, 'R');
        }

        // Línea bajo la cabecera.
        $this->color(COLOR_PDF_PRINCIPAL, 'linea');
        $this->SetLineWidth(0.6);
        $this->Line(15, 37, 195, 37);
        $this->SetLineWidth(0.2);

        $this->SetXY(15, 41);
        $this->SetTextColor(0, 0, 0);
    }


    /**
     * Pie de todas las páginas.
     */
    function Footer(): void
    {
        $this->SetY(-16);

        $this->color(COLOR_PDF_LINEA, 'linea');
        $this->Line(15, $this->GetY(), 195, $this->GetY());

        $this->Ln(2);
        $this->SetFont('Helvetica', '', 7);
        $this->color(COLOR_PDF_GRIS);

        $this->Cell(130, 5, textoPdf(
            EMPRESA_INFORME['razon_social'] . ' · ' . EMPRESA_INFORME['web']
            . ($this->referencia !== '' ? ' · Ref. ' . $this->referencia : '')
        ));

        $this->Cell(50, 5, textoPdf('Página ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }


    /**
     * Título del documento con su subtítulo y, a la derecha,
     * fecha y referencia.
     */
    function Titulo(string $titulo, string $subtitulo, string $fecha): void
    {
        $y = $this->GetY();

        $this->SetFont('Helvetica', 'B', 15);
        $this->color(COLOR_PDF_PRINCIPAL);
        $this->Cell(120, 8, textoPdf($titulo));

        $this->SetFont('Helvetica', '', 8.5);
        $this->color(COLOR_PDF_GRIS);
        $this->Cell(60, 5, textoPdf('Fecha: ' . $fecha), 0, 2, 'R');
        $this->Cell(60, 5, textoPdf('Ref.: ' . $this->referencia), 0, 0, 'R');

        $this->SetXY(15, $y + 8);
        $this->SetFont('Helvetica', '', 10);
        $this->Cell(120, 6, textoPdf($subtitulo));

        $this->Ln(7);
    }


    /**
     * Banda de sección (fondo azul, texto blanco).
     */
    function Seccion(string $titulo): void
    {
        // Que la banda no se quede sola al final de una página.
        if ($this->GetY() > 250) {
            $this->AddPage();
        }

        $this->color(COLOR_PDF_PRINCIPAL, 'relleno');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->Cell(self::ANCHO, 6.5, textoPdf('  ' . mb_strtoupper($titulo, 'UTF-8')), 0, 1, 'L', true);
        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }


    /**
     * Pares "etiqueta: valor" en dos columnas.
     *
     * @param array $pares [[etiqueta, valor], ...]
     */
    function DatosEnColumnas(array $pares): void
    {
        $anchoColumna = self::ANCHO / 2;

        foreach (array_chunk($pares, 2) as $fila) {

            foreach ($fila as [$etiqueta, $valor]) {

                $this->SetFont('Helvetica', '', 7.5);
                $this->color(COLOR_PDF_GRIS);
                $this->Cell(32, 5.2, textoPdf($etiqueta));

                $this->SetFont('Helvetica', 'B', 9);
                $this->SetTextColor(20, 30, 50);
                $this->Cell($anchoColumna - 32, 5.2, textoPdf($valor));

            }

            $this->Ln();

        }

        $this->SetTextColor(0, 0, 0);
        $this->Ln(2);
    }


    /**
     * Tabla con cabecera sombreada.
     *
     * @param string[] $cabeceras
     * @param float[]  $anchos      (deben sumar self::ANCHO)
     * @param array[]  $filas       valores ya formateados
     * @param string[] $alineaciones 'L' / 'C' / 'R' por columna
     * @param int[]    $filasNegrita índices de filas en negrita
     * @param int|null $filaResaltada índice de la fila en verde
     */
    function Tabla(array $cabeceras, array $anchos, array $filas, array $alineaciones, array $filasNegrita = [], ?int $filaResaltada = null): void
    {
        $this->color(COLOR_PDF_LINEA, 'linea');

        $this->SetFont('Helvetica', 'B', 8);
        $this->color(COLOR_PDF_FONDO, 'relleno');
        $this->color(COLOR_PDF_PRINCIPAL);

        foreach ($cabeceras as $indice => $cabecera) {
            $this->Cell($anchos[$indice], 6.5, textoPdf($cabecera), 'B', 0, $alineaciones[$indice], true);
        }

        $this->Ln();

        foreach ($filas as $numero => $fila) {

            $resaltada = $numero === $filaResaltada;

            $this->SetFont('Helvetica', in_array($numero, $filasNegrita, true) || $resaltada ? 'B' : '', 8.5);
            $this->SetTextColor(20, 30, 50);
            $this->color($resaltada ? COLOR_PDF_ACENTO_SUAVE : [255, 255, 255], 'relleno');

            foreach ($fila as $indice => $valor) {
                $this->Cell($anchos[$indice], 5.6, textoPdf((string) $valor), 'B', 0, $alineaciones[$indice], true);
            }

            $this->Ln();

        }

        $this->SetTextColor(0, 0, 0);
        $this->Ln(3);
    }


    /**
     * Recuadro destacado (ahorro o aviso).
     */
    function Destacado(string $titulo, string $texto, bool $positivo = true): void
    {
        $this->color($positivo ? COLOR_PDF_ACENTO_SUAVE : COLOR_PDF_FONDO, 'relleno');
        $this->color($positivo ? COLOR_PDF_ACENTO : COLOR_PDF_GRIS, 'linea');
        $this->SetLineWidth(0.4);

        $y = $this->GetY();
        $this->Rect(15, $y, self::ANCHO, 15, 'DF');
        $this->SetLineWidth(0.2);

        $this->SetXY(20, $y + 2);
        $this->SetFont('Helvetica', 'B', 11);
        $this->color($positivo ? COLOR_PDF_ACENTO : COLOR_PDF_PRINCIPAL);
        $this->Cell(self::ANCHO - 10, 6, textoPdf($titulo));

        $this->SetXY(20, $y + 8);
        $this->SetFont('Helvetica', '', 9);
        $this->SetTextColor(20, 30, 50);
        $this->Cell(self::ANCHO - 10, 5, textoPdf($texto));

        $this->SetXY(15, $y + 18);
        $this->SetTextColor(0, 0, 0);
    }


    /**
     * Notas en letra pequeña gris.
     */
    function Notas(array $notas): void
    {
        $this->SetFont('Helvetica', '', 7);
        $this->color(COLOR_PDF_GRIS);

        foreach ($notas as $nota) {
            $this->MultiCell(self::ANCHO, 3.4, textoPdf('· ' . $nota));
        }

        $this->SetTextColor(0, 0, 0);
    }
}
