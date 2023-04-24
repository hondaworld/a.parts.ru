<?php

namespace App\Controller\PrintDocuments;

use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKorRepository;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\SchetFakKorXlsHelper;
use App\Model\Expense\Service\SchetFakPrintService;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Service\GuidGenerator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="")
 */
class PrintSchetFakKorController extends AbstractController
{
    private NalogNdsRepository $nalogNdsRepository;

    public function __construct(
        NalogNdsRepository $nalogNdsRepository
    )
    {
        $this->nalogNdsRepository = $nalogNdsRepository;
    }

    /**
     * @Route("/schet_fak_kor.php", name="schet_fak_kor.php")
     * @param Request $request
     * @param SchetFakKorRepository $schetFakKorRepository
     * @param SchetFakPrintService $schetFakPrintService
     * @return Response
     */
    public function schetFakKor(
        Request               $request,
        SchetFakKorRepository $schetFakKorRepository,
        SchetFakPrintService  $schetFakPrintService
    ): Response
    {
        $schet_fak_forID = $request->query->getInt('id');
        $schetFakKor = $schetFakKorRepository->get($schet_fak_forID);

        $schetFakPrint = $schetFakPrintService->getSchetFak($schetFakKor->getSchetFak());

        $document_num = $schetFakKor->getDocument()->getDocumentNum();
        $document_date = $schetFakKor->getDateofadded();

        return $this->render('app/firms/schetFakKor/print/printSchetFakKor.html.twig', [
                'schetFakKor' => $schetFakKor,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'schetFakPrint' => $schetFakPrint,
            ] + $this->getOrderGoods($schetFakKor));
    }

    /**
     * @Route("/schet_fak_kor_xml.php", name="schet_fak_kor_xml.php")
     * @param Request $request
     * @param SchetFakKorRepository $schetFakKorRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @param GuidGenerator $guidGenerator
     * @return Response
     */
    public function schetFakKorXml(
        Request                     $request,
        SchetFakKorRepository       $schetFakKorRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService,
        GuidGenerator               $guidGenerator
    ): Response
    {
        $schet_fak_forID = $request->query->getInt('id');
        $schetFakKor = $schetFakKorRepository->get($schet_fak_forID);

        $schetFak = $schetFakKor->getSchetFak();
        $expenseDocument = $schetFak->getExpenseDocument();

        $osn = $expenseDocumentPrintService->osn($expenseDocument);

        $document_num = $schetFakKor->getDocument()->getDocumentNum();
        $document_date = $schetFakKor->getDateofadded();

        $filename = "ON_NSCHFDOPPR_" . $expenseDocument->getExpUser()->getEdo() . "_" . $expenseDocument->getExpFirm()->getEdo() . "_" . $schetFak->getDateofadded()->format('Ymd') . "_" . $guidGenerator->generate();

        $xml = $this->renderView('app/firms/schetFakKor/print/printSchetFakKor.xml.twig', [
                'schetFakKor' => $schetFakKor,
                'schetFak' => $schetFak,
                'expenseDocument' => $expenseDocument,
                'nakladnayaOsn' => $osn,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'filename' => $filename
            ] + $this->getOrderGoods($schetFakKor));

        $xml = mb_convert_encoding($xml, "windows-1251", "utf-8");

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * @Route("/schet_fak_kor_excel.php", name="schet_fak_kor_excel.php")
     * @param Request $request
     * @param SchetFakKorRepository $schetFakKorRepository
     * @param SchetFakPrintService $schetFakPrintService
     * @param SchetFakKorXlsHelper $schetFakKorXlsHelper
     * @return Response
     */
    public function schetFakKorExcel(
        Request               $request,
        SchetFakKorRepository $schetFakKorRepository,
        SchetFakPrintService  $schetFakPrintService,
        SchetFakKorXlsHelper  $schetFakKorXlsHelper
    ): Response
    {

        $schet_fak_forID = $request->query->getInt('id');
        $schetFakKor = $schetFakKorRepository->get($schet_fak_forID);

        $schetFakPrint = $schetFakPrintService->getSchetFak($schetFakKor->getSchetFak());

        $document_num = $schetFakKor->getDocument()->getDocumentNum();
        $document_date = $schetFakKor->getDateofadded();

        try {
            $spreadsheet = new Spreadsheet();
            $aSheet = $spreadsheet->getActiveSheet();
            $aSheet->getPageMargins()->setTop(0);
            $aSheet->getPageMargins()->setLeft(0);
            $aSheet->getPageMargins()->setRight(0);
            $aSheet->getPageMargins()->setBottom(0);
            $aSheet
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                ->SetPaperSize(PageSetup::PAPERSIZE_A4)
                ->setScale(65, true);

            $arr = $this->getOrderGoods($schetFakKor);

            $spreadsheet = $schetFakKorXlsHelper->merge($spreadsheet);
            $aSheet = $schetFakKorXlsHelper->header($aSheet, $schetFakKor, $schetFakPrint, $document_num, $document_date);

            $i = 1;
            $j = 15;
            $page = 1;
            $page_num = 1;

            $sum = $arr['sum'];

            if ($arr['nalogNds']->getNds() == 0) $nds_val = "-"; else $nds_val = ($arr['nalogNds']->getNds() * 1) . "%";

            $arStyles = $schetFakKorXlsHelper->getStyles();
            foreach ($arr['goods'] as $good) {

                if ($page == 1) {
                    // Названия колонок
                    $spreadsheet = $schetFakKorXlsHelper->mergeRowNames($spreadsheet, $j);
                    $aSheet = $schetFakKorXlsHelper->rowNames($aSheet, $j, $page_num);
                    $j++;
                    $j++;
                    $aSheet->getRowDimension($j)->setRowHeight(11.25);
                    $spreadsheet = $schetFakKorXlsHelper->mergeRow($spreadsheet, $j);
                    $aSheet = $schetFakKorXlsHelper->rowNamesNumbers($aSheet, $j);
                    //Конец названия колонок
                    $j++;
                }

                $aSheet->getRowDimension($j)->setRowHeight(11.25);
                $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':B' . ($j + 11) . '');

                $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':D' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('E' . $j . ':E' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':I' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':P' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('W' . $j . ':W' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('X' . $j . ':X' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 1) . ':AC' . ($j + 1) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 2) . ':AC' . ($j + 2) . '');


                $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('B' . $j . ':B' . ($j + 11))->applyFromArray($arStyles['tableTextFont']);
                $aSheet->setCellValue("B" . $j . "", $good['detailName'] . " (" . $good['number'] . ")");


                $aSheet->getStyle('C' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('C' . $j . ':D' . ($j + 11))->applyFromArray($arStyles['tableTextFont']);
                $aSheet->setCellValue("C" . $j . "", "А (до изменения)");

                $aSheet->getStyle('E' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('E' . $j . ':E' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("E" . $j . "", $good['okei']);

                $aSheet->getStyle('F' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('F' . $j . ':H' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("F" . $j . "", $good['ed_izm']);

                $aSheet->getStyle('I' . $j . ':I' . ($j + 5))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->getStyle('I' . ($j + 6) . ':I' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("I" . $j . "", $good["quantityBefore"]);

                $aSheet->getStyle('J' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->getStyle('J' . $j . ':K' . ($j + 5))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->getStyle('J' . ($j + 6) . ':K' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("J" . $j . "", $good['priceWithoutNds']);

                $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->getStyle('L' . $j . ':M' . ($j + 11))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->setCellValue("L" . $j . "", $good['sumWithoutNdsBefore']);

                $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('N' . $j . ':O' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("N" . $j . "", "без акциза");


                $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->getStyle('P' . $j . ':P' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("P" . $j . "", $nds_val);

                $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->getStyle('Q' . $j . ':T' . ($j + 11))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->setCellValue("Q" . $j . "", $good['ndsSumBefore']);

                $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->getStyle('U' . $j . ':V' . ($j + 11))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->setCellValue("U" . $j . "", $good['sumBefore']);

                $aSheet->getStyle('W' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('W' . $j . ':W' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("W" . $j . "", $good['country_code']);

                $aSheet->getStyle('X' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('X' . $j . ':X' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("X" . $j . "", $good['country_name']);

                $aSheet->getStyle('Y' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('Y' . $j . ':Y' . ($j + 5))->applyFromArray($arStyles['tableTextFont']);
                $aSheet->getStyle('Y' . ($j + 6) . ':Y' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("Y" . ($j + 1) . "", $good['gtd']);

                $aSheet->getStyle('Z' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('Z' . $j . ':Z' . ($j + 5))->applyFromArray($arStyles['tableTextFont']);
                $aSheet->getStyle('Z' . ($j + 6) . ':Z' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("Z" . ($j + 1) . "", $good['okei']);

                $aSheet->getStyle('AA' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->getStyle('AA' . $j . ':AA' . ($j + 5))->applyFromArray($arStyles['tableTextFont']);
                $aSheet->getStyle('AA' . ($j + 6) . ':AA' . ($j + 11))->applyFromArray($arStyles['tableText1Font']);
                $aSheet->setCellValue("AA" . ($j + 1) . "", $good['ed_izm']);

                $aSheet->getStyle('AB' . $j . ':AC' . ($j + 11))->applyFromArray($arStyles['tablePriceFont']);
                $aSheet->setCellValue("AB" . ($j + 1) . "", $good["quantityBefore"]);

                $j += 3;

                $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':D' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('E' . $j . ':E' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':I' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':P' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('W' . $j . ':W' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('X' . $j . ':X' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 1) . ':AC' . ($j + 1) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 2) . ':AC' . ($j + 2) . '');

                $aSheet->getStyle('C' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("C" . $j . "", "Б (после изменения)");

                $aSheet->getStyle('E' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("E" . $j . "", $good['okei']);

                $aSheet->getStyle('F' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("F" . $j . "", $good['ed_izm']);
                $aSheet->setCellValue("I" . $j . "", $good["quantityAfter"]);

                $aSheet->getStyle('J' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("J" . $j . "", $good['priceWithoutNds']);

                $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("L" . $j . "", $good['sumWithoutNdsAfter']);

                $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("N" . $j . "", "без акциза");

                $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("P" . $j . "", $nds_val);

                $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("Q" . $j . "", $good['ndsSumAfter']);

                $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("U" . $j . "", $good["sumAfter"]);

                $aSheet->getStyle('W' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("W" . $j . "", $good['country_code']);

                $aSheet->getStyle('X' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("X" . $j . "", $good['country_name']);

                $aSheet->getStyle('Y' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("Y" . ($j + 1) . "", $good['gtd']);

                $aSheet->getStyle('Z' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("Z" . ($j + 1) . "", $good['okei']);

                $aSheet->getStyle('AA' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("AA" . ($j + 1) . "", $good['ed_izm']);

                $aSheet->setCellValue("AB" . ($j + 1) . "", $good['quantityAfter']);
//
                $j += 3;

                $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':D' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('E' . $j . ':E' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':I' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':P' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('W' . $j . ':W' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('X' . $j . ':X' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 1) . ':AC' . ($j + 1) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 2) . ':AC' . ($j + 2) . '');

                $aSheet->setCellValue("C" . $j . "", "В (увеличение)");
                $aSheet->setCellValue("E" . $j . "", "X");
                $aSheet->setCellValue("F" . $j . "", "X");
                $aSheet->setCellValue("I" . $j . "", "X");
                $aSheet->setCellValue("J" . $j . "", "X");
                $aSheet->setCellValue("L" . $j . "", "");
                $aSheet->setCellValue("N" . $j . "", "");
                $aSheet->setCellValue("P" . $j . "", "X");
                $aSheet->setCellValue("Q" . $j . "", "");
                $aSheet->setCellValue("U" . $j . "", "");
                $aSheet->setCellValue("W" . $j . "", "X");
                $aSheet->setCellValue("X" . $j . "", "X");
                $aSheet->setCellValue("Y" . $j . "", "X");
                $aSheet->setCellValue("Y" . ($j + 1) . "", "X");
                $aSheet->setCellValue("Y" . ($j + 2) . "", "X");
                $aSheet->setCellValue("Z" . $j . "", "X");
                $aSheet->setCellValue("Z" . ($j + 1) . "", "X");
                $aSheet->setCellValue("Z" . ($j + 2) . "", "X");
                $aSheet->setCellValue("AA" . $j . "", "X");
                $aSheet->setCellValue("AA" . ($j + 1) . "", "X");
                $aSheet->setCellValue("AA" . ($j + 2) . "", "X");

                $j += 3;

                $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':D' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('E' . $j . ':E' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':I' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':P' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('W' . $j . ':W' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('X' . $j . ':X' . ($j + 2) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 1) . ':AC' . ($j + 1) . '');
                $spreadsheet->getActiveSheet()->mergeCells('AB' . ($j + 2) . ':AC' . ($j + 2) . '');

                $aSheet->getStyle('C' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("C" . $j . "", "Г (уменьшение)");

                $aSheet->getStyle('E' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("E" . $j . "", "X");

                $aSheet->getStyle('F' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("F" . $j . "", "X");
                $aSheet->setCellValue("I" . $j . "", "X");

                $aSheet->getStyle('J' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("J" . $j . "", "X");

                $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("L" . $j . "", $good['sumWithoutNdsBefore'] - $good['sumWithoutNdsAfter']);

                $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("N" . $j . "", "-");

                $nds_val = "X";
                $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("P" . $j . "", $nds_val);

                $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("Q" . $j . "", $good['ndsSumBefore'] - $good['ndsSumAfter']);

                $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("U" . $j . "", $good['sumBefore'] - $good['sumAfter']);

                $aSheet->getStyle('W' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("W" . $j . "", "X");

                $aSheet->getStyle('X' . $j . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("X" . $j . "", "X");

                $aSheet->getStyle('Y' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("Y" . $j . "", "X");
                $aSheet->setCellValue("Y" . ($j + 1) . "", "X");
                $aSheet->setCellValue("Y" . ($j + 2) . "", "X");

                $aSheet->getStyle('Z' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("Z" . $j . "", "X");
                $aSheet->setCellValue("Z" . ($j + 1) . "", "X");
                $aSheet->setCellValue("Z" . ($j + 2) . "", "X");

                $aSheet->getStyle('AA' . ($j + 1) . '')->getAlignment()->setWrapText(true);
                $aSheet->setCellValue("AA" . $j . "", "X");
                $aSheet->setCellValue("AA" . ($j + 1) . "", "X");
                $aSheet->setCellValue("AA" . ($j + 2) . "", "X");

                $i++;
                $j += 3;
                $page++;
            }


            $aSheet->getRowDimension($j)->setRowHeight(11.75);

            $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':K' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');

            $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('B' . $j . ':K' . ($j + 1))->applyFromArray($arStyles['tableAllFont']);
            $aSheet->setCellValue("B" . $j . "", "Всего увеличение (сумма строк В)");


            $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('L' . $j . ':M' . ($j + 1))->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("L" . $j . "", "");

            $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('N' . $j . ':O' . ($j + 1))->applyFromArray($arStyles['tableText1Font']);
            $aSheet->setCellValue("N" . $j . "", "X");

            $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('P' . $j . ':P' . ($j + 1))->applyFromArray($arStyles['tableText1Font']);
            $aSheet->setCellValue("P" . $j . "", "X");

            $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('Q' . $j . ':T' . ($j + 1))->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("Q" . $j . "", "");

            $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('U' . $j . ':V' . ($j + 1))->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("U" . $j . "", "");

            $aSheet->getStyle('W' . $j . ':AA' . ($j + 1))->applyFromArray($arStyles['tableText1Font']);
            $aSheet->setCellValue("W" . $j . "", "X");
            $aSheet->setCellValue("X" . $j . "", "X");
            $aSheet->setCellValue("Y" . $j . "", "X");
            $aSheet->setCellValue("Z" . $j . "", "X");
            $aSheet->setCellValue("AA" . $j . "", "X");
            $aSheet->getStyle('AB' . $j . ':AC' . ($j + 1))->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("AB" . $j . "", "");

            $j++;

            $aSheet->getRowDimension($j)->setRowHeight(11.75);

            $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':K' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AC' . $j . '');

            $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->setCellValue("B" . $j . "", "Всего уменьшение (сумма строк Г)");


            $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("L" . $j . "", $sum['sumWithoutNdsBefore'] - $sum['sumWithoutNdsAfter']);

            $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->setCellValue("N" . $j . "", "X");

            $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("P" . $j . "", "X");

            $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("Q" . $j . "", $sum['ndsSumBefore'] - $sum['ndsSumAfter']);

            $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("U" . $j . "", $sum['sumBefore'] - $sum['sumAfter']);
            $aSheet->setCellValue("W" . $j . "", "X");
            $aSheet->setCellValue("X" . $j . "", "X");
            $aSheet->setCellValue("Y" . $j . "", "X");
            $aSheet->setCellValue("Z" . $j . "", "X");
            $aSheet->setCellValue("AA" . $j . "", "X");
            $aSheet->setCellValue("AB" . $j . "", $sum['quantity']);


            $j++;
            $j++;

            $aSheet->getRowDimension($j)->setRowHeight(23.25);

            $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':T' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':W' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':AC' . $j . '');


            $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['podpFont']);
            $aSheet->setCellValue("B" . $j . "", "Руководитель организации\nили иное уполномоченное лицо");

            $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("C" . $j . "", "");

            $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("H" . $j . "", "");

            $aSheet->getStyle('P' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('P' . $j . ':T' . $j)->applyFromArray($arStyles['podpFont']);
            $aSheet->setCellValue("P" . $j . "", "Главный бухгалтер\nили иное уполномоченное лицо");

            $aSheet->getStyle('U' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('U' . $j . ':W' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("U" . $j . "", "");

            $aSheet->getStyle('Y' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('Y' . $j . ':AC' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("Y" . $j . "", "");


            $j++;
            $aSheet->getRowDimension($j)->setRowHeight(11.25);

            $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':T' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':W' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':AC' . $j . '');


            $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('C' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('D' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('E' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("C" . $j . "", "(подпись)");

            $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('H' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('J' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("H" . $j . "", "(ф.и.о.)");

            $aSheet->getStyle('U' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('V' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('W' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("U" . $j . "", "(подпись)");

            $aSheet->getStyle('Y' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('AA' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('AB' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->getStyle('AC' . $j . '')->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("Y" . $j . "", "(ф.и.о.)");

            $j++;
            $j++;

            $aSheet->getRowDimension($j)->setRowHeight(23.25);

            $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':AC' . $j . '');


            $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['podpFont']);
            $aSheet->setCellValue("B" . $j . "", "Индивидуальный предприниматель");

            $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("C" . $j . "", "");

            $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("H" . $j . "", "");

            $aSheet->getStyle('P' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('P' . $j . ':AC' . $j)->applyFromArray($arStyles['podp1Font']);
            $aSheet->setCellValue("P" . $j . "", "");


            $j++;
            $aSheet->getRowDimension($j)->setRowHeight(11.25);

            $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
            $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':AC' . $j . '');


            $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("C" . $j . "", "(подпись)");

            $aSheet->getStyle('M' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("H" . $j . "", "(ф.и.о.)");

            $aSheet->getStyle('P' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('P' . $j . ':AC' . $j)->applyFromArray($arStyles['podp2Font']);
            $aSheet->setCellValue("P" . $j . "", "(реквизиты свидетельства)");


            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="schet_fak.xls"');
            header('Cache-Control: max-age=0');

            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
        }
        return $this->json([]);
    }

    private function getOrderGoods(SchetFakKor $schetFakKor): array
    {


        $nalogNds = $this->nalogNdsRepository->getLastByFirm($schetFakKor->getFirm(), $schetFakKor->getDateofadded());


        $sum = [
            'priceWithoutNds' => 0,
            'sumWithoutNdsBefore' => 0,
            'sumWithoutNdsAfter' => 0,
            'ndsSumBefore' => 0,
            'ndsSumAfter' => 0,
            'sumBefore' => 0,
            'sumAfter' => 0,
            'quantity' => 0,
        ];

        $goods = [];
        $incomeDocuments = $schetFakKor->getIncomeDocuments();
        foreach ($incomeDocuments as $incomeDocument) {
            foreach ($incomeDocument->getIncomes() as $income) {
                $good = [
                    'number' => $income->getZapCard()->getNumber()->getValue(),
                    'detailName' => $income->getZapCard()->getDetailName(),
                    'quantity' => $income->getQuantity(),
                    'price' => $income->getPrice(),
                    'quantityBefore' => 0,
                    'quantityAfter' => 0

                ];

                $good['ndsPrice'] = $income->getPrice() / (100 + $nalogNds->getNds()) * $nalogNds->getNds();
                $good['priceWithoutNds'] = $good['price'] - $good['ndsPrice'];

                $good['country_name'] = "Япония";
                $good['country_code'] = "392";

                $good['ed_izm'] = $income->getZapCard()->getEdIzm()->getNameShort();
                $good['okei'] = $income->getZapCard()->getEdIzm()->getOkei();
                if ($income->getZapCard()->getManager()) {
                    $good['country_name'] = $income->getZapCard()->getManager()->getName();
                    $good['country_code'] = $income->getZapCard()->getManager()->getCode();
                }


                if ($income->getShopGtd()) {
                    $good['gtd'] = $income->getShopGtd()->getName()->getValue();
                } elseif ($income->getShopGtd1()) {
                    $good['gtd'] = $income->getShopGtd1()->getName()->getValue();
                } else {
                    $good['gtd'] = '';
                }

                foreach ($schetFakKor->getSchetFaks() as $schetFak) {
                    foreach ($schetFak->getExpenseDocument()->getOrderGoods() as $orderGood) {
                        if (
                            $orderGood->getNumber()->isEqual($income->getZapCard()->getNumber()) &&
                            $orderGood->getCreater()->getId() == $income->getZapCard()->getCreater()->getId() &&
                            $income->getPrice() == $orderGood->getDiscountPrice()
                        ) {
                            $good['quantityBefore'] += $orderGood->getQuantity();
                        }
                    }
                }
                $good['quantityAfter'] = $good['quantityBefore'] - $good['quantity'];

                $good['sumBefore'] = $good['price'] * $good['quantityBefore'];
                $good['ndsSumBefore'] = $good['ndsPrice'] * $good['quantityBefore'];
                $good['sumWithoutNdsBefore'] = $good['sumBefore'] - $good['ndsSumBefore'];

                $good['sumAfter'] = $good['price'] * $good['quantityAfter'];
                $good['ndsSumAfter'] = $good['ndsPrice'] * $good['quantityAfter'];
                $good['sumWithoutNdsAfter'] = $good['sumAfter'] - $good['ndsSumAfter'];
                $goods[] = $good;

                $sum['sumBefore'] += $good['sumBefore'];
                $sum['ndsSumBefore'] += $good['ndsSumBefore'];
                $sum['sumWithoutNdsBefore'] += $good['sumWithoutNdsBefore'];

                $sum['sumAfter'] += $good['sumAfter'];
                $sum['ndsSumAfter'] += $good['ndsSumAfter'];
                $sum['sumWithoutNdsAfter'] += $good['sumWithoutNdsAfter'];

                $sum['priceWithoutNds'] += $good['priceWithoutNds'];
                $sum['quantity'] += $good['quantity'];
            }
        }

        $sum = [
            'priceWithoutNds' => round($sum['priceWithoutNds'], 2),
            'sumWithoutNdsBefore' => round($sum['sumWithoutNdsBefore'], 2),
            'sumWithoutNdsAfter' => round($sum['sumWithoutNdsAfter'], 2),
            'ndsSumBefore' => round($sum['ndsSumBefore'], 2),
            'ndsSumAfter' => round($sum['ndsSumAfter'], 2),
            'sumBefore' => round($sum['sumBefore'], 2),
            'sumAfter' => round($sum['sumAfter'], 2),
            'quantity' => round($sum['quantity'], 2),
        ];

        usort($goods, function ($a, $b) {
            return strcasecmp($a['number'], $b['number']);
        });

        return compact('nalogNds', 'goods', 'sum');

    }

}
