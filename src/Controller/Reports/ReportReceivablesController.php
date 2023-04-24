<?php

namespace App\Controller\Reports;

use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportReceivableFetcher;
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
 * @Route("/reports/receivables", name="reports.receivables")
 */
class ReportReceivablesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportReceivableFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ReportReceivableFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportReceivable');

        $settings = $settings->get('reportReceivable');

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        try {
            $profits = $fetcher->all($settings);
            $printCommand->data = json_encode($profits->getItems());

        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);


        return $this->render('app/reports/receivables/index.html.twig', [
            'profits' => $profits ?? null,
            'printForm' => $printForm->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @return Response
     */
    public function print(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportReceivable');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/receivables/print.html.twig', [
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @return Response
     */
    public function excel(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportReceivable');

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
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

        $aSheet->setCellValue("A1", "Клиент");
        $aSheet->setCellValue("B1", "Задолженность");
        $aSheet->setCellValue("C1", "Дата образования задолженности");

        $j = 2;
        foreach ($profits as $profit) {
            $aSheet->setCellValue("A" . $j . "", $profit['name']);
            $aSheet->getStyle('B' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("B" . $j . "", $profit['balance']);
            $aSheet->setCellValue("C" . $j . "", (new \DateTime($profit['debts_date']))->format('d.m.Y'));
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
