<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/config/conexion.php';
require_once $projectRoot . '/app/models/ReporteModel.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ReporteController {
    public function descargar(string $tablaNombre, string $formato = 'xlsx'): void {
        $modelo = new ReporteModel();
        $datos = $modelo->obtenerDatos($tablaNombre);
        if (!$datos) exit("La tabla seleccionada no tiene datos.");

        if ($formato === 'pdf') {
            $this->generarPDF($tablaNombre, $datos);
        } else {
            $this->generarExcelCSV($tablaNombre, $datos, $formato);
        }
    }

    /**
     * @param array<int,array<string,mixed>> $datos
     */
    private function generarPDF(string $tabla, array $datos): void {
        $columnas = array_keys($datos[0]);
        $fecha = date('d/m/Y H:i');

        // Dise??o HTML para el PDF
        $html = "
        <style>
            body { font-family: 'Helvetica', sans-serif; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #0D6EFD; padding-bottom: 10px; margin-bottom: 20px; }
            .info { font-size: 12px; color: #666; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; font-size: 11px; }
            th { background-color: #0D6EFD; color: white; padding: 10px; text-transform: uppercase; }
            td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            .footer { text-align: right; font-size: 10px; margin-top: 20px; color: #999; }
        </style>
        
        <div class='header'>
            <h1>REPORTE DE SISTEMA</h1>
            <h2 style='color: #0D6EFD;'>TABLA: " . strtoupper($tabla) . "</h2>
        </div>
        
        <div class='info'>Fecha de generaci??n: $fecha</div>

        <table>
            <thead>
                <tr>";
        foreach ($columnas as $col) { $html .= "<th>" . htmlspecialchars((string) $col, ENT_QUOTES, 'UTF-8') . "</th>"; }
        $html .= "</tr>
            </thead>
            <tbody>";
        foreach ($datos as $fila) {
            $html .= "<tr>";
            foreach ($fila as $valor) { $html .= "<td>" . htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8') . "</td>"; }
            $html .= "</tr>";
        }
        $html .= "</tbody>
        </table>
        <div class='footer'>Generado autom??ticamente por SGA - P??gina {PAGENO}</div>";

        $mpdf = new \Mpdf\Mpdf(['orientation' => 'L', 'margin_left' => 10, 'margin_right' => 10]);
        $mpdf->WriteHTML($html);
        $mpdf->Output("Reporte_$tabla.pdf", 'D');
        exit;
    }

    /**
     * @param array<int,array<string,mixed>> $datos
     */
    private function generarExcelCSV(string $tabla, array $datos, string $formato): void {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $columnas = array_keys($datos[0]);

        foreach ($columnas as $i => $nombre) {
            $col = $i + 1;
            $sheet->setCellValue([$col, 1], strtoupper($nombre));
            $sheet->getStyle([$col, 1])->getFont()->setBold(true);
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $sheet->fromArray($datos, null, 'A2');

        if ($formato === 'csv') {
            $writer = new Csv($spreadsheet);
            header('Content-Type: text/csv');
        } else {
            $writer = new Xlsx($spreadsheet);
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        }

        header("Content-Disposition: attachment; filename=\"Reporte_$tabla.$formato\"");
        $writer->save('php://output');
        exit;
    }
}



