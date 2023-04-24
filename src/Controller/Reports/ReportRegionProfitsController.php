<?php

namespace App\Controller\Reports;

use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportRegionProfitFetcher;
use App\ReadModel\Shop\ResellerFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\BalancePercent;
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
 * @Route("/reports/regionProfits", name="reports.regionProfits")
 */
class ReportRegionProfitsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportRegionProfitFetcher $fetcher
     * @param BalancePercent $balancePercent
     * @param ResellerFetcher $resellerFetcher
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, ReportRegionProfitFetcher $fetcher, BalancePercent $balancePercent, ResellerFetcher $resellerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportRegionProfit');

        $filter = new Filter\RegionProfit\Filter();
        $form = $this->createForm(Filter\RegionProfit\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

//        try {
        $profits = $fetcher->all($filter);
        $dates = $fetcher->dates($filter);

        if ($profits) {
            $printProfits = $profits;
            foreach ($printProfits as &$printProfit) {
                foreach ($printProfit as &$item) {
                    unset($item['date']);
                }
            }
            $printCommand->data = json_encode($printProfits);
        }

        $prevProfits = $fetcher->prev($filter);
        $prevDates = $fetcher->prevDates($filter);

        if ($prevProfits) {
            foreach ($profits as $region => $regions) {
                foreach ($regions as $opt => $opts) {
                    foreach ($opts as $field => $item) {
                        $profits[$region][$opt][$field]['percent'] = $balancePercent->get($profits[$region][$opt][$field]['value'], $prevProfits[$region][$opt][$field]['value']);
                    }
                }
            }
        }
//        } catch (Exception $e) {
//            $this->addFlash('error', $e->getMessage());
//        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);


        return $this->render('app/reports/regionProfits/index.html.twig', [
            'profits' => $profits ?? null,
            'prevProfits' => $prevProfits ?? null,
            'dates' => $dates ?? [],
            'users' => $users ?? [],
            'prevDates' => $prevDates ?? [],
            'filter' => $form->createView(),
            'printForm' => $printForm->createView(),
            'resellers' => $resellerFetcher->assoc(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @return Response
     */
    public function print(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportRegionProfit');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/regionProfits/print.html.twig', [
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param ResellerFetcher $resellerFetcher
     * @return Response
     */
    public function excel(Request $request, ResellerFetcher $resellerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportRegionProfit');

        $resellers = $resellerFetcher->assoc();

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $profits = json_decode($printCommand->data, true);

        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $aSheet->setCellValue("A1", "Регион");
        $aSheet->setCellValue("B1", "Доход");
        $aSheet->setCellValue("C1", "Прибыль");
        $aSheet->setCellValue("D1", "Чеки");

        $j = 2;
        foreach ($profits as $region => $regions) {
            foreach ($regions as $opt => $opts) {

                $regionName = $region == 'msk' ? 'Москва' : ($region == 'spb' ? 'СПБ' : ($region == 'region' ? 'Регионы' : ''));
                $optName = $opt == 'opt' ? 'опт' : ($opt == 'notOpt' ? 'розница' : ($opt == 'service' ? 'сервис' : $resellers[$opt]));

                $aSheet->setCellValue("A" . $j . "", $regionName . ' ' . $optName);
                $aSheet->getStyle('B' . $j . ':F' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("B" . $j . "", $opts['income']['value']);
                $aSheet->setCellValue("C" . $j . "", $opts['profit']['value']);
                $aSheet->setCellValue("D" . $j . "", $opts['checks']['value']);
                $j++;
            }
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

        return $this->json([]);
    }
}
