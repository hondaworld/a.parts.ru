<?php

namespace App\Controller\Analytics;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\ReadModel\Analytics\Filter;
use App\ReadModel\Analytics\AnalyticsPriceRegionFetcher;
use App\ReadModel\Analytics\UseCase\PriceRegion\Handler;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/analytics/priceRegions", name="analytics.priceRegions")
 */
class AnalyticsPriceRegionsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param AnalyticsPriceRegionFetcher $fetcher
     * @param PartPriceService $partPriceService
     * @param IncomeFetcher $incomeFetcher
     * @param CreaterRepository $createrRepository
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, AnalyticsPriceRegionFetcher $fetcher, PartPriceService $partPriceService, IncomeFetcher $incomeFetcher, CreaterRepository $createrRepository, ProviderPriceFetcher $providerPriceFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsPriceRegion');

        $filter = new Filter\PriceRegion\Filter();
        $form = $this->createForm(Filter\PriceRegion\Form::class, $filter);
        $form->handleRequest($request);

        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $providerPrices = $providerPriceFetcher->assocDescriptions();
        $arr = $fetcher->all($filter);

        $all = [];
        foreach ($arr as &$item) {
            $betterPrice = $partPriceService->onePriceByNumberAndCreaterIDBetterPriceWithClear(new DetailNumber($item['number']), $createrRepository->get($item['createrID']), [86, 88, 199, 197, 80, 209]);

            if (
                $betterPrice &&
                $betterPrice["providerPriceID"] != $item["currency_providerPriceID"] &&
                $betterPrice["priceWithDostRub"] * 1.03 < $item["price"]) {

                $item["providerPriceID_new"] = $betterPrice["providerPriceID"];
                $item["providerPriceName_new"] = $betterPrice["name"];
                $item["providerPricePrice_new"] = $betterPrice["priceWithDostRub"];
                $arLastPrice = $incomeFetcher->getLastIncomeInByZapCardID($item['zapCardID']);
                if ($arLastPrice) $item["realPrice"] = $arLastPrice["price"];
                $all[] = $item;
            }
        }

        return $this->render('app/analytics/priceRegions/index.html.twig', [
            'all' => $all,
            'providerPrices' => $providerPrices,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/providerPrices", name=".providerPrices")
     * @param ZapCard $zapCard
     * @param PartPriceService $partPriceService
     * @return Response
     * @throws Exception
     */
    public function providerPrices(ZapCard $zapCard, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AnalyticsPriceRegion');

        $prices = $partPriceService->fullPriceByNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), [86, 88, 199, 197, 80, 209]);

        return $this->render('app/analytics/priceRegions/providerPrices.html.twig', [
            'zapCard' => $zapCard,
            'prices' => $prices,
        ]);
    }

    /**
     * @Route("/{id}/providerPrice/update", name=".providerPrice.update")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ProviderPriceRepository $providerPriceRepository
     * @param Handler $handler
     * @return Response
     */
    public function providerPriceUpdate(ZapCard $zapCard, Request $request, ProviderPriceRepository $providerPriceRepository, Handler $handler): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'AnalyticsPriceRegion');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];
        try {
            $providerPrice = $providerPriceRepository->get($request->query->getInt('providerPriceID'));
            $arr = $handler->handle($providerPrice, $zapCard);

            $data['idIdentification'] = [
                ['value' => $arr['providerPrice'], 'name' => 'providerPrice_' . $zapCard->getId()],
                ['value' => number_format($arr['price'], 2, ',', ' '), 'name' => 'price_' . $zapCard->getId()]
            ];

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }


        return $this->json($data);
    }
}
