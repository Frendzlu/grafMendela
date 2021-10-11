<?php
date_default_timezone_set('Europe/Warsaw');
require_once('./TCPDF-main/tcpdf.php');
require_once('class.php');
class MyPDF extends TCPDF{
    function Header(){
        $this->SetFont('helvetica', '', 12);
        $date = date('d-m-Y H:i:s');
        $this->Cell(0, 15, "The pdf was generated on: $date", 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }
    function Footer(){
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 9);
        $this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Frendzel');
$pdf->SetTitle('BodyTemp');
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

$pdf->SetFont('Helvetica', 'I', 12);
$pdf->AddPage();
$pdf->setJPEGQuality(75);

$pdf->Image('./imagens.png', 15, 10, 180, 80, 'PNG', "./image.php", '', true, 150, '', false, false, 1, false, false, false);

$pdf->Ln(65);
$txt = <<<EOD
    Meanings:                                              Measurements:
        - illness
        - no measurement
EOD;
$pdf->Write(0, $txt, '', 0, 'L', true, 0, false, false, 0);
$pdf->Circle(23, 100.25, 1.5, 0, 360, 'DF', '', array(210, 0, 0));
$pdf->Circle(23, 105.5, 1.5, 0, 360, 'DF', '', array(155, 155, 155));
//tabelka
$pdf->SetFont('Helvetica', 'I', 10);
$pdf->setCellPaddings(0, 0, 0, 0);
$pdf->setCellMargins(0, 0, 0, 0);
$pdf->Multicell(15, 5, "Day", 1, 'C', 0, 1, 130, 93, true, 0, false, true, 0);
$pdf->Multicell(35, 5, "Temp", 1, 'C', 0, 1, 145, 93, true, 0, false, true, 0);
$baza=mysqli_connect("localhost","root","","projectdb");

$mainInstance->data->getAll();
$table = $mainInstance->data->values;

for ($i = 0; $i<count($table);$i++){
    $y = 93 + (($i+1)*5);
    $temperature = strval($table[$i]["Temp"]);
    if($temperature == ""){
        $temperature = "No Measurement";
    }
    if($temperature == 0){
        $temperature = "Ilness";
    }
    $pdf->Multicell(15, 5, strval($i+1),1,'C', 0, 1, 130, $y, true, 0, false, true, 0);
    $pdf->Multicell(35, 5, $temperature , 1, 'C', 0,1, 145, $y, true, 0, false, true, 0);
}

$pdf->Output('output.pdf', 'I');