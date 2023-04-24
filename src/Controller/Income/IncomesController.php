<?php


namespace App\Controller\Income;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Card\UseCase\Card\Name;
use App\Model\Card\UseCase\Card\Country;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Income\UseCase\Income\Weight;
use App\Model\Income\UseCase\Income\Number;
use App\Model\Income\UseCase\Income\DateOfZakaz;
use App\Model\Income\UseCase\Income\DateOfIn;
use App\Model\Income\UseCase\Income\DateOfInPlan;
use App\Model\Income\UseCase\Income\Create;
use App\Model\Income\UseCase\Income\Quantity;
use App\Model\Income\UseCase\Income\QuantityChange;
use App\Model\Income\UseCase\Income\Label;
use App\Model\Income\Entity\Income\Income;
use App\Model\User\Entity\User\UserRepository;
use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Income\Filter;
use App\ReadModel\Income\PrintForm;
use App\ReadModel\Income\IncomeDocumentFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Income\IncomeOrderFetcher;
use App\ReadModel\Income\IncomeSkladFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Income\IncomeVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use App\Service\Price\PartPriceService;
use App\Service\WindowCoordsService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/income", name="income")
 */
class IncomesController extends AbstractController
{
    private array $cols = [
        'abc' => 'ABC',
        'manager' => 'Менеджер',
        'dateofadded' => 'Дата',
        'creater' => 'Бренд',
        'name' => 'Наименование',
        'number' => 'Номер',
        'location' => 'Ячейка',
        'providerPrice' => 'Регион',
        'country' => 'Страна',
        'gtd' => 'ГТД',
        'order' => 'Заказ',
        'weight' => 'Вес',
        'priceZak' => 'Закупка',
        'priceDost' => 'Доставка',
        'price' => 'Цена',
        'discountPrice' => 'Розница',
        'status' => 'Статус',
        'quantity' => 'Количество',
        'dateofzakaz' => 'Заказано',
        'dateofin' => 'Приход',
        'dateofinplan' => 'План',
        'incomeID' => '#',
//        'isDoc' => 'Пров.',
        'isUnpack' => 'Посч.',
        'incomeOrder' => 'Заказ',
        'incomeDocument' => 'ПН',
    ];

    /**
     * @Route("/", name="")
     * @param Request $request
     * @param IncomeFetcher $fetcher
     * @param ManagerSettings $settings
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @param OrderGoodFetcher $orderGoodFetcher
     * @param WeightFetcher $weightFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeSkladFetcher $incomeSkladFetcher
     * @param IncomeOrderFetcher $incomeOrderFetcher
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param PartPriceService $partPriceService
     * @param IncomeRepository $incomeRepository
     * @param UserRepository $userRepository
     * @param WindowCoordsService $windowCoordsService
     * @return Response
     */
    public function index(
        Request               $request,
        IncomeFetcher         $fetcher,
        ManagerSettings       $settings,
        ShopLocationFetcher   $shopLocationFetcher,
        ZapCardAbcFetcher     $zapCardAbcFetcher,
        OrderGoodFetcher      $orderGoodFetcher,
        WeightFetcher         $weightFetcher,
        ZapSkladFetcher       $zapSkladFetcher,
        IncomeSkladFetcher    $incomeSkladFetcher,
        IncomeOrderFetcher    $incomeOrderFetcher,
        IncomeDocumentFetcher $incomeDocumentFetcher,
        ProviderPriceFetcher  $providerPriceFetcher,
        PartPriceService      $partPriceService,
        IncomeRepository      $incomeRepository,
        UserRepository        $userRepository,
        WindowCoordsService   $windowCoordsService
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $settings = $settings->get('income');

        $printCommand = new PrintForm\Command();

        $filter = new Filter\Income\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Income\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $arWeights = [];
        $arIncomes = [];
        $arZapCards = [];
        $arSkladZapCards = [];
        $arIncomeOrders = [];
        $arIncomeDocuments = [];
        $arReturning = [];
        $sum = [
            'priceZak' => 0,
            'price' => 0,
            'weight' => 0,
            'priceDost' => 0,
        ];

        $providerPrices = $providerPriceFetcher->assoc();

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as &$item) {
                $arIncomes[] = $item['incomeID'];
                $arZapCards[] = $item['zapCardID'];
//                $item['locations'] = $shopLocationFetcher->assocByZapCardID($item['zapCardID']);
//                $item['abc'] = $zapCardAbcFetcher->assocByZapCardID($item['zapCardID']);

//                $orderGoods = $orderGoodFetcher->findByIncome($item['incomeID']);
//                if ($orderGoods) {
//                    $item['discountPrice'] = round($orderGoods[0]["price"] - $orderGoods[0]["price"] * $orderGoods[0]["discount"] / 100);
//                }

                if (!isset($arWeights[$item['createrID']][$item['number']])) {
                    $weight = $weightFetcher->allByNumberAndCreater($item['number'], $item['createrID']);
                    $arWeights[$item['createrID']][$item['number']] = $weight ? $weight[0] : null;
                }
                $item['weight'] = $arWeights[$item['createrID']][$item['number']];

                if ($item['orderID'] == 'Склад') {
                    $arSkladZapCards[] = $item['zapCardID'];
                }
                if ($item['orderUserID']) {
                    $item['userTown'] = ($userRepository->get($item['orderUserID']))->getMainContact() && ($userRepository->get($item['orderUserID']))->getMainContact()->getTown() ? ($userRepository->get($item['orderUserID']))->getMainContact()->getTown()->getName() : '';
                }
                if ($item['incomeOrderID']) $arIncomeOrders[] = $item['incomeOrderID'];
                if ($item['incomeDocumentID']) $arIncomeDocuments[] = $item['incomeDocumentID'];

                if ($item['quantity'] == $item['quantityUnPack']) {
                    $item['isUnpack'] = true;
                } else {
                    $item['isUnpack'] = false;
                }
                if ($item['status'] == 1) {
                    $item['providerPriceBetterPrice'] = $partPriceService->onePriceByIncomeBetterPrice($incomeRepository->get($item['incomeID']));
                }
                if ($item['status'] == IncomeStatus::IN_WAREHOUSE) {
                    $arReturning[] = $item['incomeID'];
                }
                $sum['priceZak'] += $item['priceZak'] * $item['quantity'];
                $sum['price'] += $item['price'] * $item['quantity'];
                $sum['priceDost'] += $item['priceDost'] * $item['quantity'];
                $sum['weight'] += ($item['weight'] ? $item['weight']['weight'] : 0) * $item['quantity'];
            }
            $pagination->setItems($items);

            $zakazano = $fetcher->assocOrderedByZapCards($arSkladZapCards);

            $incomeOrders = $incomeOrderFetcher->findByIds($arIncomeOrders);
            $incomeDocuments = $incomeDocumentFetcher->findByIds($arIncomeDocuments);

            $locations = $shopLocationFetcher->findByZapCards($arZapCards);
            $abcs = $zapCardAbcFetcher->findByZapCards($arZapCards);

            $discountPrices = $orderGoodFetcher->findPricesByIncomes($arIncomes);
        }

        $printCommand->data = json_encode($items ?? []);
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);

        $name = new Name\Command(0);
        $formName = $this->createForm(Name\Form::class, $name);

        $country = new Country\Command(0);
        $formCountry = $this->createForm(Country\Form::class, $country);

        $weightCommand = new Weight\Command();
        $formWeight = $this->createForm(Weight\Form::class, $weightCommand);

        $sklads = $zapSkladFetcher->assoc();
        $incomeSklads = $incomeSkladFetcher->findByIncomes($arIncomes);

        $dateOfZakazCommand = new DateOfZakaz\Command(0);
        $formDateOfZakaz = $this->createForm(DateOfZakaz\Form::class, $dateOfZakazCommand);

        $dateOfInCommand = new DateOfIn\Command(0);
        $formDateOfIn = $this->createForm(DateOfIn\Form::class, $dateOfInCommand);

        $dateOfInPlanCommand = new DateOfInPlan\Command(0);
        $formDateOfInPlan = $this->createForm(DateOfInPlan\Form::class, $dateOfInPlanCommand);

        $numberCommand = new Number\Command(0);
        $formNumber = $this->createForm(Number\Form::class, $numberCommand);

        $quantityCommand = new Quantity\Command(0);
        $formQuantity = $this->createForm(Quantity\Form::class, $quantityCommand);

        $quantityChangeCommand = new QuantityChange\Command(0);
        $formQuantityChange = $this->createForm(QuantityChange\Form::class, $quantityChangeCommand);

        $windowTop = $windowCoordsService->getTop();

        return $this->render('app/income/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'cols' => $this->cols,
            'hideCols' => $settings['hideCols'] ?? [],
            'table_checkable' => true,
            'formCountry' => $formCountry->createView(),
            'formName' => $formName->createView(),
            'formWeight' => $formWeight->createView(),
            'formDateOfZakaz' => $formDateOfZakaz->createView(),
            'formDateOfIn' => $formDateOfIn->createView(),
            'formDateOfInPlan' => $formDateOfInPlan->createView(),
            'formNumber' => $formNumber->createView(),
            'formQuantity' => $formQuantity->createView(),
            'formQuantityChange' => $formQuantityChange->createView(),
            'sklads' => $sklads,
            'incomeSklads' => $incomeSklads,
            'zakazano' => $zakazano ?? [],
            'incomeOrders' => $incomeOrders ?? [],
            'incomeDocuments' => $incomeDocuments ?? [],
            'locations' => $locations ?? [],
            'abcs' => $abcs ?? [],
            'discountPrices' => $discountPrices ?? [],
            'providerPrices' => $providerPrices,
            'sum' => $sum,
            'arReturning' => implode(',', $arReturning),
            'printForm' => $printForm->createView(),
            'windowTop' => $windowTop
        ]);
    }

    /**
     * @Route("/print/", name=".print")
     * @param Request $request
     * @param IncomeFetcher $fetcher
     * @param ManagerSettings $settings
     * @param ShopLocationFetcher $shopLocationFetcher
     * @param ZapCardAbcFetcher $zapCardAbcFetcher
     * @param OrderGoodFetcher $orderGoodFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeSkladFetcher $incomeSkladFetcher
     * @param IncomeOrderFetcher $incomeOrderFetcher
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     * @throws Exception
     */
    public function print(
        Request               $request,
        IncomeFetcher         $fetcher,
        ManagerSettings       $settings,
        ShopLocationFetcher   $shopLocationFetcher,
        ZapCardAbcFetcher     $zapCardAbcFetcher,
        OrderGoodFetcher      $orderGoodFetcher,
        ZapSkladFetcher       $zapSkladFetcher,
        IncomeSkladFetcher    $incomeSkladFetcher,
        IncomeOrderFetcher    $incomeOrderFetcher,
        IncomeDocumentFetcher $incomeDocumentFetcher,
        ProviderPriceFetcher  $providerPriceFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Income');

        $settings = $settings->get('income');

        $printCommand = new PrintForm\Command();
        $printForm = $this->createForm(PrintForm\Form::class, $printCommand);
        $printForm->handleRequest($request);

        $items = json_decode($printCommand->data, true);

        $arIncomes = [];
        $arZapCards = [];
        $arSkladZapCards = [];
        $arIncomeOrders = [];
        $arIncomeDocuments = [];
        $sum = [
            'priceZak' => 0,
            'price' => 0,
            'weight' => 0,
            'priceDost' => 0,
        ];

        $providerPrices = $providerPriceFetcher->assoc();

        foreach ($items as &$item) {
            $arIncomes[] = $item['incomeID'];
            $arZapCards[] = $item['zapCardID'];
            if ($item['incomeOrderID']) $arIncomeOrders[] = $item['incomeOrderID'];
            if ($item['incomeDocumentID']) $arIncomeDocuments[] = $item['incomeDocumentID'];

            $sum['priceZak'] += $item['priceZak'] * $item['quantity'];
            $sum['price'] += $item['price'] * $item['quantity'];
            $sum['priceDost'] += $item['priceDost'] * $item['quantity'];
            $sum['weight'] += ($item['weight'] ? $item['weight']['weight'] : 0) * $item['quantity'];
        }

        $zakazano = $fetcher->assocOrderedByZapCards($arSkladZapCards);

        $incomeOrders = $incomeOrderFetcher->findByIds($arIncomeOrders);
        $incomeDocuments = $incomeDocumentFetcher->findByIds($arIncomeDocuments);

        $locations = $shopLocationFetcher->findByZapCards($arZapCards);
        $abcs = $zapCardAbcFetcher->findByZapCards($arZapCards);

        $discountPrices = $orderGoodFetcher->findPricesByIncomes($arIncomes);

        $sklads = $zapSkladFetcher->assoc();
        $incomeSklads = $incomeSkladFetcher->findByIncomes($arIncomes);


        return $this->render('app/income/print.html.twig', [
            'pagination' => $items,
            'cols' => $this->cols,
            'hideCols' => $settings['hideCols'] ?? [],
            'sklads' => $sklads,
            'incomeSklads' => $incomeSklads,
            'zakazano' => $zakazano ?? [],
            'incomeOrders' => $incomeOrders ?? [],
            'incomeDocuments' => $incomeDocuments ?? [],
            'locations' => $locations ?? [],
            'abcs' => $abcs ?? [],
            'discountPrices' => $discountPrices ?? [],
            'providerPrices' => $providerPrices,
            'sum' => $sum,
        ]);
    }

    /**
     * @Route("/labels", name=".labels")
     * @param Request $request
     * @param IncomeFetcher $fetcher
     * @param ManagerSettings $settings
     * @param ShopLocationFetcher $shopLocationFetcher
     * @return Response
     */
    public function labels(
        Request             $request,
        IncomeFetcher       $fetcher,
        ManagerSettings     $settings,
        ShopLocationFetcher $shopLocationFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(IncomeVoter::INCOME_LABELS, 'Income');

        $settings = $settings->get('income');
        $settings['inPage'] = 500;
        $settings['sort'] = 'creater, number';
        $settings['direction'] = '';

        $filter = new Filter\Income\Filter();
//        $form = $this->createForm(Filter\Income\Form::class, $filter);
//        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            1,
            $settings
        );

        $arIncomes = [];
        $arZapCards = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as $item) {
                $arIncomes[$item['incomeID']] = [
                    'quantity' => $item['quantity'],
                    'isCheck' => !is_numeric($item['orderID']),
                ];
                $arZapCards[] = $item['zapCardID'];
            }
            $pagination->setItems($items);

            $locations = $shopLocationFetcher->findByZapCards($arZapCards);
        }

        $incomeCommand = new Label\Command($arIncomes);
        $form = $this->createForm(Label\Form::class, $incomeCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
//                $handler->handle($command);
                $incomes = $incomeCommand->incomes;
                return $this->render('app/income/label/print.html.twig', [
                    'incomes' => $incomes,
                    'pagination' => $pagination,
                    'locations' => $locations ?? [],
                ]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/income/label/index.html.twig', [
            'pagination' => $pagination,
            'form' => $form->createView(),
            'locations' => $locations ?? [],
        ]);
    }

    /**
     * @Route("/cols", name=".cols")
     * @param ManagerSettings $settings
     * @return Response
     */
    public function cols(ManagerSettings $settings): Response
    {
        $data = ['code' => 200, 'message' => ''];

        $settings->getCols('income', array_keys($this->cols));

        return $this->json($data);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Income');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('income', ['page' => $request->getSession()->get('page/income') ?: 1]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/income/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Income $income
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Income $income, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if ($income->getStatus()->getId() != IncomeStatus::DEFAULT_STATUS) {
                return $this->json(['code' => 500, 'message' => 'Приход уже не в статусе Заказать']);
            } else {
                $income->clearOrderGoods();
                $em->remove($income);
                $flusher->flush();
                $data['message'] = 'Приход удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param IncomeRepository $incomeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, IncomeRepository $incomeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $income = $incomeRepository->get($request->query->getInt('id'));
            if ($income->getStatus()->getId() != IncomeStatus::DEFAULT_STATUS) {
                return $this->json(['code' => 500, 'message' => 'Приход №' . $income->getId() . ' уже не в статусе Заказать']);
            } else {
                $income->clearOrderGoods();
                $em->remove($income);
                $flusher->flush();
                $data['action'] = 'delete';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/checkPrice", name=".checkPrice")
     * @param IncomeRepository $incomeRepository
     * @param PartPriceService $partPriceService
     * @param Flusher $flusher
     * @return Response
     * @throws Exception
     */
    public function checkPrice(IncomeRepository $incomeRepository, PartPriceService $partPriceService, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(IncomeVoter::INCOME_CHECK_PRICE, 'Income');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $incomes = $incomeRepository->findIncomeNew();
            foreach ($incomes as $income) {
                $prices = $partPriceService->onePrice($income->getZapCard()->getNumber(), $income->getZapCard()->getCreater(), $income->getProviderPrice());
                if ($prices['priceZak'] != 0) {
                    $income->updatePrices($prices['priceZak'], $prices['priceDostUsd'], $prices['priceWithDostRub']);
                }
            }
            $flusher->flush();
            $data['reload'] = true;
        } catch (DomainException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}