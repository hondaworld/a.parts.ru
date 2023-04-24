<?php

namespace App\Controller\Reports;

use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportFinanceTypeFetcher;
use App\Security\Voter\StandartActionsVoter;
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
 * @Route("/reports/financeTypes", name="reports.financeTypes")
 */
class ReportFinanceTypesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportFinanceTypeFetcher $fetcher
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @return Response
     */
    public function index(Request $request, ReportFinanceTypeFetcher $fetcher, FinanceTypeFetcher $financeTypeFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportFinanceType');

        $filter = new Filter\FinanceType\Filter();
        $form = $this->createForm(Filter\FinanceType\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        $financeTypes = $financeTypeFetcher->assoc();

        try {
            $profits = $fetcher->all($financeTypes, $filter);
            $dates = $fetcher->dates($filter);

            $printProfits = $profits;
            foreach ($printProfits as &$printProfit) {
                foreach ($printProfit as &$item) {
                    unset($item['date']);
                }
            }
            $printCommand->data = json_encode($printProfits);

        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);


        return $this->render('app/reports/financeTypes/index.html.twig', [
            'financeTypes' => $financeTypes,
            'profits' => $profits ?? null,
            'dates' => $dates ?? [],
            'period' => $filter->period,
            'filter' => $form->createView(),
            'printForm' => $printForm->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @return Response
     */
    public function print(Request $request, FinanceTypeFetcher $financeTypeFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportFinanceType');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/financeTypes/print.html.twig', [
            'financeTypes' => $financeTypes,
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @return Response
     */
    public function excel(Request $request, FinanceTypeFetcher $financeTypeFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportFinanceType');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
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

        $aSheet->setCellValue("A1", "Вид оплаты");
        $aSheet->setCellValue("B1", "Сумма");

        $j = 2;
        foreach ($financeTypes as $finance_typeID => $financeTypeName) {
            $aSheet->setCellValue("A" . $j . "", $financeTypeName);
            $aSheet->getStyle('B' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("B" . $j . "", $profits[$finance_typeID]['value']);
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
