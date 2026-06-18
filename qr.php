<?php
require __DIR__.'/includes/auth.php';
requireAuth($pdo);

require __DIR__.'/lib/fpdf.php';
define('FPDF_FONTPATH', __DIR__.'/lib/font/');

$id = (int)($_GET['id'] ?? 0);
$et = getEtudiantWithDroit($pdo, $id);
if (!$et) { http_response_code(404); exit('Étudiant introuvable'); }

$qrText = qrCodeContent($et);
$qrPng = fetchQrPng($qrText, 500);

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();

// Gold band
$pdf->SetFillColor(212,168,76);
$pdf->Rect(0,0,210,28,'F');
$pdf->SetTextColor(26,18,8);
$pdf->SetFont('Helvetica','B',20);
$pdf->SetXY(15,9);
$pdf->Cell(0,10,'TAFARAY-SENI',0,0,'L');
$pdf->SetFont('Helvetica','',10);
$pdf->SetXY(15,18);
$pdf->Cell(0,5,'Reception des Novices - 2026',0,0,'L');

// Carte identique au QR ALL

$marginX = 15;
$y = 60;
$rowH = 38;

// Card background
$pdf->SetDrawColor(220,220,220);
$pdf->SetLineWidth(0.2);
$pdf->Rect($marginX, $y+2, 210-2*$marginX, $rowH-4);

// QR
$pdf->Image($qrPng, $marginX+3, $y+4, 30, 30, 'PNG');

// Code
$code = "SENI-2026-{$et['NomEt']}.{$et['PrenomEt']}";

$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Helvetica','B',11);
$pdf->SetXY($marginX+38, $y+5);
$pdf->Cell(0,5,utf8_decode($code),0,1);

// Footer band
$pdf->SetY(265);
$pdf->SetFillColor(15,23,42);
$pdf->Rect(0,265,210,32,'F');
$pdf->SetTextColor(212,168,76);
$pdf->SetFont('Helvetica','B',9);
$pdf->SetXY(15,272);
$pdf->Cell(0,5,'TAFARAY-SENI 2026',0,1);
$pdf->SetTextColor(180,180,180);
$pdf->SetFont('Helvetica','',8);
$pdf->SetX(15);
$pdf->Cell(0,5,utf8_decode('Document officiel - Gestion de la reception des novices'),0,1);

@unlink($qrPng);
$pdf->Output('I', 'QR_'.$et['NomEt'].'_'.$et['PrenomEt'].'.pdf');
