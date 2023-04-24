<?php

namespace App\Controller\Reseller;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\ReadModel\Reseller\UseCase\Zzap\ComparePrice;
use App\ReadModel\Reseller\Filter;
use App\Service\CsvUploadHelper;
use App\Service\Detail\CreaterService;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/resellers/zzap/comparePrice", name="resellers.zzap.comparePrice")
 */
class ZzapComparePriceController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @return Response
     */
    public function index(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZzapComparePrice');

        $command = new ComparePrice\Command();
        $form = $this->createForm(ComparePrice\Form::class, $command);

        return $this->render('app/reseller/zzap/comparePrice/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list/", name=".list")
     * @param Request $request
     * @param CsvUploadHelper $csvUploadHelper
     * @param CreaterService $createrService
     * @param ZapCardRepository $zapCardRepository
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @return Response
     */
    public function list(Request $request, CsvUploadHelper $csvUploadHelper, CreaterService $createrService, ZapCardRepository $zapCardRepository, ZapCardAbcFetcher $zapCardAbcFetcher, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZzapComparePrice');

        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');

        $command = new ComparePrice\Command();
        $form = $this->createForm(ComparePrice\Form::class, $command);
        $form->handleRequest($request);


        $filter = new Filter\ZzapComparePrice\Filter();
        $formFilter = $this->createForm(Filter\ZzapComparePrice\Form::class, $filter);
        $formFilter->handleRequest($request);

        $lines = [];
        $lineHeaders = [];

        $zapCardsID = [];

        $opts = $optRepository->findAllOrdered();
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {
                    $DataFile = fopen($file->getPathname(), "r");
                    while (!feof($DataFile)) {
                        $line = $csvUploadHelper->getCsvLine($DataFile);
                        if ($line) {
                            if (!$lineHeaders) {
                                foreach ($line as $item) {
                                    if ($item != '') {
                                        $lineHeaders[] = $csvUploadHelper->convertText($item);
                                    }
                                }
                            }
                            $creater = $createrService->findCreaterFromCsv($line[0]);
                            $number = (new DetailNumber($csvUploadHelper->convertText(trim($line[1]))))->getValue();
                            $price = str_replace(',', '.', trim($line[5]));
                            $name = $csvUploadHelper->convertText(trim($line[2]));
                            if ($creater && $number != '' && is_numeric($price) && $price > 0) {
                                $createrID = $creater['createrID'];
                                $zapCard = $zapCardRepository->getByNumberAndCreaterID($number, $createrID);
                                if ($zapCard) {
                                    $zapCardsID[] = $zapCard->getId();
                                    foreach ($line as &$item) {
                                        $item = $csvUploadHelper->convertText($item);
                                    }
                                    $prices = [];
                                    foreach ($opts as $opt) {
                                        $optPrice = $zapCardPriceService->priceOpt($zapCard, $opt);
                                        $prices['optPrice' . $opt->getId()] = $optPrice;
                                        $prices['priceGroup'] = $zapCard->getPriceGroup() ? $zapCard->getPriceGroup()->getName() : '';
                                    }

                                    $lines[] = [
                                        'creater_name' => $creater['name'],
                                        'number' => $number,
                                        'name' => $name,
                                        'price' => $price,
                                        'prices' => $prices,
                                        'zapCard' => $zapCard,
                                        'line' => $line
                                    ];
                                }
                            }
                        }
                    }
                }

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $abc = $zapCardAbcFetcher->findByZapCards($zapCardsID);

        foreach ($lines as &$line) {
            $line['abc'] = $abc[$line['zapCard']->getId()] ?? [];
        }

        return $this->render('app/reseller/zzap/comparePrice/list.html.twig', [
            'lines' => $lines,
            'lineHeaders' => $lineHeaders,
            'filter' => $formFilter->createView(),
            'abc' => $abc,
            'opts' => $opts
        ]);
    }
}
