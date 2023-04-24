<?php

namespace App\Controller\Reports;

use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportClientBalanceFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
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
 * @Route("/reports/clientBalance", name="reports.clientBalance")
 */
class ReportClientBalanceController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportClientBalanceFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ReportClientBalanceFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientBalance');

        $settings = $settings->get('reportClientBalance');

        $filter = new Filter\ClientBalance\Filter();
        $form = $this->createForm(Filter\ClientBalance\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        $pagination = $fetcher->all($filter, $settings);
        $printCommand->data = json_encode($pagination->getItems());
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);


        return $this->render('app/reports/clientBalance/index.html.twig', [
            'pagination' => $pagination,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientBalance');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $pagination = json_decode($printCommand->data, true);

        return $this->render('app/reports/clientBalance/print.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @return Response
     */
    public function excel(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientBalance');

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
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

        $aSheet->setCellValue("A1", "ID");
        $aSheet->setCellValue("B1", "Клиент");
        $aSheet->setCellValue("C1", "Баланс");
        $aSheet->setCellValue("D1", "Метод оплаты");

        $j = 2;
        foreach ($pagination as $user) {
            $aSheet->setCellValue("A" . $j . "", $user['userID']);
            $aSheet->setCellValue("B" . $j . "", $user['name']);
            $aSheet->getStyle('C' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("C" . $j . "", $user['balance']);
            $aSheet->setCellValue("D" . $j . "", $user['shop_pay_type']);
            $j++;
        }

        try {
            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report.xls"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->json([]);
    }
}
