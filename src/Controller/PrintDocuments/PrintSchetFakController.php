<?php

namespace App\Controller\PrintDocuments;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Expense\Entity\SchetFak\SchetFakRepository;
use App\Model\Expense\Entity\SchetFakPrint\SchetFakPrint;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\SchetFakXlsHelper;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Shop\UseCase\ShopGtd\Create;
use App\Model\Expense\Service\SchetFakPrintService;
use App\Model\Finance\Entity\NalogNds\NalogNdsRepository;
use App\Model\Shop\Entity\Gtd\ShopGtdRepository;
use App\ReadModel\Order\OrderGoodFetcher;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("", name="")
 */
class PrintSchetFakController extends AbstractController
{
    private NalogNdsRepository $nalogNdsRepository;
    private OrderGoodFetcher $orderGoodFetcher;
    private ZapCardRepository $zapCardRepository;
    private ShopGtdRepository $shopGtdRepository;
    private ValidatorInterface $validator;
    private IncomeRepository $incomeRepository;

    public function __construct(
        NalogNdsRepository $nalogNdsRepository,
        OrderGoodFetcher   $orderGoodFetcher,
        ZapCardRepository  $zapCardRepository,
        ShopGtdRepository  $shopGtdRepository,
        ValidatorInterface $validator,
        IncomeRepository   $incomeRepository
    )
    {
        $this->nalogNdsRepository = $nalogNdsRepository;
        $this->orderGoodFetcher = $orderGoodFetcher;
        $this->zapCardRepository = $zapCardRepository;
        $this->shopGtdRepository = $shopGtdRepository;
        $this->validator = $validator;
        $this->incomeRepository = $incomeRepository;
    }

    /**
     * @Route("/schet_fak.php", name="schet_fak.php")
     * @param Request $request
     * @param SchetFakRepository $schetFakRepository
     * @param SchetFakPrintService $schetFakPrintService
     * @return Response
     */
    public function schetFak(
        Request              $request,
        SchetFakRepository   $schetFakRepository,
        SchetFakPrintService $schetFakPrintService
    ): Response
    {
        $schet_fakID = $request->query->getInt('id');
        $schetFak = $schetFakRepository->get($schet_fakID);

        $schetFakPrint = $schetFakPrintService->getSchetFak($schetFak);

        $expenseDocument = $schetFak->getExpenseDocument();

        $expense_document_num = $expenseDocument->getDocument()->getDocumentNum();
        $expense_document_date = $expenseDocument->getDateofadded();

        $document_num = $schetFak->getDocument()->getDocumentNum();
        $document_date = $schetFak->getDateofadded();

        return $this->render('app/orders/schetFak/printSchetFak.html.twig', [
                'schetFak' => $schetFak,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'expense_document_num' => $expense_document_num,
                'expense_document_date' => $expense_document_date,
                'schetFakPrint' => $schetFakPrint
            ] + $this->getOrderGoods($expenseDocument));
    }

    /**
     * @Route("/schet_fak_xml.php", name="schet_fak_xml.php")
     * @param Request $request
     * @param SchetFakRepository $schetFakRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @param GuidGenerator $guidGenerator
     * @return Response
     */
    public function schetFakXml(
        Request              $request,
        SchetFakRepository   $schetFakRepository,
        ExpenseDocumentPrintService $expenseDocumentPrintService,
        GuidGenerator        $guidGenerator
    ): Response
    {
        $schet_fakID = $request->query->getInt('id');
        $schetFak = $schetFakRepository->get($schet_fakID);

        $expenseDocument = $schetFak->getExpenseDocument();

        $osn = $expenseDocumentPrintService->osn($expenseDocument);

        $expense_document_num = $expenseDocument->getDocument()->getDocumentNum();
        $expense_document_date = $expenseDocument->getDateofadded();

        $document_num = $schetFak->getDocument()->getDocumentNum();
        $document_date = $schetFak->getDateofadded();

        $filename = "ON_NSCHFDOPPR_" . $expenseDocument->getExpUser()->getEdo() . "_" . $expenseDocument->getExpFirm()->getEdo() . "_" . $expenseDocument->getDateofadded()->format('Ymd') . "_" . $guidGenerator->generate();

        $xml = $this->renderView('app/orders/schetFak/printSchetFak.xml.twig', [
                'schetFak' => $schetFak,
                'expenseDocument' => $expenseDocument,
                'nakladnayaOsn' => $osn,
                'document_num' => $document_num,
                'document_date' => $document_date,
                'expense_document_num' => $expense_document_num,
                'expense_document_date' => $expense_document_date,
                'filename' => $filename
            ] + $this->getOrderGoods($expenseDocument));

        $xml = mb_convert_encoding($xml, "windows-1251", "utf-8");

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'xml');
        return $response;
    }

    /**
     * @Route("/schet_fak_excel.php", name="schet_fak_excel.php")
     * @param Request $request
     * @param SchetFakRepository $schetFakRepository
     * @param SchetFakPrintService $schetFakPrintService
     * @param SchetFakXlsHelper $schetFakXlsHelper
     * @return Response
     */
    public function schetFakExcel(
        Request              $request,
        SchetFakRepository   $schetFakRepository,
        SchetFakPrintService $schetFakPrintService,
        SchetFakXlsHelper    $schetFakXlsHelper
    ): Response
    {

        $schet_fakID = $request->query->getInt('id');
        $schetFak = $schetFakRepository->get($schet_fakID);

        $schetFakPrint = $schetFakPrintService->getSchetFak($schetFak);

        $expenseDocument = $schetFak->getExpenseDocument();

        $expense_document_num = $expenseDocument->getDocument()->getDocumentNum();
        $expense_document_date = $expenseDocument->getDateofadded();

        $document_num = $schetFak->getDocument()->getDocumentNum();
        $document_date = $schetFak->getDateofadded();

        try {
            $writer = $this->excel($expenseDocument, $schetFak, $schetFakPrint, $schetFakXlsHelper);

            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="schet_fak.xls"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
        } catch (Exception | \PhpOffice\PhpSpreadsheet\Exception $e) {
        }
        return $this->json([]);
    }

    /**
     * @param ExpenseDocument $expenseDocument
     * @param SchetFak $schetFak
     * @param SchetFakPrint $schetFakPrint
     * @param SchetFakXlsHelper $schetFakXlsHelper
     * @return IWriter
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function excel(ExpenseDocument $expenseDocument, SchetFak $schetFak, SchetFakPrint $schetFakPrint, SchetFakXlsHelper $schetFakXlsHelper): IWriter
    {
        $expense_document_num = $expenseDocument->getDocument()->getDocumentNum();
        $expense_document_date = $expenseDocument->getDateofadded();

        $document_num = $schetFak->getDocument()->getDocumentNum();
        $document_date = $schetFak->getDateofadded();

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

        $arr = $this->getOrderGoods($expenseDocument);

        $spreadsheet = $schetFakXlsHelper->merge($spreadsheet);
        $aSheet = $schetFakXlsHelper->header($aSheet, $schetFakPrint, $document_num, $document_date, $expense_document_num, $expense_document_date, count($arr['orderGoods']));

        $i = 1;
        $j = 17;
        $page = 1;
        $page_num = 1;

        $sumPage = [
            'priceWithoutNds' => 0,
            'sumWithoutNds' => 0,
            'ndsSum' => 0,
            'sum' => 0,
            'quantity' => 0,
        ];

        $arStyles = $schetFakXlsHelper->getStyles();
        foreach ($arr['orderGoods'] as $orderGood) {

            if (($page == 15) && ($i == 15) || ($page == 24)) {
                // Итоги по странице
                $spreadsheet = $schetFakXlsHelper->mergeSumPage($spreadsheet, $j);
                $aSheet = $schetFakXlsHelper->rowSumPage($aSheet, $sumPage, $j);

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
                $spreadsheet = $schetFakXlsHelper->mergeHeaderPage($spreadsheet, $j);
                $aSheet = $schetFakXlsHelper->rowHeaderPage($aSheet, $page_num, $j, $document_num, $document_date);

                $j++;
            }

            if ($page == 1) {
                // Названия колонок
                $spreadsheet = $schetFakXlsHelper->mergeRowNames($spreadsheet, $j);
                $aSheet = $schetFakXlsHelper->rowNames($aSheet, $j, $page_num);
                $j++;
                $j++;
                $aSheet->getRowDimension($j)->setRowHeight(11.25);
                $spreadsheet = $schetFakXlsHelper->mergeRow($spreadsheet, $j);
                $aSheet = $schetFakXlsHelper->rowNamesNumbers($aSheet, $j);
                //Конец названия колонок
                $j++;
            }

            $aSheet->getRowDimension($j)->setRowHeight(22.25);
            $spreadsheet = $schetFakXlsHelper->mergeRow($spreadsheet, $j);

            $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['tableTextFont']);
            $aSheet->setCellValue("B" . $j . "", $orderGood['detail_name'] . " (" . $orderGood['number'] . ")");

            $aSheet->getStyle('C' . $j . ':H' . $j)->applyFromArray($arStyles['tableText1Font']);

            $aSheet->getStyle('D' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('D' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->setCellValue("D" . $j . "", "-");

            $aSheet->getStyle('E' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->setCellValue("E" . $j . "", $orderGood['okei']);

            $aSheet->getStyle('F' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->setCellValue("F" . $j . "", $orderGood['ed_izm']);

            $aSheet->getStyle('I' . $j . ':M' . $j)->applyFromArray($arStyles['tablePriceFont']);

            $aSheet->setCellValue("I" . $j . "", $orderGood['quantity']);

            $aSheet->getStyle('J' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("J" . $j . "", $orderGood['priceWithoutNds']);

            $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("L" . $j . "", $orderGood['sumWithoutNds']);

            $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('N' . $j . ':P' . $j)->applyFromArray($arStyles['tableText1Font']);

            $aSheet->setCellValue("N" . $j . "", "без акциза");

            if ($arr['nalogNds']->getNds() == 0) $nds_val = "-"; else $nds_val = ($arr['nalogNds']->getNds() * 1) . "%";
            $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("P" . $j . "", $nds_val);

            $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

            $aSheet->getStyle('Q' . $j . ':V' . $j)->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("Q" . $j . "", $orderGood['ndsSum']);

            $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("U" . $j . "", $orderGood['sum']);

            $aSheet->getStyle('W' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('W' . $j . '')->applyFromArray($arStyles['tableText1Font']);
            $aSheet->setCellValue("W" . $j . "", " " . $orderGood['country_code']);

            $aSheet->getStyle('X' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('X' . $j . '')->applyFromArray($arStyles['tableTextFont']);
            $aSheet->setCellValue("X" . $j . "", $orderGood['country_name']);

            $aSheet->getStyle('Y' . $j . '')->getAlignment()->setWrapText(true);
            $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['tableTextFont']);
            $aSheet->setCellValue("Y" . $j . "", $orderGood['gtd']);

            $aSheet->getStyle('Z' . $j . '')->applyFromArray($arStyles['tableText1Font']);
            $aSheet->getStyle('AA' . $j . '')->applyFromArray($arStyles['tableTextFont']);
            $aSheet->getStyle('AB' . $j . '')->applyFromArray($arStyles['tableTextFont']);

            $sumPage['priceWithoutNds'] += $orderGood['priceWithoutNds'];
            $sumPage['sumWithoutNds'] += $orderGood['sumWithoutNds'];
            $sumPage['ndsSum'] += $orderGood['ndsSum'];
            $sumPage['sum'] += $orderGood['discountPrice'] * $orderGood['quantity'];
            $sumPage['quantity'] += $orderGood['quantity'];

            $i++;
            $j++;
            $page++;
        }
        if (count($arr['orderGoods']) > 16) {

            $aSheet->getRowDimension($j)->setRowHeight(11.75);
            $spreadsheet = $schetFakXlsHelper->mergeSumPage($spreadsheet, $j);
            $aSheet = $schetFakXlsHelper->rowSumPage($aSheet, $sumPage, $j);

            $j++;
        }

        $aSheet->getRowDimension($j)->setRowHeight(11.75);
        $spreadsheet = $schetFakXlsHelper->mergeRowSum($spreadsheet, $j);

        $aSheet = $schetFakXlsHelper->lastRowSum($aSheet, $arr['sum'], $j);


        $j++;
        $j++;

        $aSheet->getRowDimension($j)->setRowHeight(23.25);

        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('K' . $j . ':N' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('O' . $j . ':Q' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('S' . $j . ':V' . $j . '');


        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);

        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['podpFont']);
        $aSheet->setCellValue("B" . $j . "", "Руководитель организации\nили иное уполномоченное лицо");

        $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("C" . $j . "", "");

        $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("H" . $j . "", "");

        $aSheet->getStyle('K' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('K' . $j . ':N' . $j)->applyFromArray($arStyles['podpFont']);
        $aSheet->setCellValue("K" . $j . "", "Главный бухгалтер\nили иное уполномоченное лицо");

        $aSheet->getStyle('O' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('O' . $j . ':Q' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("O" . $j . "", "");

        $aSheet->getStyle('S' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('S' . $j . ':V' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("S" . $j . "", "");


        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(11.25);

        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('K' . $j . ':N' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('O' . $j . ':Q' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('S' . $j . ':V' . $j . '');

        $aSheet->getStyle('C' . $j . ':V' . $j)->applyFromArray($arStyles['podp2Font']);

        $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("C" . $j . "", "(подпись)");

        $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("H" . $j . "", "(ф.и.о.)");

        $aSheet->getStyle('O' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("O" . $j . "", "(подпись)");

        $aSheet->getStyle('S' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("S" . $j . "", "(ф.и.о.)");


        $j++;
        $j++;

        $aSheet->getRowDimension($j)->setRowHeight(23.25);

        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('M' . $j . ':V' . $j . '');


        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['podpFont']);
        $aSheet->setCellValue("B" . $j . "", "Индивидуальный предприниматель\nили иное уполномоченное лицо");

        $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("C" . $j . "", "");

        $aSheet->getStyle('H' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("H" . $j . "", "");

        $aSheet->getStyle('M' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('M' . $j . ':V' . $j)->applyFromArray($arStyles['podp1Font']);
        $aSheet->setCellValue("M" . $j . "", "");


        $j++;
        $aSheet->getRowDimension($j)->setRowHeight(18.5);

        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('H' . $j . ':J' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('M' . $j . ':V' . $j . '');


        $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('C' . $j . ':F' . $j)->applyFromArray($arStyles['podp2Font']);
        $aSheet->setCellValue("C" . $j . "", "(подпись)");

        $aSheet->getStyle('M' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('H' . $j . ':J' . $j)->applyFromArray($arStyles['podp2Font']);
        $aSheet->setCellValue("H" . $j . "", "(ф.и.о.)");

        $aSheet->getStyle('M' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('M' . $j . ':V' . $j)->applyFromArray($arStyles['podp2Font']);
        $aSheet->setCellValue("M" . $j . "", "(реквизиты свидетельства о государственной регистрации\nиндивидуального предпринимателя)");

        $writer = IOFactory::createWriter($spreadsheet, 'Xls');

        return $writer;
    }

    private function getOrderGoods(ExpenseDocument $expenseDocument): array
    {
        $nalogNds = $this->nalogNdsRepository->getLastByFirm($expenseDocument->getExpFirm(), $expenseDocument->getDateofadded());

        $orderGoods = $this->orderGoodFetcher->findByExpenseDocument($expenseDocument->getId());

        $sum = [
            'priceWithoutNds' => 0,
            'sumWithoutNds' => 0,
            'ndsSum' => 0,
            'sum' => 0,
            'quantity' => 0,
        ];

        foreach ($orderGoods as &$item) {
            $item['zapCard'] = $this->zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);

            $item['country_name'] = "Япония";
            $item['country_code'] = "392";

            if ($item['zapCard']) {
                $item['ed_izm'] = $item['zapCard']->getEdIzm()->getNameShort();
                $item['okei'] = $item['zapCard']->getEdIzm()->getOkei();
                $item['detail_name'] = $item['zapCard']->getDetailName();
                if ($item['zapCard']->getManager()) {
                    $item['country_name'] = $item['zapCard']->getManager()->getName();
                    $item['country_code'] = $item['zapCard']->getManager()->getCode();
                }
            } else {
                $item['ed_izm'] = "шт";
                $item['okei'] = "796";
                $item['detail_name'] = '';
            }

            if ($item['shop_gtdID']) {
                $item['gtd'] = $this->shopGtdRepository->get($item['shop_gtdID'])->getName()->getValue();
            } elseif ($item['shop_gtdID1']) {
                $item['gtd'] = $this->shopGtdRepository->get($item['shop_gtdID1'])->getName()->getValue();
            } else {
                $command = new Create\Command();
                while (!$this->validator->validate($command) || strlen($command->name) <= 12) {
                    $shopGtd = $this->shopGtdRepository->getRand();
                    $command->name = $shopGtd->getName()->getValue();
                }
                if (isset($shopGtd)) {
                    $this->incomeRepository->updateGtd($item['incomeID'], $shopGtd);
                }
                $item['gtd'] = $command->name;
            }

            $item['discountPrice'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
            $item['ndsPrice'] = $item['discountPrice'] / (100 + $nalogNds->getNds()) * $nalogNds->getNds();
            $item['priceWithoutNds'] = $item['discountPrice'] - $item['ndsPrice'];
            $item['sum'] = $item['discountPrice'] * $item['quantity'];
            $item['ndsSum'] = $item['ndsPrice'] * $item['quantity'];
            $item['sumWithoutNds'] = $item['sum'] - $item['ndsSum'];

            $sum['priceWithoutNds'] += $item['priceWithoutNds'];
            $sum['sumWithoutNds'] += $item['sumWithoutNds'];
            $sum['ndsSum'] += $item['ndsSum'];
            $sum['sum'] += $item['discountPrice'] * $item['quantity'];
            $sum['quantity'] += $item['quantity'];
        }

        $sum = [
            'priceWithoutNds' => round($sum['priceWithoutNds'], 2),
            'sumWithoutNds' => round($sum['sumWithoutNds'], 2),
            'ndsSum' => round($sum['ndsSum'], 2),
            'sum' => round($sum['sum'], 2),
            'quantity' => round($sum['quantity'], 2),
        ];

        return compact('nalogNds', 'orderGoods', 'sum');
    }

}
