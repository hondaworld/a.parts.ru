<?php

namespace App\Controller\Reports;

use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Manager\ManagerFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportOrdersCountFetcher;
use App\ReadModel\Shop\DeleteReasonFetcher;
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
 * @Route("/reports/ordersCount", name="reports.ordersCount")
 */
class ReportOrdersCountController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ReportOrdersCountFetcher $fetcher
     * @param ManagerFetcher $managerFetcher
     * @param DeleteReasonFetcher $deleteReasonFetcher
     * @param CreaterFetcher $createrFetcher
     * @return Response
     */
    public function index(Request $request, ReportOrdersCountFetcher $fetcher, ManagerFetcher $managerFetcher, DeleteReasonFetcher $deleteReasonFetcher, CreaterFetcher $createrFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportOrdersCount');

        $filter = new Filter\OrdersCount\Filter();
        $form = $this->createForm(Filter\OrdersCount\Form::class, $filter);
        $form->handleRequest($request);

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        try {
            $profits = $fetcher->all($filter);
            if ($profits) {
                krsort($profits);

                $managersAll = $managerFetcher->assoc();
                $deleteReasons = $deleteReasonFetcher->assoc();
                $creaters = $createrFetcher->assoc();

                $managers = [];
                $templates = [
                    'managers' => [],
                    'countNotOpt' => [],
                    'countOpt' => [],
                    'countDeleted' => [],
                ];
                foreach ($profits as $dateofadded => $profit) {
                    if (isset($profit['managers'])) {
                        foreach (array_keys($profit['managers']) as $managerID) {
                            if (!in_array($managerID, $managers)) $managers[] = $managerID;
                        }
                        foreach ($profit['managers'] as $managerID => $manager) {
                            if (isset($manager['reasons'])) {
                                foreach ($manager['reasons'] as $reasonID => $reason) {
                                    $templates['managers'][$dateofadded][$managerID][$reasonID] = $this->renderView('app/reports/ordersCount/_orders.html.twig', ['orders' => $reason]);
                                }
                            }
                        }
                    }

                    if (isset($profit['countNotOpt'])) {
                        $templates['countNotOpt'][$dateofadded] = $this->renderView('app/reports/ordersCount/_orders.html.twig', ['orders' => $profit['countNotOpt']]);

                    }

                    if (isset($profit['countOpt'])) {
                        $templates['countOpt'][$dateofadded] = $this->renderView('app/reports/ordersCount/_orders.html.twig', ['orders' => $profit['countOpt']]);
                    }

                    if (isset($profit['countDeleted'])) {
                        $templates['countDeleted'][$dateofadded] = $this->renderView('app/reports/ordersCount/_deletedNumbers.html.twig', [
                            'orders' => $profit['countDeleted'],
                            'managers' => $managersAll,
                            'deleteReasons' => $deleteReasons,
                            'creaters' => $creaters,
                        ]);
                    }
                }
                $managers = $managerFetcher->assocByIds($managers);

                $printCommand->data = json_encode(compact('profits', 'managers'));
            }
        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        return $this->render('app/reports/ordersCount/index.html.twig', [
            'templates' => $templates ?? [],
            'managers' => $managers ?? [],
            'profits' => $profits ?? null,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportOrdersCount');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $arr = json_decode($printCommand->data, true);
        $profits = $arr['profits'];
        $managers = $arr['managers'];

        return $this->render('app/reports/ordersCount/print.html.twig', [
            'managers' => $managers,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportOrdersCount');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $arr = json_decode($printCommand->data, true);
        $profits = $arr['profits'];
        $managers = $arr['managers'];

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

        $i = 1;
        foreach ($managers as $manager) {
            $aSheet->setCellValue(chr(65 + $i) . "1", mb_substr($manager, 0, 3) . ' офис');
            $aSheet->setCellValue(chr(65 + $i + 1) . "1", mb_substr($manager, 0, 3) . ' тел');
            $aSheet->setCellValue(chr(65 + $i + 2) . "1", mb_substr($manager, 0, 3) . ' сумма');
            $i += 3;
        }

        $aSheet->setCellValue(chr(65 + $i) . "1", 'Роз');
        $aSheet->setCellValue(chr(65 + $i + 1) . "1", 'Опт');
        $aSheet->setCellValue(chr(65 + $i + 2) . "1", 'Отказ');
        $aSheet->setCellValue(chr(65 + $i + 3) . "1", 'Детали отказ');

//        $aSheet->getStyle( chr(65 + $i) . $j . ':' . chr(66 + $i) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
//        $aSheet->setCellValue(chr(65 + $i) . $j . "", $profits[$createrID][$zapSkladID]['sum_income'] ?? 0);
//        $aSheet->setCellValue(chr(66 + $i) . $j . "", $profits[$createrID][$zapSkladID]['sum_card'] ?? 0);

        $j = 2;
        foreach ($profits as $dateofadded => $profit) {
            $aSheet->setCellValue("A" . $j . "", (new \DateTime($dateofadded))->format('d.m.Y'));
            $i = 1;
            foreach ($managers as $managerID => $manager) {
                $aSheet->getStyle(chr(65 + $i + 2) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue(chr(65 + $i) . $j, isset($profit['managers'][$managerID]) ? (isset($profit['managers'][$managerID]['reasons'][1]) ? count($profit['managers'][$managerID]['reasons'][1]) : 0) : 0);
                $aSheet->setCellValue(chr(65 + $i + 1) . $j, isset($profit['managers'][$managerID]) ? (isset($profit['managers'][$managerID]['reasons'][2]) ? count($profit['managers'][$managerID]['reasons'][2]) : 0) : 0);
                $aSheet->setCellValue(chr(65 + $i + 2) . $j, isset($profit['managers'][$managerID]) ? ($profit['managers'][$managerID]['sum'] ?? 0) : 0);
                $i += 3;
            }
            $aSheet->setCellValue(chr(65 + $i) . $j, count($profit['countNotOpt']));
            $aSheet->setCellValue(chr(65 + $i + 1) . $j, count($profit['countOpt']));
            $aSheet->setCellValue(chr(65 + $i + 2) . $j, count($profit['countDeleted']));

            $count = 0;
            foreach ($profit['countDeleted'] as $item) {
                $count += count($item);
            }
            $aSheet->setCellValue(chr(65 + $i + 3) . $j, $count);
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
}
