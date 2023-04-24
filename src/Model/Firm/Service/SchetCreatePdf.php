<?php

namespace App\Model\Firm\Service;


use App\Model\Firm\Entity\Schet\Schet;
use TCPDF;

class SchetCreatePdf
{
    public $pdf;
    private Schet $schet;

    public function __construct(Schet $schet)
    {
// create new PDF document
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
        $this->pdf->SetCreator(PDF_CREATOR);
        $this->pdf->SetAuthor('PartsRu');
        $this->pdf->SetTitle("Счет {$schet->getDocument()->getDocumentNum()}");
        $this->pdf->SetSubject("Счет {$schet->getDocument()->getDocumentNum()}");

// set header and footer fonts
        $this->pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $this->pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
        $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// remove default header/footer
        $this->pdf->setPrintHeader(false);
//$pdf->setPrintFooter(false);

// set margins
        $this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
        $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
        $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


// ---------------------------------------------------------

// set default font subsetting mode
        $this->pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
        $this->pdf->SetFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
        $this->pdf->AddPage();

// set text shadow effect
//$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

        $this->schet = $schet;
    }

    public function setContent($html)
    {
        $html = '
<style>
    * {
        font-size: 10px;
    }

    body {
        line-height: 1.0;
    }

    .w-100 {
        width: 100%;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    strong {
        font-weight: bold;
    }

    table {
        width: 100%;
    }

    .border, .border-bottom {
        border-color: #000000 !important;
    }

    table.border1 {
        width: auto;
        border: 1px solid #000000;
        border-collapse: collapse;
    }

    table.border1 td, table.border1 th {
        border: 1px solid #000000;
        border-collapse: collapse;
        font-size: 10px;
        vertical-align: top;
        padding: 5px;
    }

    table.table-goods {
        border: 2px solid #000000;
    }

    table.table-goods th,
    table.table-goods td {
        vertical-align: top;
        border: 1px solid #000000;
        padding: 3px;
    }

    table.table-goods th {
        text-align: center;
    }

    h2 {
        margin: 0;
        font-weight: bold;
        font-size: 18px;
    }
</style>


        ' . $html;

// Print text using writeHTMLCell()
//$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $this->pdf->writeHTML($html, true, false, true, false, '');
    }

    public function setSignature(string $image)
    {
        $x_pos = $this->pdf->GetX();
        $y_pos = $this->pdf->GetY();

//$pdf->Image('/images/schet_signature.png', 20, $y_pos-87, '', '', 'PNG', '', '', false, 150, '', false, false, 1, false, false, true);
        $this->pdf->Image($image, 20, $y_pos - 25, '', '', 'PNG', '', '', false, 150, '', false, false, 0, false, false, true);
//        $this->pdf->Image('images/image_with_alpha.png', 50, 50, 100, '', '', 'http://www.tcpdf.org', '', false, 300);

    }

    public function setBottom($bottom)
    {
        $this->pdf->writeHTML($bottom, true, false, true, false, '');
    }

    public function save(): string
    {
        // Close and output PDF document
// This method has several options, check the source code documentation for more information.
        return $this->pdf->Output('schet_' . $this->schet->getDocument()->getSchetNum() . '.pdf', 'S');
    }
}