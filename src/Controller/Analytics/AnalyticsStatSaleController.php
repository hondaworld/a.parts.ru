<?php

namespace App\Controller\Analytics;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Analytics\Filter;
use App\ReadModel\Analytics\AnalyticsStatSaleFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Sklad\ZapSkladLocationFetcher;
use App\Security\Voter\StandartActionsVoter;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/analytics/statSale", name="analytics.statSale")
 */
class AnalyticsStatSaleController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsStatSale');

        $filter = new Filter\StatSale\Filter();
        if (!$filter->dateofreport) {
            $filter->dateofreport['date_from'] = null;
            $filter->dateofreport['date_till'] = null;
        }
        if (!$filter->dateofreport['date_from']) {
            $filter->dateofreport['date_from'] = '01.01.' . date('Y');
        }
        $form = $this->createForm(Filter\StatSale\Form::class, $filter);


        return $this->render('app/analytics/statSale/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param AnalyticsStatSaleFetcher $fetcher
     * @param ZapCardRepository $zapCardRepository
     * @param ZapSkladLocationFetcher $zapSkladLocationFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @return Response
     */
    public function print(
        Request                  $request,
        AnalyticsStatSaleFetcher $fetcher,
        ZapCardRepository        $zapCardRepository,
        ZapSkladLocationFetcher  $zapSkladLocationFetcher,
        ProviderPriceFetcher     $providerPriceFetcher,
        ZapCardAbcFetcher        $zapCardAbcFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsStatSale');

        $filter = new Filter\StatSale\Filter();
        $form = $this->createForm(Filter\StatSale\Form::class, $filter);
        $form->handleRequest($request);

        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');

        $providerPrices = $providerPriceFetcher->allArray();

        $printCommand = new PrintForm\Command();

        try {
            $all = $fetcher->all($filter);
            $zapCards = $zapCardRepository->findByZapCards(array_keys($all));
            $quantityMin = $zapSkladLocationFetcher->findQuantityMinByZapCards(array_keys($all));
            $incomeData = $fetcher->getIncomeData($filter, array_keys($all), $providerPrices);
            $abc = $zapCardAbcFetcher->findByZapCards(array_keys($all));
            uasort($zapCards, function (ZapCard $a, ZapCard $b) {
                return $a->getNumber()->getValue() <=> $b->getNumber()->getValue();
            });
            $dates = $fetcher->dates($filter);

            $printZapCards = [];
            foreach ($zapCards as $zapCardID => $zapCard) {
                $printZapCards[$zapCardID] = [
                    'number' => $zapCard->getNumber()->getValue(),
                    'creater_name' => $zapCard->getCreater()->getName(),
                    'detail_name' => $zapCard->getDetailName(),
                    'price' => $zapCard->getPrice(),
                    'manager_nick' => $zapCard->getManager() ? $zapCard->getManager()->getNick() : '',
                ];
            }

            $printCommand->data = json_encode(compact('all', 'printZapCards', 'quantityMin', 'incomeData', 'abc', 'dates'));
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        return $this->render('app/analytics/statSale/print.html.twig', [
            'all' => $all ?? [],
            'zapCards' => $zapCards ?? [],
            'quantityMin' => $quantityMin ?? [],
            'incomeData' => $incomeData ?? [],
            'providerPrices' => $providerPrices,
            'abc' => $abc ?? [],
            'dates' => $dates ?? [],
            'printForm' => $printForm->createView(),
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     * @throws Exception
     */
    public function excel(Request $request, ProviderPriceFetcher $providerPriceFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsStatSale');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $arr = json_decode($printCommand->data, true);

        $all = $arr['all'];
        $zapCards = $arr['printZapCards'];
        $quantityMin = $arr['quantityMin'];
        $incomeData = $arr['incomeData'];
        $abc = $arr['abc'];
        $dates = $arr['dates'];

        $providerPrices = $providerPriceFetcher->allArray();

        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $aSheet->setCellValue("A1", "ABC");
        $aSheet->setCellValue("B1", "Менеджер");
        $aSheet->setCellValue("C1", "Производитель");
        $aSheet->setCellValue("D1", "Номер");
        $aSheet->setCellValue("E1", "Наименование");
        $aSheet->setCellValue("F1", "Регион поставки");
        $aSheet->setCellValue("G1", "Дата первого прихода");
        $aSheet->setCellValue("H1", "Склад");
        $aSheet->setCellValue("I1", "Наличие");
        $aSheet->setCellValue("J1", "Количество");
        $aSheet->setCellValue("K1", "Закупка");
        $aSheet->setCellValue("L1", "Мин. МСК");
        $aSheet->setCellValue("M1", "Мин. СПБ");
        $i = 13;
        foreach ($dates as $date) {
            $aSheet->setCellValue($this->getExcelColChar($i) . "1", (new DateTime($date['date']))->format('m.Y') . ' - кол.');
            $i++;
        }
        $aSheet->setCellValue($this->getExcelColChar($i) . "1", "Итого кол.");
        $i++;
        foreach ($dates as $date) {
            $aSheet->setCellValue($this->getExcelColChar($i) . "1", (new DateTime($date['date']))->format('m.Y') . ' - прибыль');
            $i++;
        }
        $aSheet->setCellValue($this->getExcelColChar($i) . "1", "Итого прибыль");

        $j = 2;

        foreach ($zapCards as $zapCardID => $zapCard) {

            $abcItem = [];
            if (isset($abc[$zapCardID])) {
                foreach ($abc[$zapCardID] as $skladName => $abcName) {
                    $abcItem[] = $skladName . " - " . $abcName;
                }
            }

            $aSheet->setCellValue("A" . $j, implode("\n", $abcItem));
            $aSheet->setCellValue("B" . $j, $zapCard['manager_nick']);
            $aSheet->setCellValue("C" . $j, $zapCard['creater_name']);
            $aSheet->setCellValue("D" . $j, $zapCard['number']);
            $aSheet->setCellValue("E" . $j, $zapCard['detail_name']);
            $aSheet->setCellValue("F" . $j, isset($incomeData[$zapCardID]) ? (isset($incomeData[$zapCardID]['providerPriceID']) ? $providerPrices[$incomeData[$zapCardID]['providerPriceID']]['description'] : '') : '');
            $aSheet->setCellValue("G" . $j, isset($incomeData[$zapCardID]) ? (isset($incomeData[$zapCardID]['date_first_income']) ? (new DateTime($incomeData[$zapCardID]['date_first_income']))->format('d.m.Y') : '') : '');
            $aSheet->setCellValue("H" . $j, isset($incomeData[$zapCardID]) ? ($incomeData[$zapCardID]['is_sklad'] ? 'да' : 'нет') : '');
            $aSheet->setCellValue("I" . $j, isset($incomeData[$zapCardID]) ? ($incomeData[$zapCardID]['is_nal'] ? 'да' : 'нет') : '');
            $aSheet->setCellValue("J" . $j, isset($incomeData[$zapCardID]) ? $incomeData[$zapCardID]['quantity'] : '');
            $aSheet->getStyle("K" . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("K" . $j, $zapCard['price']);
            $aSheet->setCellValue("L" . $j, isset($quantityMin[$zapCardID]) ? ($quantityMin[$zapCardID][1] ?? '') : '');
            $aSheet->setCellValue("M" . $j, isset($quantityMin[$zapCardID]) ? ($quantityMin[$zapCardID][5] ?? '') : '');

            $i = 13;
            foreach ($dates as $date) {
                $aSheet->setCellValue($this->getExcelColChar($i) . $j, $all[$zapCardID]['date'][(new DateTime($date['date']))->format('Y-m')]['quantity'] ?? '');
                $i++;
            }

            $aSheet->setCellValue($this->getExcelColChar($i) . $j, $all[$zapCardID]['quantity']);
            $i++;
            foreach ($dates as $date) {
                $aSheet->setCellValue($this->getExcelColChar($i) . $j, $all[$zapCardID]['date'][(new DateTime($date['date']))->format('Y-m')]['sum'] ?? '');
                $i++;
            }
            $aSheet->setCellValue($this->getExcelColChar($i) . $j, $all[$zapCardID]['sum']);

            $j++;
        }

        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report.xls"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
        } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

//        return $this->render('app/home.html.twig');
        return $this->json([]);
    }

    private function getExcelColChar(int $i): string
    {
        $addChar = '';
        if ($i >= 26) $addChar = chr(65 + floor($i / 26) - 1);
        return $addChar . chr(65 + $i % 26);
    }
}
