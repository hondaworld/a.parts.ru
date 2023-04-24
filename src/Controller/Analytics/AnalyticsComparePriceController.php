<?php

namespace App\Controller\Analytics;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Analytics\Filter;
use App\ReadModel\Detail\PartPriceFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\ReadModel\Analytics\UseCase\ComparePrice;
use App\Model\Card\UseCase\Card\ProfitZapCardFromComparePrice;
use App\Service\CsvUploadHelper;
use App\Service\Detail\CreaterService;
use App\Service\Price\PartPriceService;
use DomainException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/analytics/comparePrice", name="analytics.comparePrice")
 */
class AnalyticsComparePriceController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsComparePrice');

        $command = new ComparePrice\Command();
        $form = $this->createForm(ComparePrice\Form::class, $command);

        return $this->render('app/analytics/comparePrice/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param CsvUploadHelper $csvUploadHelper
     * @param CreaterService $createrService
     * @param ZapCardRepository $zapCardRepository
     * @param PartPriceService $partPriceService
     * @param PartPriceFetcher $partPriceFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function print(Request $request, CsvUploadHelper $csvUploadHelper, CreaterService $createrService, ZapCardRepository $zapCardRepository, PartPriceService $partPriceService, PartPriceFetcher $partPriceFetcher, ProviderPriceFetcher $providerPriceFetcher, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsComparePrice');

        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');

        $command = new ComparePrice\Command();
        $form = $this->createForm(ComparePrice\Form::class, $command);
        $form->handleRequest($request);

        $arZapCards = [];
        $tableNames = [];

        $providerPrices = [];
        foreach ($providerPriceFetcher->assocDescriptions() as $providerPriceID => $name) {
            if (in_array($providerPriceID, $command->providerPriceID)) {
                $providerPrices[$providerPriceID] = $name;
            }
        }

        $opts = $optRepository->findAllOrdered();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $DataFile = fopen($file->getPathname(), "r");
                    while (!feof($DataFile)) {
                        $line = $csvUploadHelper->getCsvLine($DataFile);
                        if ($line) {
                            $creater = $createrService->findCreaterFromCsv($line[0]);
                            $number = (new DetailNumber($csvUploadHelper->convertText(trim($line[1]))))->getValue();
                            $price = str_replace(',', '.', trim($line[2]));

                            if ($creater && $number != '' && is_numeric($price) && $price > 0) {
                                $createrID = $creater['createrID'];
                                $arZapCards[$createrID][$number]["creater_name"] = $creater['name'];
                                $tableNames[$createrID] = $creater['isOriginal'] == 1 ? $creater['tableName'] : 'shopPriceN';
                                $arZapCards[$createrID][$number]["price"] = $price;
                                $arZapCards[$createrID][$number]["pricePercent"] = round($price * (1 + $command->profit / 100), 2);
                                if (!isset($arZapCards[$createrID][$number]['zapCardID'])) {
                                    $zapCard = $zapCardRepository->getByNumberAndCreaterID($number, $createrID);
                                    if ($zapCard) {

                                        if (!$zapCard->isShowProfitForm($command->days)) {
                                            unset($arZapCards[$createrID][$number]);
                                        } else {
                                            $arZapCards[$createrID][$number]['profitsFromZapCard'] = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

                                            $arZapCards[$createrID][$number]['zapCardID'] = $zapCard->getId();
                                            $arZapCards[$createrID][$number]['priceZak'] = $zapCard->getPrice();
                                            if ($arZapCards[$createrID][$number]['priceZak'] == 0)
                                                $arZapCards[$createrID][$number]['percentZak'] = 0;
                                            else
                                                $arZapCards[$createrID][$number]['percentZak'] = round(($arZapCards[$createrID][$number]["pricePercent"] - $arZapCards[$createrID][$number]["priceZak"]) * 100 / $arZapCards[$createrID][$number]["priceZak"], 2);
                                        }
                                    } else {
                                        $arZapCards[$createrID][$number]['zapCardID'] = null;
                                    }
                                }
                            }

                        }
                    }
                }

                if ($command->providerPriceID) {
                    foreach ($arZapCards as $createrID => &$numbers) {
                        if (!isset($arrPriceZak[$tableNames[$createrID]])) {
                            $arrPriceZak[$tableNames[$createrID]] = $partPriceFetcher->priceZakForProviderPrices($tableNames[$createrID], $command->providerPriceID);
                        }
                        foreach ($numbers as $number => &$item) {
                            if (isset($arrPriceZak[$tableNames[$createrID]][$createrID][$number])) {
                                foreach ($arrPriceZak[$tableNames[$createrID]][$createrID][$number] as $providerPriceID => $price) {
                                    $partPriceService->addWeight($number, $createrID);
                                    $item["providers"][$providerPriceID]["price"] = $partPriceService->getPriceZakWithDeliveryRub($number, $createrID, $providerPriceID, $price);
                                    $item["providers"][$providerPriceID]["percentPrice"] = round(($item["pricePercent"] - $item["providers"][$providerPriceID]["price"]) * 100 / $item["providers"][$providerPriceID]["price"], 2);
                                }

                            }
                        }
                    }
                }
//                return $this->redirectToRoute('zamena', ['page' => $request->getSession()->get('page/shopZamena') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }

            if ($command->isExcel) {
                return $this->excel($arZapCards, $providerPrices);
            }
        }

        return $this->render('app/analytics/comparePrice/print.html.twig', [
            'arZapCards' => $arZapCards,
            'providerPrices' => $providerPrices,
            'opts' => $opts
        ]);
    }

    /**
     * @param array $arZapCards
     * @param array $providerPrices
     * @return Response
     */
    private function excel(array $arZapCards, array $providerPrices): Response
    {
        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

        $aSheet->setCellValue("A1", "Производитель");
        $aSheet->setCellValue("B1", "Номер");
        $aSheet->setCellValue("C1", "Цена");

        $charNum = ord('D');

        foreach ($providerPrices as $providerPrice) {
            $aSheet->setCellValue(chr($charNum++) . "1", $providerPrice . " цена");
            $aSheet->setCellValue(chr($charNum++) . "1", $providerPrice . " %");
        }

        $j = 2;
        foreach ($arZapCards as $createrID => $numbers) {
            foreach ($numbers as $number => $item) {
                $aSheet->setCellValue("A" . $j, $item['creater_name']);
                $aSheet->getStyle('B' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $aSheet->setCellValue("B" . $j, $number);
                $aSheet->getStyle('C' . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                $aSheet->setCellValue("C" . $j, $item['price']);

                $charNum = ord('D');

                foreach ($providerPrices as $providerPriceID => $providerPrice) {
                    if (!empty($item['providers'][$providerPriceID])) {
                        $aSheet->getStyle(chr($charNum) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                        $aSheet->setCellValue(chr($charNum++) . $j, $item['providers'][$providerPriceID]['price']);
                        $aSheet->getStyle(chr($charNum) . $j)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                        $aSheet->setCellValue(chr($charNum++) . $j, $item['providers'][$providerPriceID]['percentPrice']);
                    } else {
                        $aSheet->setCellValue(chr($charNum++) . $j, "");
                        $aSheet->setCellValue(chr($charNum++) . $j, "");
                    }
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

    /**
     * @Route("/profitZapCard", name=".profitZapCard")
     * @param Request $request
     * @param ProfitZapCardFromComparePrice\Handler $handler
     * @param OptRepository $optRepository
     * @return Response
     */
    public function profitZapCard(Request $request, ProfitZapCardFromComparePrice\Handler $handler, OptRepository $optRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsComparePrice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $opts = $optRepository->findAllOrdered();
            $zapCardID = $request->request->getInt('zapCardID');

            $command = ProfitZapCardFromComparePrice\Command::fromEntity(
                $zapCardID,
                $opts,
                $request->request->get('profits')
            );

            $handler->handle($command);

            $data['func'] = 'profitsDone';
            $data['params'] = $zapCardID;
        } catch (DomainException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);

    }
}
