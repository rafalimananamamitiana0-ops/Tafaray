<?php
require __DIR__.'/includes/auth.php';
requireAuth($pdo);

require __DIR__.'/lib/fpdf.php';
define('FPDF_FONTPATH', __DIR__.'/lib/font/');

$rows = allEtudiantsWithDroit($pdo);
if (!$rows) { http_response_code(404); exit('Aucun etudiant.'); }

$pdf = new FPDF('P','mm','A4');
$pdf->SetAutoPageBreak(false);

// Layout: A4 = 210 x 297 mm. 7 QR per page, 1 per row.
// Top header 22mm. Available height ~ 275mm / 7 = ~39mm per row.
$rowH = 38; $topY = 22; $marginX = 15;

$temps = [];
$perPage =7; $i = 0;

foreach ($rows as $idx => $et) {
  if ($i % $perPage === 0) {
    $pdf->AddPage();
    // header
    $pdf->SetFillColor(212,168,76);
    $pdf->Rect(0,0,210,16,'F');
    $pdf->SetTextColor(26,18,8);
    $pdf->SetFont('Helvetica','B',13);
    $pdf->SetXY($marginX,4);
    $pdf->Cell(0,8,'TAFARAY-SENI  -  Tous les QR Codes',0,0,'L');
    $pdf->SetFont('Helvetica','',9);
    $pdf->SetXY(-50,5);
    $pdf->Cell(35,6,'Page '.($pdf->PageNo()),0,0,'R');
  }
  $rowIndex = $i % $perPage;
  $y = $topY + $rowIndex * $rowH;

  $qrText = qrCodeContent($et);
  $png = fetchQrPng($qrText, 350);
  $temps[] = $png;

  // Card background
  $pdf->SetDrawColor(220,220,220);
  $pdf->SetLineWidth(0.2);
  $pdf->Rect($marginX, $y+2, 210-2*$marginX, $rowH-4);

  // QR
  $pdf->Image($png, $marginX+3, $y+4, 30, 30, 'PNG');

  // Code header
  $code = "SENI-2026-{$et['NomEt']}.{$et['PrenomEt']}";
  $pdf->SetTextColor(0,0,0);
  $pdf->SetFont('Helvetica','B',11);
  $pdf->SetXY($marginX+38, $y+5);
  $pdf->Cell(0,5,utf8_decode($code),0,1);


  $i++;
}

foreach ($temps as $t) @unlink($t);
$pdf->Output('I','TousLesQR_TAFARAY-SENI.pdf');
