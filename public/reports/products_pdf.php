<?php
require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../app/Database.php';
require_once __DIR__ . '/../../app/auth.php';

require_role('admin');

require_once __DIR__ . '/../../lib/fpdf/fpdf.php';

$pdo = Database::pdo();

$rows = $pdo->query("
  SELECT p.ID_Produs, p.Nume, p.Pret, c.Nume AS Categorie
  FROM Produs p
  JOIN CategoriiProduse c ON c.ID_Categorie = p.ID_Categorie
  ORDER BY c.Nume ASC, p.Nume ASC
")->fetchAll();

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Hypermarket Activities - Raport Produse', 0, 1, 'C');

$pdf->Ln(4);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Data generare: ' . date('Y-m-d H:i'), 0, 1);

$pdf->Ln(4);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(15, 7, 'ID', 1);
$pdf->Cell(80, 7, 'Nume', 1);
$pdf->Cell(35, 7, 'Pret', 1);
$pdf->Cell(60, 7, 'Categorie', 1);
$pdf->Ln();

// Rows
$pdf->SetFont('Arial', '', 10);
foreach ($rows as $r) {
    $pdf->Cell(15, 7, (string)$r['ID_Produs'], 1);
    $pdf->Cell(80, 7, substr((string)$r['Nume'], 0, 40), 1);
    $pdf->Cell(35, 7, number_format((float)$r['Pret'], 2) . ' RON', 1);
    $pdf->Cell(60, 7, substr((string)$r['Categorie'], 0, 30), 1);
    $pdf->Ln();
}

$pdf->Output('I', 'raport_produse.pdf');
