<?php

namespace App\Controller\Reports;

use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportNumbersNotSaleFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
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
 * @Route("/reports/numbersNotSale", name="reports.numbersNotSale")
 */
class ReportNumbersNotSaleController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportNumbersNotSaleFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ReportNumbersNotSaleFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportNumbersNotSale');

        $settings = $settings->get('reportNumbersNotSale');

        $filter = new Filter\NumbersNotSale\Filter();
        $form = $this->createForm(Filter\NumbersNotSale\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        try {
            $pagination = $fetcher->all($filter, $settings);
            $printCommand->data = json_encode($pagination->getItems());
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        return $this->render('app/reports/numbersNotSale/index.html.twig', [
            'pagination' => $pagination ?? null,
            'filter' => $form->createView(),
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportNumbersNotSale');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $pagination = json_decode($printCommand->data, true);

        return $this->render('app/reports/numbersNotSale/print.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function excel(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportNumbersNotSale');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $pagination = json_decode($printCommand->data, true);

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
        $aSheet->setCellValue("B1", "Производитель");
        $aSheet->setCellValue("C1", "Номер");
        $aSheet->setCellValue("D1", "Количество");
        $aSheet->setCellValue("E1", "Сумма");
        $aSheet->setCellValue("F1", "Дней не продается");
        $aSheet->setCellValue("G1", "ID самого старого прихода");
        $aSheet->setCellValue("H1", "Дата прихода");
        $aSheet->setCellValue("I1", "Цена прихода");

        $j = 2;

        foreach ($pagination as $item) {
            $abcItem = [];
            foreach ($item['abc'] as $skladName => $abc) {
                $abcItem[] = $skladName . " - " . $abc;
            }

            $aSheet->setCellValue("A" . $j . "", implode("\n", $abcItem));
            $aSheet->setCellValue("B" . $j . "", $item['creater_name']);
            $aSheet->setCellValue("C" . $j . "", $item['number']);
            $aSheet->setCellValue("D" . $j . "", $item['quantity']);
            $aSheet->getStyle('E' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("E" . $j . "", $item['sum']);
            $aSheet->setCellValue("F" . $j . "", $item['days']);
            $aSheet->setCellValue("G" . $j . "", $item['income_incomeID']);
            $aSheet->setCellValue("H" . $j . "", $item['income_dateofin'] ? (new DateTime($item['income_dateofin']))->format('d.m.Y') : '');
            $aSheet->getStyle('I' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("I" . $j . "", $item['income_price']);
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
