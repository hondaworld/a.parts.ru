<?php

namespace App\Controller\Reports;

use App\ReadModel\Income\IncomeStatusFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportIncomeGoodFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
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
 * @Route("/reports/incomeGoods", name="reports.incomeGoods")
 */
class ReportIncomeGoodsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportIncomeGoodFetcher $fetcher
     * @param IncomeStatusFetcher $incomeStatusFetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ReportIncomeGoodFetcher $fetcher, IncomeStatusFetcher $incomeStatusFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportIncomeGood');

        $settings = $settings->get('reportManagerMoneyView');

        $filter = new Filter\IncomeGood\Filter();
        $form = $this->createForm(Filter\IncomeGood\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        $statuses = $incomeStatusFetcher->assocForReportIncomeGoods();

        ini_set('max_execution_time', '900');

        $sum = 0;
        $sumStatuses = [];

        try {
            $profits = $fetcher->all($filter, $statuses, $settings);
            foreach ($profits->getItems() as $item) {
                $sum += $item['sum'];
            }


            $profitsStatuses = $fetcher->allWithStatuses($filter, $statuses);
            foreach ($profitsStatuses as $profit) {
                foreach ($profit['statuses'] as $status => $sumStatus) {
                    $sumStatuses[$status] = ($sumStatuses[$status] ?? 0) + $sumStatus;
                }
            }

            $printCommand->data = json_encode([
                'profits' => $profits->getItems(),
                'profitsStatuses' => $profitsStatuses
            ]);
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        return $this->render('app/reports/incomeGoods/index.html.twig', [
            'statuses' => $statuses,
            'profits' => $profits ?? null,
            'sum' => $sum,
            'sumStatuses' => $sumStatuses,
            'profitsStatuses' => $profitsStatuses ?? null,
            'filter' => $form->createView(),
            'printForm' => $printForm->createView(),
            'filter_sklad' => $filter->sklad,
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param IncomeStatusFetcher $incomeStatusFetcher
     * @return Response
     */
    public function print(Request $request, IncomeStatusFetcher $incomeStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportIncomeGood');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $statuses = $incomeStatusFetcher->assocForReportIncomeGoods();
        $arr = json_decode($printCommand->data, true);
        $profits = $arr['profits'];
        $profitsStatuses = $arr['profitsStatuses'];

        return $this->render('app/reports/incomeGoods/print.html.twig', [
            'statuses' => $statuses,
            'profits' => $profits,
            'profitsStatuses' => $profitsStatuses,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param IncomeStatusFetcher $incomeStatusFetcher
     * @return Response
     */
    public function excel(Request $request, IncomeStatusFetcher $incomeStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportIncomeGood');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $statuses = $incomeStatusFetcher->assocForReportIncomeGoods();
        $arr = json_decode($printCommand->data, true);
        $profits = $arr['profits'];
        $profitsStatuses = $arr['profitsStatuses'];

        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $aSheet->setCellValue("A1", "Поставщик");
        $aSheet->setCellValue("B1", "Сумма");
        $i = 2;
        foreach ($statuses as $statusName) {
            $aSheet->setCellValue(chr(65 + $i) . "1", $statusName);
            $i++;
        }

        $j = 2;

        foreach ($profits as $provider) {
            $aSheet->setCellValue("A" . $j . "", $provider['name']);
            $aSheet->getStyle('B' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("B" . $j . "", $provider['sum']);
            $i = 2;
            foreach ($statuses as $status => $statusName) {
                $aSheet->getStyle(chr(65 + $i) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue(chr(65 + $i) . $j, $profitsStatuses[$provider['providerID']]['statuses'][$status] ?? 0);
                $i++;
            }
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

        return $this->json([]);
    }
}
