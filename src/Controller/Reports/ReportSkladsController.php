<?php

namespace App\Controller\Reports;

use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Reports\Filter;
use App\ReadModel\Reports\PrintForm;
use App\ReadModel\Reports\ReportSkladFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
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
 * @Route("/reports/sklads", name="reports.sklads")
 */
class ReportSkladsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ReportSkladFetcher $fetcher
     * @param CreaterFetcher $createrFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function index(ReportSkladFetcher $fetcher, CreaterFetcher $createrFetcher, ProviderPriceFetcher $providerPriceFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportSklad');

        $printCommand = new PrintForm\Command();

        ini_set('max_execution_time', '900');

        $creaters = $createrFetcher->assoc();
        $providerPrices = $providerPriceFetcher->assoc();
        $sklads = $zapSkladFetcher->assoc();

        $sum = [];
        try {


            $profits = $fetcher->all();
            foreach ($profits as &$creater) {
                foreach ($creater as $zapSkladID => &$profit) {
                    if ($profit['sum_income'] > 0) {
                        $profit['template'] = $this->renderView('app/reports/sklads/_providerPrices.html.twig', [
                            'profits' => $profit['providerPrices'],
                            'providerPrices' => $providerPrices,
                            'sum_income' => $profit['sum_income']
                        ]);
                        $sum[$zapSkladID]['sum_income'] = ($sum[$zapSkladID]['sum_income'] ?? 0) + $profit['sum_income'];
                        $sum[$zapSkladID]['sum_card'] = ($sum[$zapSkladID]['sum_card'] ?? 0) + $profit['sum_card'];
                    }
                }
            }

            $printProfits = $profits;
            foreach ($printProfits as &$creater) {
                foreach ($creater as &$profit) {
                    unset($profit['providerPrices']);
                }
            }
            $printCommand->data = json_encode($printProfits);

        } catch (Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        return $this->render('app/reports/sklads/index.html.twig', [
            'creaters' => $creaters,
            'providerPrices' => $providerPrices,
            'sklads' => $sklads,
            'sum' => $sum,
            'profits' => $profits ?? null,
            'printForm' => $printForm->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function print(Request $request, CreaterFetcher $createrFetcher, ProviderPriceFetcher $providerPriceFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportSklad');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $creaters = $createrFetcher->assoc();
        $providerPrices = $providerPriceFetcher->assoc();
        $sklads = $zapSkladFetcher->assoc();
        $profits = json_decode($printCommand->data, true);

        return $this->render('app/reports/sklads/print.html.twig', [
            'creaters' => $creaters,
            'providerPrices' => $providerPrices,
            'sklads' => $sklads,
            'profits' => $profits,
        ]);
    }

    /**
     * @Route("/excel/", name=".excel")
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function excel(Request $request, CreaterFetcher $createrFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ReportSklad');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $creaters = $createrFetcher->assoc();
        $sklads = $zapSkladFetcher->assoc();
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

        $aSheet->setCellValue("A1", "Марка");
        $i = 1;
        foreach ($sklads as $sklad) {
            $aSheet->setCellValue(chr(65 + $i) . "1", $sklad . " из приходов");
            $aSheet->setCellValue(chr(66 + $i) . "1", $sklad . " из закупки");
            $i += 2;
        }

        $j = 2;
        foreach ($creaters as $createrID => $createrName) {
            if (isset($profits[$createrID])) {
                $aSheet->setCellValue("A" . $j . "", $createrName);
                $i = 1;
                foreach ($sklads as $zapSkladID => $skladName) {
                    $aSheet->getStyle( chr(65 + $i) . $j . ':' . chr(66 + $i) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    $aSheet->setCellValue(chr(65 + $i) . $j . "", $profits[$createrID][$zapSkladID]['sum_income'] ?? 0);
                    $aSheet->setCellValue(chr(66 + $i) . $j . "", $profits[$createrID][$zapSkladID]['sum_card'] ?? 0);
                    $i += 2;
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
