<?php

namespace App\Controller\Sklad;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Flusher;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Sklad\ZapCardPriceFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Sklad\SkladVoter;
use App\Model\Card\UseCase\Card\ProfitZapCard;
use App\Service\ManagerSettings;
use App\ReadModel\Sklad\Filter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/sklads/prices", name="sklads.prices")
 */
class ZapSkladPartPricesController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapSklad $zapSklad
     * @param Request $request
     * @param ZapCardPriceFetcher $fetcher
     * @param ManagerSettings $settings
     * @param OptRepository $optRepository
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapCardRepository $zapCardRepository
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @return Response
     * @throws Exception
     */
    public function index(
        ZapSklad            $zapSklad,
        Request             $request,
        ZapCardPriceFetcher $fetcher,
        ManagerSettings     $settings,
        OptRepository       $optRepository,
        ZapSkladFetcher     $zapSkladFetcher,
        IncomeFetcher       $incomeFetcher,
        ZapCardPriceService $zapCardPriceService,
        ZapCardRepository   $zapCardRepository,
        ExpenseSkladFetcher $expenseSkladFetcher,
        ZapCardAbcFetcher   $zapCardAbcFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PART_PRICES, 'ZapSklad');

        $settings = $settings->get('skladZapCardPrices');

        $filter = new Filter\ZapCardPrice\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;
        $filter->zapSkladID = $zapSklad->getId();

        $form = $this->createForm(Filter\ZapCardPrice\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $zapSklad,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

//        $opt = $optRepository->get(Opt::DEFAULT_OPT_ID);
        $sklads = $zapSkladFetcher->assoc();
        $opts = $optRepository->findAllOrdered();
        $zapCardsID = [];

        $items = $pagination->getItems();
        foreach ($items as &$item) {
            $zapCardsID[] = $item['zapCardID'];
        }
        $zapCards = $zapCardRepository->findByZapCards($zapCardsID);
        foreach ($items as &$item) {
            foreach ($opts as $opt) {
                $optPrice = $zapCardPriceService->priceOpt($zapCards[$item['zapCardID']], $opt);
                $item['optPrice' . $opt->getId()] = $optPrice;
                $item['priceGroup'] = $zapCards[$item['zapCardID']]->getPriceGroup() ? $zapCards[$item['zapCardID']]->getPriceGroup()->getName() : '';
            }
        }
        $pagination->setItems($items);

        $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards($zapCardsID);
        $quantityIncome = $incomeFetcher->findQuantityOrderedByZapCards($zapCardsID);
        $quantityFrom = $expenseSkladFetcher->findQuantityOrderedFromSkladsByZapCards($zapCardsID);
        $quantityTo = $expenseSkladFetcher->findQuantityOrderedToSkladsByZapCards($zapCardsID);
        $abc = $zapCardAbcFetcher->assocByZapCardsAndZapSklad($zapCardsID, $zapSklad->getId());

        return $this->render('app/sklads/prices/index.html.twig', [
            'pagination' => $pagination,
            'zapSklad' => $zapSklad,
            'sklads' => $sklads,
            'opts' => $opts,
            'quantityInWarehouse' => $quantityInWarehouse,
            'quantityIncome' => $quantityIncome,
            'quantityFrom' => $quantityFrom,
            'quantityTo' => $quantityTo,
            'abc' => $abc,
            'filter' => $form->createView(),
            'table_sortable' => true,
        ]);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param OptRepository $optRepository
     * @param ZapCardPriceService $zapCardPriceService
     * @param ProfitZapCard\Handler $handler
     * @param ValidatorInterface $validator
     * @param Flusher $flusher
     * @return Response
     */
    public function price(ZapCard $zapCard, Request $request, OptRepository $optRepository, ZapCardPriceService $zapCardPriceService, ProfitZapCard\Handler $handler, ValidatorInterface $validator, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(SkladVoter::SKLAD_PART_PRICES, 'ZapSklad');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => 'ok'];

        $opts = $optRepository->findAllOrdered();

        try {
            $command = ProfitZapCard\Command::fromEntity($zapCard, $opts, []);
            $data['valuesIdentification'] = [];

            foreach ($opts as $opt) {
                $value = str_replace(',', '.', $request->request->get('optPrice' . $opt->getId()));
                $profit = floor(($value / $zapCard->getPrice() - 1) * 100 * 100) / 100;
                $profitName = $command->getProfit($opt->getId());
                $command->$profitName = $profit;
            }

            $errors = $validator->validate($command);
            if (count($errors) == 0) {
                $handler->handle($command);

                if ($request->request->get('isClearPriceGroup') && $request->request->get('isClearPriceGroup') == 1) {
                    $zapCard->updatePriceGroup(null, false);
                    $flusher->flush();
                }

                foreach ($opts as $opt) {
                    $data['valuesIdentification'][] = ['id' => 'optPrice' . $opt->getId() . '_' . $zapCard->getId(), 'value' => $zapCardPriceService->priceOpt($zapCard, $opt)];
                }
                $data['message'] = 'Данные сохранены';
            } else {
                $data['code'] = 404;
                foreach ($errors as $error) {
                    $data['message'] .= $error->getMessage() . ' ';
                }
            }

        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }
}