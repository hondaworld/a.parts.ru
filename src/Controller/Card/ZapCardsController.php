<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumberRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Weight\WeightRepository;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\Card\Create;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\AbcFetcher;
use App\ReadModel\Card\Filter;
use App\ReadModel\Card\ZapCardFetcher;
use App\ReadModel\Card\ZapCardHistoryFetcher;
use App\ReadModel\Document\DocumentTypeFetcher;
use App\ReadModel\Firm\FirmFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/parts", name="card.parts")
 */
class ZapCardsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ZapCardFetcher $fetcher
     * @param ManagerSettings $settings
     * @param OptRepository $optRepository
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardRepository $zapCardRepository
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, ZapCardFetcher $fetcher, ManagerSettings $settings, OptRepository $optRepository, ZapSkladFetcher $zapSkladFetcher, IncomeFetcher $incomeFetcher, ZapCardPriceService $zapCardPriceService, ZapCardRepository $zapCardRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCard');

        $settings = $settings->get('zapCards');

        $filter = new Filter\ZapCard\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ZapCard\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $opts = $optRepository->findAllOrdered();
        $sklads = $zapSkladFetcher->assoc();
        $zapCardsID = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as $item) {
                $zapCardsID[] = $item['zapCardID'];
            }
            $zapCards = $zapCardRepository->findByZapCards($zapCardsID);
            foreach ($items as &$item) {
                $optPrices = $zapCardPriceService->priceAllOpt($zapCards[$item['zapCardID']], $opts);
                $item['optPrices'] = $optPrices;
                $item['detail_name'] = $zapCards[$item['zapCardID']]->getDetailName();
//                $zapCardsID[] = $item['zapCardID'];
            }
            $pagination->setItems($items);
        }

        $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards($zapCardsID);

        return $this->render('app/card/parts/index.html.twig', [
            'pagination' => $pagination,
            'opts' => $opts,
            'sklads' => $sklads,
            'quantityInWarehouse' => $quantityInWarehouse,
            'filter' => $form->createView(),
            'table_sortable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ZapCard');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.parts');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/parts/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param ZapCard $zapCard
     * @param WeightRepository $weightRepository
     * @param AbcFetcher $abcFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     */
    public function show(ZapCard $zapCard, WeightRepository $weightRepository, AbcFetcher $abcFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');

        $weight = $weightRepository->findByZapCard($zapCard);

        $abc = $abcFetcher->all();

        $zapSklads = $zapSkladFetcher->assoc();

        return $this->render('app/card/parts/show.html.twig', [
            'zapCard' => $zapCard,
            'weight' => $weight,
            'abc' => $abc,
            'zapSklads' => $zapSklads,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/prices", name=".prices")
     * @param ZapCard $zapCard
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardStockNumberRepository $zapCardStockNumberRepository
     * @param PartPriceService $partPriceService
     * @return Response
     */
    public function prices(ZapCard $zapCard, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ZapCardStockNumberRepository $zapCardStockNumberRepository, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');

        $opts = $optRepository->findAllOrdered();

        $optPrices = $zapCardPriceService->priceAllOpt($zapCard, $opts);

        $profitsFromPriceGroup = $zapCardPriceService->profitsFromPriceGroupAllOpt($zapCard, $opts);
        $profitsFromZapCard = $zapCardPriceService->profitsFromZapCardAllOpt($zapCard, $opts);

        $stock = $zapCardStockNumberRepository->findFromNumberAndCreater($zapCard->getNumber(), $zapCard->getCreater(), false);

        try {
            $optimal = $partPriceService->getOptimalProviderPrice($zapCard);
        } catch (Exception $e) {
            $optimal = '';
        }
        $optimalName = $optimal ? $optimal['postavka'] : '';

        return $this->render('app/card/parts/prices.html.twig', [
            'zapCard' => $zapCard,
            'opts' => $opts,
            'optPrices' => $optPrices,
            'stock' => $stock,
            'optimalName' => $optimalName,
            'profitsFromPriceGroup' => $profitsFromPriceGroup,
            'profitsFromZapCard' => $profitsFromZapCard,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/abc", name=".abc")
     * @param int $id
     * @param Request $request
     * @param ZapCardRepository $repository
     * @param ZapSkladRepository $zapSkladRepository
     * @param ManagerRepository $managerRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function abc(int $id, Request $request, ZapCardRepository $repository, ZapSkladRepository $zapSkladRepository, ManagerRepository $managerRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $zapCard = $repository->get($id);

            $zapSklad = $zapSkladRepository->get($request->query->getInt('zapSkladID'));

            $abc = trim($request->query->get('abc'));
            $zapCard->updateAbc($zapSklad, $abc, $managerRepository->get($this->getUser()->getId()));

            $flusher->flush();
            $data['message'] = 'ABC изменено на ' . ($abc != '' ? $abc : 'пустое значение');
            $data['data'] = $zapCard->getAbcHistory($request->query->getInt('zapSkladID'));

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/history", name=".history")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ZapCardHistoryFetcher $fetcher
     * @param ManagerSettings $settings
     * @param DocumentTypeFetcher $documentTypeFetcher
     * @param FirmFetcher $firmFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @return Response
     * @throws Exception
     */
    public function history(ZapCard $zapCard, Request $request, ZapCardHistoryFetcher $fetcher, ManagerSettings $settings, DocumentTypeFetcher $documentTypeFetcher, FirmFetcher $firmFetcher, ZapSkladFetcher $zapSkladFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCard');

        $settings = $settings->get('zapCardHistory');

        $filter = new Filter\ZapCardHistory\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ZapCardHistory\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $zapCard,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $documentTypes = $documentTypeFetcher->unique();
        $firms = $firmFetcher->assoc();
        $sklads = $zapSkladFetcher->allSklads();
//        $incomeSklads = $incomeSkladFetcher->findByIncome($income);


        return $this->render('app/card/parts/history.html.twig', [
            'zapCard' => $zapCard,
            'documentTypes' => $documentTypes,
            'firms' => $firms,
            'sklads' => $sklads,
            'filter' => $form->createView(),
            'pagination' => $pagination
        ]);
    }
}
