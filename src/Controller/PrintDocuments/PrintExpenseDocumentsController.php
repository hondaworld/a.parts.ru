<?php

namespace App\Controller\PrintDocuments;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\ExpenseDocumentXlsHelper;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Shop\Entity\Discount\DiscountRepository;
use App\Model\Order\UseCase\ExpenseDocument\Test;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\OrderGoodFetcher;
use App\Service\Converter\NumberInWordsConverter;
use App\Service\GuidGenerator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", name="")
 */
class PrintExpenseDocumentsController extends AbstractController
{
    private NalogNdsRepository $nalogNdsRepository;
    private OrderGoodFetcher $orderGoodFetcher;
    private ZapCardRepository $zapCardRepository;

    public function __construct(
        NalogNdsRepository $nalogNdsRepository,
        OrderGoodFetcher   $orderGoodFetcher,
        ZapCardRepository  $zapCardRepository
    )
    {
        $this->nalogNdsRepository = $nalogNdsRepository;
        $this->orderGoodFetcher = $orderGoodFetcher;
        $this->zapCardRepository = $zapCardRepository;
    }

    /**
     * @Route("/chek.php", name="chek.php")
     * @param Request $request
     * @param DiscountRepository $discountRepository
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function chek(
        Request                     $request,
        DiscountRepository          $discountRepository,
        ExpenseDocumentRepository   $expenseDocumentRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService
    ): Response
    {
        $expenseDocumentID = $request->query->getInt('id');
        $expenseDocument = $expenseDocumentRepository->get($expenseDocumentID);

        $expenseDocumentPrint = $expenseDocumentPrintService->getCheck($expenseDocument);

        $document_num = $expenseDocument->getDocument()->getDocumentNum();
        $document_date = $expenseDocument->getDateofadded();

        $user = $expenseDocument->getUser();
        if ($user->isRetail()) {
            $sum = $this->orderGoodFetcher->getSumByRetailUser($user->getId());

            $nextDiscountSum = null;
            $nextDiscountPercent = null;

            $discounts = $discountRepository->findBy([], ['summ' => 'asc']);
            foreach ($discounts as $discount) {
                if ($sum < $discount->getSumm()) {
                    $nextDiscountSum = $discount->getSumm() - $sum;
                    $nextDiscountPercent = $discount->getDiscountSpare();
                }
            }
        }

        return $this->render('app/orders/expenseDocument/printChek.html.twig', [
                'expenseDocument' => $expenseDocument,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'expenseDocumentPrint' => $expenseDocumentPrint,
                'nextDiscountSum' => $nextDiscountSum ?? null,
                'nextDiscountPercent' => $nextDiscountPercent ?? null,
            ] + $this->getOrderGoods($expenseDocument));
    }

    /**
     * @Route("/nakladnaya.php", name="nakladnaya.php")
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @return Response
     */
    public function nakladnaya(
        Request                     $request,
        ExpenseDocumentRepository   $expenseDocumentRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService
    ): Response
    {
        $expenseDocumentID = $request->query->getInt('id');
        $expenseDocument = $expenseDocumentRepository->get($expenseDocumentID);

        $expenseDocumentPrint = $expenseDocumentPrintService->getNakladnaya($expenseDocument);

        $document_num = $expenseDocument->getDocument()->getDocumentNum();
        $document_date = $expenseDocument->getDateofadded();

        return $this->render('app/orders/expenseDocument/printNakladnaya.html.twig', [
                'expenseDocument' => $expenseDocument,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'expenseDocumentPrint' => $expenseDocumentPrint
            ] + $this->getOrderGoods($expenseDocument));
    }

    /**
     * @Route("/{id}/nakladnaya_test.php", name="nakladnaya_test.php")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @return Response
     */
    public function nakladnayaTest(
        User                        $user,
        Request                     $request,
        ExpenseDocumentRepository   $expenseDocumentRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService
    ): Response
    {
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $command = new Test\Command();
        $form = $this->createForm(Test\Form::class, $command);
        $form->handleRequest($request);

        $expenseDocumentPrint = $expenseDocumentPrintService->getNakladnaya($expenseDocument, true);

        return $this->render('app/orders/expenseDocument/printNakladnaya.html.twig', [
                'expenseDocument' => $expenseDocument,
                'document_num' => $command->document_num,
                'document_date' => $command->document_date,
                'expenseDocumentPrint' => $expenseDocumentPrint
            ] + $this->getOrderGoods($expenseDocument, true));
    }

    /**
     * @Route("/nakladnaya_xml.php", name="nakladnaya_xml.php")
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @param GuidGenerator $guidGenerator
     * @return Response
     */
    public function nakladnayaXml(
        Request                     $request,
        ExpenseDocumentRepository   $expenseDocumentRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService,
        GuidGenerator               $guidGenerator
    ): Response
    {
        $expenseDocumentID = $request->query->getInt('id');
        $expenseDocument = $expenseDocumentRepository->get($expenseDocumentID);

        $osn = $expenseDocumentPrintService->osn($expenseDocument);

        $document_num = $expenseDocument->getDocument()->getDocumentNum();
        $document_date = $expenseDocument->getDateofadded();

        $filename = "DP_TOVTORGPR_" . $expenseDocument->getExpUser()->getEdo() . "_" . $expenseDocument->getExpFirm()->getEdo() . "_" . $expenseDocument->getDateofadded()->format('Ymd') . "_" . $guidGenerator->generate();

        $xml = $this->renderView('app/orders/expenseDocument/printNakladnaya.xml.twig', [
                'expenseDocument' => $expenseDocument,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'nakladnayaOsn' => $osn,
//                'expenseDocumentPrint' => $expenseDocumentPrint,
                'filename' => $filename
            ] + $this->getOrderGoods($expenseDocument));

        $xml = mb_convert_encoding($xml, "windows-1251", "utf-8");

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * @Route("/nakladnaya_excel.php", name="nakladnaya_excel.php")
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @param ExpenseDocumentXlsHelper $expenseDocumentXlsHelper
     * @param NumberInWordsConverter $numberInWordsConverter
     * @return Response
     */
    public function nakladnayaExcel(
        Request                     $request,
        ExpenseDocumentRepository   $expenseDocumentRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService,
        ExpenseDocumentXlsHelper    $expenseDocumentXlsHelper,
        NumberInWordsConverter      $numberInWordsConverter
    ): Response
    {
        $expenseDocumentID = $request->query->getInt('id');
        $expenseDocument = $expenseDocumentRepository->get($expenseDocumentID);

        $expenseDocumentPrint = $expenseDocumentPrintService->getNakladnaya($expenseDocument);

        $document_num = $expenseDocument->getDocument()->getDocumentNum();
        $document_date = $expenseDocument->getDateofadded();

        try {
            $writer = $this->excel($expenseDocumentXlsHelper, $numberInWordsConverter, $expenseDocument, $expenseDocumentPrint);

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="nakladnaya.xls"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
        }
        return $this->json([]);
    }

    /**
     * @param ExpenseDocumentXlsHelper $expenseDocumentXlsHelper
     * @param NumberInWordsConverter $numberInWordsConverter
     * @param ExpenseDocument $expenseDocument
     * @param ExpenseDocumentPrint $expenseDocumentPrint
     * @return \PhpOffice\PhpSpreadsheet\Writer\IWriter
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function excel(ExpenseDocumentXlsHelper $expenseDocumentXlsHelper, NumberInWordsConverter $numberInWordsConverter, ExpenseDocument $expenseDocument, ExpenseDocumentPrint $expenseDocumentPrint): IWriter
    {
        $document_num = $expenseDocument->getDocument()->getDocumentNum();
        $document_date = $expenseDocument->getDateofadded();

        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

        $spreadsheet = $expenseDocumentXlsHelper->merge($spreadsheet);
        $aSheet = $expenseDocumentXlsHelper->header($aSheet, $expenseDocumentPrint, $document_num, $document_date);

        $arr = $this->getOrderGoods($expenseDocument);
        $i = 1;
        $j = 19;
        $page = 1;
        $page_num = 1;

        $sumPage = [
            'priceWithoutNds' => 0,
            'sumWithoutNds' => 0,
            'ndsSum' => 0,
            'sum' => 0,
            'quantity' => 0,
        ];

        $arStyles = $expenseDocumentXlsHelper->getStyles();
        foreach ($arr['orderGoods'] as $orderGood) {

            if (($page == 12) && ($i == 12) || ($page == 23)) {
                // Итоги по странице
                $spreadsheet = $expenseDocumentXlsHelper->mergeSumPage($spreadsheet, $j);
                $aSheet = $expenseDocumentXlsHelper->rowSumPage($aSheet, $sumPage, $j);

                $sumPage = [
                    'priceWithoutNds' => 0,
                    'sumWithoutNds' => 0,
                    'ndsSum' => 0,
                    'sum' => 0,
                    'quantity' => 0,
                ];

                $j++;
                $page = 1;
                $page_num++;

                $spreadsheet->getActiveSheet()->setBreak('A' . ($j - 1), Worksheet::BREAK_ROW);
            }

            if ($page == 1) {
                // Названия колонок
                $spreadsheet = $expenseDocumentXlsHelper->mergeRowNames($spreadsheet, $j + 1);
                $aSheet = $expenseDocumentXlsHelper->rowNames($aSheet, $j, $page_num);
                $j++;
                $j++;
                $j++;
                $aSheet->getRowDimension($j)->setRowHeight(11.25);
                $spreadsheet = $expenseDocumentXlsHelper->mergeRow($spreadsheet, $j);
                $aSheet = $expenseDocumentXlsHelper->rowNamesNumbers($aSheet, $j);
                //Конец названия колонок
                $j++;
            }

            $spreadsheet = $expenseDocumentXlsHelper->mergeRow($spreadsheet, $j);

            $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left'])->applyFromArray($arStyles['border']);
            $aSheet->setCellValue("B" . $j . "", $i);

            $arStyles['border_now'] = $arStyles['border'];
            if ($i == 1) $arStyles['border_now'] = $arStyles['border_top'];
            $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('C' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_left']);
            $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("C" . $j . "", $orderGood['detail_name']);

            $aSheet->getStyle('G' . $j . ':AO' . $j)->applyFromArray($arStyles['textFont']);

            $arStyles['border_now'] = $arStyles['border_left_right'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top_left_right'];
            else if (count($arr['orderGoods']) == 1) $arStyles['border_now'] = $arStyles['border_medium'];
            else if (($page == 11) && ($i == 11) || ($page == 22)) $arStyles['border_now'] = $arStyles['border_left_right_bottom'];
            else if ($i == count($arr['orderGoods'])) $arStyles['border_now'] = $arStyles['border_left_right_bottom'];
            $aSheet->getStyle('G' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('G' . $j . '')->applyFromArray($arStyles['align_left']);
            $aSheet->getStyle('G' . $j . ':I' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("G" . $j . "", " " . $orderGood['number']);

            $arStyles['border_now'] = $arStyles['border_left_right'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top_left_right'];
            $aSheet->getStyle('J' . $j . '')->applyFromArray($arStyles['align_center']);
            $aSheet->getStyle('J' . $j . ':L' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("J" . $j . "", $orderGood['ed_izm']);

            $arStyles['border_now'] = $arStyles['border'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top'];
            else if (($page == 11) && ($i == 11) || ($page == 22)) $arStyles['border_now'] = $arStyles['border_bottom'];
            else if ($i == count($arr['orderGoods'])) $arStyles['border_now'] = $arStyles['border_bottom'];

            $aSheet->getStyle('M' . $j . ':AF' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->getStyle('M' . $j . ':Y' . $j)->applyFromArray($arStyles['align_center']);

            $aSheet->setCellValue("M" . $j . "", $orderGood['okei']);
            $aSheet->setCellValue("O" . $j . "", "");
            $aSheet->setCellValue("P" . $j . "", "");
            $aSheet->setCellValue("R" . $j . "", "");
            $aSheet->setCellValue("V" . $j . "", "");
            $aSheet->setCellValue("Y" . $j . "", $orderGood['quantity'] . ",000");

            $aSheet->getStyle('AA' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("AA" . $j . "", $orderGood['priceWithoutNds']);

            $aSheet->getStyle('AD' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("AD" . $j . "", $orderGood['sumWithoutNds']);

            $arStyles['border_now'] = $arStyles['border_left_right'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top_left_right'];
            if ($arr['nalogNds']->getNds() == 0) $nds_val = "-"; else $nds_val = ($arr['nalogNds']->getNds() * 1) . "%";
            $aSheet->getStyle('AG' . $j . '')->applyFromArray($arStyles['align_center']);
            $aSheet->getStyle('AG' . $j . ':AJ' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("AG" . $j . "", $nds_val);

            $arStyles['border_now'] = $arStyles['border'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top'];
            else if (($page == 11) && ($i == 11) || ($page == 22)) $arStyles['border_now'] = $arStyles['border_bottom'];
            else if ($i == count($arr['orderGoods'])) $arStyles['border_now'] = $arStyles['border_bottom'];
            $aSheet->getStyle('AK' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('AK' . $j . ':AM' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("AK" . $j . "", $orderGood['ndsSum']);

            $arStyles['border_now'] = $arStyles['border_right'];
            if ($page == 1) $arStyles['border_now'] = $arStyles['border_top_right'];
            else if (($page == 11) && ($i == 11) || ($page == 22)) $arStyles['border_now'] = $arStyles['border_right_bottom'];
            else if ($i == count($arr['orderGoods'])) $arStyles['border_now'] = $arStyles['border_right_bottom'];
            $aSheet->getStyle('AN' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('AN' . $j . ':AO' . $j)->applyFromArray($arStyles['border_now']);
            $aSheet->setCellValue("AN" . $j . "", $orderGood['sum']);


            $sumPage['priceWithoutNds'] += $orderGood['priceWithoutNds'];
            $sumPage['sumWithoutNds'] += $orderGood['sumWithoutNds'];
            $sumPage['ndsSum'] += $orderGood['ndsSum'];
            $sumPage['sum'] += $orderGood['discountPrice'] * $orderGood['quantity'];
            $sumPage['quantity'] += $orderGood['quantity'];

            $i++;
            $j++;
            $page++;
        }

        $spreadsheet = $expenseDocumentXlsHelper->mergeRowSum($spreadsheet, $j);
        $aSheet = $expenseDocumentXlsHelper->lastRowSumPage($aSheet, $sumPage, $j);
        $j++;

        $spreadsheet = $expenseDocumentXlsHelper->mergeRowSum($spreadsheet, $j);
        $aSheet = $expenseDocumentXlsHelper->lastRowSum($aSheet, $arr['sum'], $j);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(11);
        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':Y' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont']);
        $aSheet->setCellValue("F" . $j . "", "Товарная накладная имеет приложение на $page_num листах");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(11);
        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':G' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont']);
        $aSheet->setCellValue("F" . $j . "", "и содержит");

        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':Y' . $j . '');
        $aSheet->getStyle('H' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('H' . $j . ':Y' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("H" . $j . "", $numberInWordsConverter->getWords($i - 1));

        $spreadsheet->getActiveSheet()->mergeCells('Z' . $j . ':AF' . $j . '');
        $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left']);
        $aSheet->setCellValue("Z" . $j . "", "порядковых номеров записей");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(8);
        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':Y' . $j . '');
        $aSheet->getStyle('H' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("H" . $j . "", "прописью");

        $aSheet->getStyle('AJ' . $j . ':AO' . ($j + 3))->applyFromArray($arStyles['border_medium']);
        $spreadsheet->getActiveSheet()->mergeCells('AJ' . $j . ':AO' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AJ' . ($j + 2) . ':AO' . ($j + 3) . '');

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9);
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':P' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("L" . $j . "", "Масса груза (нетто)");

        $spreadsheet->getActiveSheet()->mergeCells('T' . $j . ':AG' . $j . '');
        $aSheet->getStyle('T' . $j . ':AG' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(8);
        $spreadsheet->getActiveSheet()->mergeCells('T' . $j . ':AG' . $j . '');
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("T" . $j . "", "прописью");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9);

        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':C' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left']);
        $aSheet->setCellValue("B" . $j . "", "Всего мест");

        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('F' . $j . ':H' . $j)->applyFromArray($arStyles['border_bottom1']);

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':P' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("L" . $j . "", "Масса груза (брутто)");

        $spreadsheet->getActiveSheet()->mergeCells('T' . $j . ':AG' . $j . '');
        $aSheet->getStyle('T' . $j . ':AG' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(8);
        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("F" . $j . "", "прописью");
        $spreadsheet->getActiveSheet()->mergeCells('T' . $j . ':AG' . $j . '');
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("T" . $j . "", "прописью");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(22.5);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':G' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", "Приложение (паспорта, сертификаты и т.п.) на");

        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('J' . $j . '')->applyFromArray($arStyles['border_bottom1']);

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("L" . $j . "", "листах");

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':Y' . $j . '');
        $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("U" . $j . "", "По доверенности №");
        $aSheet->getStyle('Z' . $j . ':AE' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('AE' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AE" . $j . "", "от");
        $aSheet->getStyle('AF' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(10.5);
        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':J' . $j . '');
        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("I" . $j . "", "прописью");
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(10.25);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':S' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['boldFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", "Всего отпущено " . $numberInWordsConverter->getWords($i - 1) . " наименований на сумму");
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':W' . $j . '');
        $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("U" . $j . "", "выданной");
        $aSheet->getStyle('Z' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9.5);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':S' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['boldFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", trim($numberInWordsConverter->getWords($arr['sum']['sum'], true)) . " " . $numberInWordsConverter->getKop($arr['sum']['sum']));
        $aSheet->getStyle('B' . $j . ':S' . $j)->applyFromArray($arStyles['border_bottom1']);

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('Z' . $j . ':AO' . $j . '');
        $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("Z" . $j . "", "кем, кому (организация, должность, фамилия, и. о.)");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9.5);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':S' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("B" . $j . "", "прописью");
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);
        $aSheet->getStyle('Z' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(20.75);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':C' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", "Отпуск разрешил");

        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':J' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->getStyle('F' . $j . ':J' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("F" . $j . "", "Ген. директор");

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->getStyle('L' . $j . ':R' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("L" . $j . "", $expenseDocumentPrint->getDirector());

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);
        $aSheet->getStyle('Z' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9.5);

        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("F" . $j . "", "должность");


        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':J' . $j . '');
        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("I" . $j . "", "подпись");

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("L" . $j . "", "расшифровка подписи");

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);


        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(20.75);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':H' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", "Главный (старший) бухгалтер");

        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':J' . $j . '');
        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('J' . $j . '')->applyFromArray($arStyles['border_bottom1']);

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->getStyle('L' . $j . ':R' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("L" . $j . "", $expenseDocumentPrint->getBuhgalter());

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':Y' . $j . '');
        $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("U" . $j . "", "Груз принял");

        $aSheet->getStyle('Z' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);


        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9.5);

        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':J' . $j . '');
        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("I" . $j . "", "подпись");

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("L" . $j . "", "расшифровка подписи");

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('Z' . $j . ':AA' . $j . '');
        $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("Z" . $j . "", "должность");

        $spreadsheet->getActiveSheet()->mergeCells('AC' . $j . ':AG' . $j . '');
        $aSheet->getStyle('AC' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("AC" . $j . "", "подпись");

        $spreadsheet->getActiveSheet()->mergeCells('AJ' . $j . ':AO' . $j . '');
        $aSheet->getStyle('AJ' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("AJ" . $j . "", "расшифровка подписи");


        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(20.75);
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':C' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("B" . $j . "", "Отпуск груза произвел");

        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':J' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->getStyle('F' . $j . ':J' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("F" . $j . "", "");

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->getStyle('L' . $j . ':R' . $j)->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("L" . $j . "", $expenseDocumentPrint->getDirector());

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':Y' . $j . '');
        $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("U" . $j . "", "Груз получил");

        $aSheet->getStyle('Z' . $j . ':AO' . $j)->applyFromArray($arStyles['border_bottom1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(9.5);

        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . $j . '');
        $aSheet->getStyle('F' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("F" . $j . "", "должность");


        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':J' . $j . '');
        $aSheet->getStyle('I' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("I" . $j . "", "подпись");

        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':R' . $j . '');
        $aSheet->getStyle('L' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("L" . $j . "", "расшифровка подписи");

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':Y' . $j . '');
        $aSheet->getStyle('U' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("U" . $j . "", "грузополучатель");

        $spreadsheet->getActiveSheet()->mergeCells('Z' . $j . ':AA' . $j . '');
        $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("Z" . $j . "", "должность");

        $spreadsheet->getActiveSheet()->mergeCells('AC' . $j . ':AG' . $j . '');
        $aSheet->getStyle('AC' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("AC" . $j . "", "подпись");

        $spreadsheet->getActiveSheet()->mergeCells('AJ' . $j . ':AO' . $j . '');
        $aSheet->getStyle('AJ' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("AJ" . $j . "", "расшифровка подписи");

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(5);
        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(11.25);

        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':E' . $j . '');
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("B" . $j . "", "М.П.");

        $aSheet->getStyle('H' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("H" . $j . "", '"    "');

        $aSheet->getStyle('I' . $j . ':J' . $j)->applyFromArray($arStyles['border_bottom1']);

        $spreadsheet->getActiveSheet()->mergeCells('K' . $j . ':O' . $j . '');
        $aSheet->getStyle('K' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("K" . $j . "", "20      года");

        $aSheet->getStyle('T' . $j . '')->applyFromArray($arStyles['border_left1']);

        $spreadsheet->getActiveSheet()->mergeCells('X' . $j . ':AA' . $j . '');
        $aSheet->getStyle('X' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("X" . $j . "", "М.П.");

        $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AD' . $j . '');
        $aSheet->getStyle('AB' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AB" . $j . "", '"    "');

        $aSheet->getStyle('AE' . $j . ':AI' . $j)->applyFromArray($arStyles['border_bottom1']);

        $spreadsheet->getActiveSheet()->mergeCells('AJ' . $j . ':AK' . $j . '');
        $aSheet->getStyle('AJ' . $j . '')->applyFromArray($arStyles['textMediumFont'])->applyFromArray($arStyles['align_left_bottom']);
        $aSheet->setCellValue("AJ" . $j . "", "20      года");


        $writer = IOFactory::createWriter($spreadsheet, 'Xls');

        return $writer;
    }

    private function getOrderGoods(ExpenseDocument $expenseDocument, bool $isTest = false): array
    {
        $nalogNds = $this->nalogNdsRepository->getLastByFirm($expenseDocument->getExpFirm(), $expenseDocument->getDateofadded());

        if ($isTest) {
            $orderGoods = $this->orderGoodFetcher->allExpenses($expenseDocument->getUser()->getId());
        } else {
            $orderGoods = $this->orderGoodFetcher->findByExpenseDocument($expenseDocument->getId());
        }

        $sum = [
            'priceWithoutNds' => 0,
            'sumWithoutNds' => 0,
            'ndsSum' => 0,
            'sum' => 0,
            'sumWithoutDiscount' => 0,
            'quantity' => 0,
            'isDiscount' => false,
        ];

        foreach ($orderGoods as &$item) {
            $item['zapCard'] = $this->zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
            if ($item['zapCard']) {
                $item['ed_izm'] = $item['zapCard']->getEdIzm()->getNameShort();
                $item['okei'] = $item['zapCard']->getEdIzm()->getOkei();
                $item['detail_name'] = $item['zapCard']->getDetailName();
            } else {
                $item['ed_izm'] = "шт";
                $item['okei'] = "796";
                $item['detail_name'] = '';
            }

            $item['discountPrice'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
            $item['ndsPrice'] = $item['discountPrice'] / (100 + $nalogNds->getNds()) * $nalogNds->getNds();
            $item['priceWithoutNds'] = $item['discountPrice'] - $item['ndsPrice'];
            $item['sum'] = $item['discountPrice'] * $item['quantity'];
            $item['sumWithoutDiscount'] = $item['price'] * $item['quantity'];
            $item['ndsSum'] = $item['ndsPrice'] * $item['quantity'];
            $item['sumWithoutNds'] = $item['sum'] - $item['ndsSum'];

            $sum['priceWithoutNds'] += $item['priceWithoutNds'];
            $sum['sumWithoutNds'] += $item['sumWithoutNds'];
            $sum['ndsSum'] += $item['ndsSum'];
            $sum['sum'] += $item['discountPrice'] * $item['quantity'];
            $sum['sumWithoutDiscount'] += $item['price'] * $item['quantity'];
            $sum['quantity'] += $item['quantity'];

            if ($item['discount'] > 0) $sum['isDiscount'] = true;
        }

        $sum = [
            'priceWithoutNds' => round($sum['priceWithoutNds'], 2),
            'sumWithoutNds' => round($sum['sumWithoutNds'], 2),
            'ndsSum' => round($sum['ndsSum'], 2),
            'sum' => round($sum['sum'], 2),
            'sumWithoutDiscount' => round($sum['sumWithoutDiscount'], 2),
            'quantity' => round($sum['quantity'], 2),
            'isDiscount' => $sum['isDiscount']
        ];

        return compact('nalogNds', 'orderGoods', 'sum');
    }

}
