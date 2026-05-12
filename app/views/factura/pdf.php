<?php
ob_start();

$viewData = get_defined_vars();
$factura = is_array($viewData['factura'] ?? null) ? $viewData['factura'] : [];

$fpdfCandidates = [
    __DIR__ . '/../../../libs/fpdf.php',
    __DIR__ . '/../../../vendor/setasign/fpdf/fpdf.php',
];

$fpdfPath = null;
foreach ($fpdfCandidates as $candidate) {
    if (file_exists($candidate)) {
        $fpdfPath = $candidate;
        break;
    }
}

if ($fpdfPath === null) {
    $id_factura = $factura['id_factura'] ?? 0;
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Factura #<?= $id_factura ?></title>
        <style>
            body { font-family: "Segoe UI", Arial, sans-serif; margin: 24px; color: #111827; background: #f6f8fb; }
            .box { border: 1px solid #d8dee7; border-radius: 8px; padding: 16px; max-width: 800px; background: #ffffff; }
            h2 { color: #102a43; text-align: center; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            th, td { border: 1px solid #d8dee7; padding: 8px; text-align: center; vertical-align: middle; }
            th { background: #102a43; color: #ffffff; }
            .right { text-align: right; }
        </style>
    </head>
    <body>
    <div class="box">
        <h2>Factura #<?= str_pad($id_factura, 6, "0", STR_PAD_LEFT) ?></h2>
        <p><strong>Atendido por:</strong> <?= htmlspecialchars($factura['nombre_usuario'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Deportista:</strong> <?= htmlspecialchars($factura['nombre_deportista'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Evento:</strong> <?= htmlspecialchars($factura['nombre_evento'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Tipo de pago:</strong> <?= htmlspecialchars(strtoupper($factura['metodo_pago_texto'] ?? 'N/A'), ENT_QUOTES, 'UTF-8') ?></p>
        <table>
            <tr>
                <th>Concepto</th>
                <th class="right">Monto</th>
            </tr>
            <tr>
                <td>Inscripción</td>
                <td class="right">$<?= number_format($factura['total'] ?? $factura['monto'] ?? 0, 2) ?></td>
            </tr>
        </table>
    </div>
    <script>window.print();</script>
    </body>
    </html>
    <?php
    exit;
}

require_once $fpdfPath;

class PDF extends FPDF {
    function Header() {
        $this->SetTextColor(16, 42, 67);
        $this->SetFont('Arial', 'B', 16); 
        $this->Cell(190, 10, utf8_decode('REPORTE DE FACTURACIÓN'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8); 
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12); 

$id_factura   = $factura['id_factura'] ?? 0;
$atendido_por = $factura['nombre_usuario'] ?? 'N/A';
$deportista   = $factura['nombre_deportista'] ?? 'N/A';
$evento       = $factura['nombre_evento'] ?? 'N/A'; // <-- Aquí está el texto del evento
$metodo_pago  = $factura['metodo_pago_texto'] ?? 'N/A';
$total_monto   = $factura['total'] ?? $factura['monto'] ?? 0; 

// --- Encabezado ---
$pdf->SetFillColor(216, 222, 231);
$pdf->SetTextColor(16, 42, 67);
$pdf->Cell(190, 10, utf8_decode("Factura N°: " . str_pad($id_factura, 6, "0", STR_PAD_LEFT)), 1, 1, 'L', true);
$pdf->Ln(5);

$pdf->Cell(95, 8, utf8_decode("Atendido por: " . $atendido_por), 0, 0);
$pdf->Cell(95, 8, utf8_decode("Deportista: " . $deportista), 0, 1);
$pdf->Cell(95, 8, utf8_decode("Tipo de Pago: " . strtoupper($metodo_pago)), 0, 0);

// 🚨 CORRECCIÓN AQUÍ: Pinta el nombre del evento en lugar del 0
$pdf->Cell(95, 8, utf8_decode("Evento: " . $evento), 0, 1); 
$pdf->Ln(10);

// --- Tabla ---
$pdf->SetFont('Arial', 'B', 12); 
$pdf->SetFillColor(16, 42, 67); 
$pdf->SetTextColor(255, 255, 255); 

$pdf->Cell(130, 10, utf8_decode('Descripción del Servicio / Evento'), 1, 0, 'C', true);
$pdf->Cell(60, 10, utf8_decode('Monto'), 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 12); 
$pdf->SetTextColor(0, 0, 0); 

$pdf->Cell(130, 10, utf8_decode("Inscripción: " . $evento), 1, 0, 'L');
$pdf->Cell(60, 10, "$" . number_format($total_monto, 2), 1, 1, 'R');

$pdf->SetFont('Arial', 'B', 12); 
$pdf->Cell(130, 10, 'TOTAL', 1, 0, 'R');
$pdf->Cell(60, 10, "$" . number_format($total_monto, 2), 1, 1, 'R');

ob_end_clean(); 
$pdf->Output('I', 'Factura_' . $id_factura . '.pdf'); 
exit;
