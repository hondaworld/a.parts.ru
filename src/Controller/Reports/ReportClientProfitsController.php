<?php

namespace App\Controller\Reports;

use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportClientProfitFetcher;
use App\ReadModel\User\OptFetcher;
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
 * @Route("/reports/clientProfits", name="reports.clientProfits")
 */
class ReportClientProfitsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportClientProfitFetcher $fetcher
     * @param OptFetcher $optFetcher
     * @param BalancePercent $balancePercent
     * @return Response
     */
    public function index(Request $request, ReportClientProfitFetcher $fetcher, OptFetcher $optFetcher, BalancePercent $balancePercent): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientProfit');

        $filter = new Filter\ClientProfit\Filter();
        $form = $this->createForm(Filter\ClientProfit\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        $opts = [];
        foreach ($optFetcher->assoc() as $optID => $name) {
            $opts[$optID] = $name;
            if ($optID == Opt::DEFAULT_OPT_ID) {
                $opts['opt'] = 'Опт';
            }
        }

        try {
            $profits = $fetcher->all($opts, $filter);
            $dates = $fetcher->dates($filter);
            $users = $fetcher->users($opts);

            foreach ($users as &$user) {
                $user['template'] = $this->renderView('app/reports/clientProfits/_users.html.twig', ['users' => $user]);
            }

            if ($profits) {
                $printProfits = $profits;
                foreach ($printProfits as &$printProfit) {
                    foreach ($printProfit as &$item) {
                        unset($item['date']);
                    }
                }
                $printCommand->data = json_encode($printProfits);
            }

            $prevProfits = $fetcher->prev($opts, $filter);
            $prevDates = $fetcher->prevDates($filter);

            if ($prevProfits) {
                foreach ($profits as $optID => $profit) {
                    foreach ($profit as $field => $item) {
                        $profits[$optID][$field]['percent'] = $balancePercent->get($profits[$optID][$field]['value'], $prevProfits[$optID][$field]['value']);
                    }
                }
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        $printUsersCommand = new PrintForm\ClientProfitUsers\Command();
        $printUsersForm = $this->createForm(PrintForm\ClientProfitUsers\Form::class, $printUsersCommand);

        return $this->render('app/reports/clientProfits/index.html.twig', [
            'opts' => $opts,
            'profits' => $profits ?? null,
            'prevProfits' => $prevProfits ?? null,
            'dates' => $dates ?? [],
            'users' => $users ?? [],
            'prevDates' => $prevDates ?? [],
            'filter' => $form->createView(),
            'printForm' => $printForm->createView(),
            'printUsersForm' => $printUsersForm->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param OptFetcher $optFetcher
     * @return Response
     */
    public function print(Request $request, OptFetcher $optFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientProfit');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $opts = $optFetcher->assoc();
        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/clientProfits/print.html.twig', [
            'opts' => $opts,
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/print/users/", name=".print.users")
     * @param Request $request
     * @return Response
     */
    public function printUsers(Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientProfit');

        $printCommand = new PrintForm\ClientProfitUsers\Command();
        $printForm = $this->createForm(PrintForm\ClientProfitUsers\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $html = $printCommand->data;

        return $this->render('app/reports/clientProfits/printUsers.html.twig', [
            'html' => $html
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param OptFetcher $optFetcher
     * @return Response
     */
    public function excel(Request $request, OptFetcher $optFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportClientProfit');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $opts = $optFetcher->assoc();
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

        $aSheet->setCellValue("A1", "Опт");
        $aSheet->setCellValue("B1", "Доход");
        $aSheet->setCellValue("C1", "Прибыль");
        $aSheet->setCellValue("D1", "МСК Доход");
        $aSheet->setCellValue("E1", "МСК Прибыль");
        $aSheet->setCellValue("F1", "МСК Доход сервис");
        $aSheet->setCellValue("G1", "МСК Прибыль сервис");
        $aSheet->setCellValue("H1", "СПБ Доход");
        $aSheet->setCellValue("I1", "СПБ Прибыль");
        $aSheet->setCellValue("J1", "СПБ Доход сервис");
        $aSheet->setCellValue("K1", "СПБ Прибыль сервис");
        $aSheet->setCellValue("L1", "Доход сервис");
        $aSheet->setCellValue("M1", "Прибыль сервис");

        $j = 2;
        foreach ($opts as $optID => $opt) {
            $aSheet->setCellValue("A" . $j . "", $opt);
            $aSheet->getStyle('B' . $j . ':N' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue("B" . $j . "", $profits[$optID]['income']['value']);
            $aSheet->setCellValue("C" . $j . "", $profits[$optID]['profit']['value']);
            $aSheet->setCellValue("D" . $j . "", $profits[$optID]['mskIncome']['value']);
            $aSheet->setCellValue("E" . $j . "", $profits[$optID]['mskProfit']['value']);
            $aSheet->setCellValue("F" . $j . "", $profits[$optID]['mskServiceIncome']['value']);
            $aSheet->setCellValue("G" . $j . "", $profits[$optID]['mskServiceProfit']['value']);
            $aSheet->setCellValue("H" . $j . "", $profits[$optID]['spbIncome']['value']);
            $aSheet->setCellValue("I" . $j . "", $profits[$optID]['spbProfit']['value']);
            $aSheet->setCellValue("J" . $j . "", $profits[$optID]['spbServiceIncome']['value']);
            $aSheet->setCellValue("K" . $j . "", $profits[$optID]['spbServiceProfit']['value']);
            $aSheet->setCellValue("L" . $j . "", $profits[$optID]['serviceIncome']['value']);
            $aSheet->setCellValue("M" . $j . "", $profits[$optID]['serviceProfit']['value']);
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
