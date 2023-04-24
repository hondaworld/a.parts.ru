<?php

namespace App\Controller\Reports;

use App\ReadModel\Finance\FinanceTypeFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportManagerMoneyFetcher;
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
 * @Route("/reports/managerMoney", name="reports.managerMoney")
 */
class ReportManagerMoneyController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportManagerMoneyFetcher $fetcher
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function index(Request $request, ReportManagerMoneyFetcher $fetcher, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $filter = new Filter\ManagerMoney\Filter();
        if (!$filter->dateofreport) {
            $filter->dateofreport['date_from'] = null;
            $filter->dateofreport['date_till'] = null;
        }
        if (!$filter->dateofreport['date_from']) {
            $filter->dateofreport['date_from'] = '01.01.' . date('Y');
        }
        $form = $this->createForm(Filter\ManagerMoney\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();

        try {
            $profits = $fetcher->all($filter);
            $dates = $fetcher->dates($filter);

            $printProfits = $profits;
            foreach ($printProfits as &$printProfit) {
                unset($printProfit['date']);
            }
            $printCommand->data = json_encode($printProfits);

        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);


        return $this->render('app/reports/managerMoney/index.html.twig', [
            'financeTypes' => $financeTypes,
            'managers' => $managers,
            'profits' => $profits ?? null,
            'dates' => $dates ?? [],
            'period' => $filter->period,
            'filter' => $form->createView(),
            'printForm' => $printForm->createView(),
        ]);
    }

    /**
     * @Route("/view", name=".view")
     * @param Request $request
     * @param ReportManagerMoneyFetcher $fetcher
     * @param ManagerSettings $settings
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function view(Request $request, ReportManagerMoneyFetcher $fetcher, ManagerSettings $settings, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $settings = $settings->get('reportManagerMoneyView');

        $filter = new Filter\ManagerMoneyView\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;
        $form = $this->createForm(Filter\ManagerMoneyView\Form::class, $filter);
        $form->handleRequest($request);


        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();

        try {
            $pagination = $fetcher->view(
                $filter,
                $request->query->getInt('page', 1),
                $settings
            );
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('app/reports/managerMoney/view.html.twig', [
            'financeTypes' => $financeTypes,
            'managers' => $managers,
            'pagination' => $pagination ?? null,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/view/print", name=".view.print")
     * @param Request $request
     * @param ReportManagerMoneyFetcher $fetcher
     * @param ManagerSettings $settings
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function viewPrint(Request $request, ReportManagerMoneyFetcher $fetcher, ManagerSettings $settings, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $settings = $settings->get('reportManagerMoneyView');

        $filter = new Filter\ManagerMoneyView\Filter();
        $form = $this->createForm(Filter\ManagerMoneyView\Form::class, $filter);
        $form->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();

        try {
            $pagination = $fetcher->viewAll(
                $filter,
                $settings
            );
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->render('app/reports/managerMoney/printView.html.twig', [
            'financeType' => $financeTypes[$filter->finance_typeID],
            'manager' => $managers[$filter->managerID],
            'pagination' => $pagination ?? null,
        ]);
    }

    /**
     * @Route("/view/excel", name=".view.excel")
     * @param Request $request
     * @param ReportManagerMoneyFetcher $fetcher
     * @param ManagerSettings $settings
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function viewExcel(Request $request, ReportManagerMoneyFetcher $fetcher, ManagerSettings $settings, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $settings = $settings->get('reportManagerMoneyView');

        $filter = new Filter\ManagerMoneyView\Filter();
        $form = $this->createForm(Filter\ManagerMoneyView\Form::class, $filter);
        $form->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();

        try {
            $pagination = $fetcher->viewAll(
                $filter,
                $settings
            );

            $spreadsheet = new Spreadsheet();
            $aSheet = $spreadsheet->getActiveSheet();
            $aSheet->getPageMargins()->setTop(0);
            $aSheet->getPageMargins()->setLeft(0);
            $aSheet->getPageMargins()->setRight(0);
            $aSheet->getPageMargins()->setBottom(0);
            $aSheet
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

            $aSheet->setCellValue("A1", "Дата");
            $aSheet->setCellValue("B1", "Клиент");
            $aSheet->setCellValue("C1", "Сумма");


//            <td>{{ balance.dateofadded|date('d.m.Y H:i') }}</td>
//                    <td>{{ balance.user_name }}</td>
//                    <td class="text-right">{{ balance.balance|number_format(2, ',', ' ') }}</td>

            $j = 2;
            foreach ($pagination as $balance) {
                $aSheet->setCellValue("A" . $j . "", (new \DateTime($balance['dateofadded']))->format('d.m.Y H:i'));
                $aSheet->setCellValue("B" . $j . "", $balance['user_name']);
                $aSheet->getStyle('C' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("C" . $j . "", $balance['balance']);

                $j++;
            }
            $writer = IOFactory::createWriter($spreadsheet, 'Xls');
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="report.xls"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
        } catch (Exception | \PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->json([]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function print(Request $request, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();
        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/managerMoney/print.html.twig', [
            'financeTypes' => $financeTypes,
            'managers' => $managers,
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param FinanceTypeFetcher $financeTypeFetcher
     * @param ManagerFetcher $managerFetcher
     * @return Response
     */
    public function excel(Request $request, FinanceTypeFetcher $financeTypeFetcher, ManagerFetcher $managerFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportManagerMoney');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $financeTypes = $financeTypeFetcher->assoc();
        $managers = $managerFetcher->assoc();
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

        $aSheet->setCellValue("A1", "Менеджер");
        $aSheet->setCellValue("B1", "Сумма");
        $i = 2;
        foreach ($financeTypes as $financeType) {
            $aSheet->setCellValue(chr(65 + $i) . "1", $financeType);
            $i++;
        }

        $j = 2;
        foreach ($managers as $managerID => $managerName) {
            if (isset($profits[$managerID])) {
                $aSheet->setCellValue("A" . $j . "", $managerName);
                $aSheet->getStyle('B' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("B" . $j . "", $profits[$managerID]['value']);

                $i = 2;
                foreach ($financeTypes as $finance_typeID => $financeTypeName) {
                    $aSheet->getStyle(chr(65 + $i) . $j . ':' . chr(66 + $i) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    $aSheet->setCellValue(chr(65 + $i) . $j . "", $profits[$managerID]['financeTypes'][$finance_typeID] ?? 0);
                    $i++;
                }
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
