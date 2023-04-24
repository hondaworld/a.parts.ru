<?php


namespace App\Controller\Order;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Order\Service\DocumentService;
use App\Model\Order\Service\OrderGoodLocation;
use App\Model\Order\Service\OrderGoodService;
use App\Model\Order\Service\OrderPriceService;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\User\Entity\User\User;
use App\Model\Card\UseCase\Card\Name;
use App\ReadModel\Card\ZapCardReserveFetcher;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Expense\ExpenseFetcher;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\Income\IncomeDocumentFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Order\Filter;
use App\ReadModel\Order\OrderAlertFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\Model\Order\UseCase\Good\Price;
use App\Model\Order\UseCase\Good\Quantity;
use App\Model\Order\UseCase\Good\QuantityChange;
use App\Model\Order\UseCase\Good\Create;
use App\Model\Order\UseCase\Good\CreateCustom;
use App\Model\Order\UseCase\Good\CreateFile;
use App\Model\Order\UseCase\Good\Delete;
use App\Model\Order\UseCase\Good\Confirm;
use App\ReadModel\Order\SiteFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\Order\OrderVoter;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderGoodsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param WeightFetcher $weightFetcher
     * @param OrderGoodLocation $orderGoodLocation
     * @param ManagerSettings $settings
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param SchetFetcher $schetFetcher
     * @param OrderGoodService $orderGoodService
     * @param OrderPriceService $orderPriceService
     * @param OrderRepository $orderRepository
     * @param DocumentService $documentService
     * @param ZapCardReserveFetcher $zapCardReserveFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardRepository $zapCardRepository
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @param ExpenseFetcher $expenseFetcher
     * @param SiteFetcher $siteFetcher
     * @param OrderAlertFetcher $orderAlertFetcher
     * @return Response
     * @throws Exception
     */
    public function index(
        User                      $user,
        Request                   $request,
        OrderGoodFetcher          $fetcher,
        WeightFetcher             $weightFetcher,
        OrderGoodLocation         $orderGoodLocation,
        ManagerSettings           $settings,
        ExpenseDocumentRepository $expenseDocumentRepository,
        IncomeDocumentFetcher     $incomeDocumentFetcher,
        ExpenseDocumentFetcher    $expenseDocumentFetcher,
        SchetFetcher              $schetFetcher,
        OrderGoodService          $orderGoodService,
        OrderPriceService         $orderPriceService,
        OrderRepository           $orderRepository,
        DocumentService           $documentService,
        ZapCardReserveFetcher     $zapCardReserveFetcher,
        ZapSkladFetcher           $zapSkladFetcher,
        IncomeFetcher             $incomeFetcher,
        ZapCardRepository         $zapCardRepository,
        ExpenseSkladFetcher       $expenseSkladFetcher,
        ExpenseFetcher            $expenseFetcher,
        SiteFetcher               $siteFetcher,
        OrderAlertFetcher         $orderAlertFetcher
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $settings = $settings->get('orderGoods' . $user->getId());

        $filter = new Filter\Good\Filter($user->getId());
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Good\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $user->getId(),
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $arWeights = [];
        $arIncomeDocuments = [];
        $arExpenseDocuments = [];
        $arSchet = [];
        $arZapCards = [];
        $sumOrder = 0;
        $sumOrderProfit = 0;
        $isOrderPriceWrong = false;
        $arReturning = [];
        $arOrders = [];

        if ($pagination) {
            $items = $pagination->getItems();
            foreach ($items as &$item) {
                if (!isset($arWeights[$item['createrID']][$item['number']])) {
                    $arWeights[$item['createrID']][$item['number']] = $weightFetcher->oneByNumberAndCreater($item['number'], $item['createrID']);
                }
                $item['weight'] = $arWeights[$item['createrID']][$item['number']];
                $item['expenseSklads'] = $expenseSkladFetcher->getNotIncomeByOrderGood($item['goodID']);
                $item['isExpenseGood'] = $expenseFetcher->isExpenseByGoodID($item['goodID']);
                $item['location'] = $orderGoodLocation->get($item['incomeID'], $item['zapSkladID'], $item['providerPriceID'], $item['expenseSklads']);
                $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
                $item['reserve'] = $zapCardReserveFetcher->getOrderGoodReserve($item['goodID']);
                $item['alerts'] = $orderAlertFetcher->findByOrderGood($item['goodID']);
                $item['income'] = $incomeFetcher->getIncomeForOrderGood($item['incomeID']);
                $item['zapCard'] = $zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
                if ($item['zapCard']) $arZapCards[] = $item['zapCard']->getId();
                if (!in_array($item['orderID'], $arOrders)) $arOrders[] = $item['orderID'];

//                dump($item['income']);
//                dump($item['reserve']);

                if ($item['incomeDocumentID']) $arIncomeDocuments[] = $item['incomeDocumentID'];
                if ($item['expenseDocumentID']) $arExpenseDocuments[] = $item['expenseDocumentID'];
                if ($item['schetID']) $arSchet[] = $item['schetID'];
                $item['status'] = $orderGoodService->getStatus($item);
                $item['isDisabled'] = $orderGoodService->isDisabled($item);
                $item['isExpense'] = $orderGoodService->isExpense($item);
                $item['isPerem'] = $orderGoodService->isPerem($item);

                if ($item['expenseDocumentID'] && $item['isDeleted'] == 0) {
                    $arReturning[] = $item['goodID'];
                }

                $orderPrices = $orderPriceService->get($item);
                $sumOrder += $orderPrices['price'];
                $sumOrderProfit += $orderPrices['price'] - $orderPrices['priceZak'] ?: 0;
                $isOrderPriceWrong = $isOrderPriceWrong || $orderPrices['isPriceWrong'];

            }

            $orders = $orderRepository->findByIDs($arOrders);
            $incomeDocuments = $incomeDocumentFetcher->findByIds($arIncomeDocuments);
            $expenseDocuments = $expenseDocumentFetcher->findByIds($arExpenseDocuments);
            $schets = $schetFetcher->findByIds($arSchet);

            $quantityInWarehouse = $incomeFetcher->findQuantityInWarehouseByZapCards($arZapCards);
            $quantityIncomes = $incomeFetcher->findAllQuantitiesByZapCards($arZapCards);
            foreach ($items as &$item) {
                $item['styleClasses'] = $orderGoodService->getStyleClasses($item, $expenseDocuments);
                $item['document'] = $documentService->document($item, $expenseDocuments, $incomeDocuments);
                $item['schet'] = $documentService->schet($item, $schets);
                foreach ($orders as $order) {
                    if ($item['orderID'] == $order->getId()) {
                        $item['order'] = $order;
                    }
                }
            }
            $pagination->setItems($items);
        }


//        dump($pagination);

        $expensesSum = [
            'quantity' => 0,
            'sum' => 0
        ];
        $expenses = $fetcher->allExpenses($user->getId());
        foreach ($expenses as &$item) {
            if (!isset($arWeights[$item['createrID']][$item['number']])) {
                $arWeights[$item['createrID']][$item['number']] = $weightFetcher->oneByNumberAndCreater($item['number'], $item['createrID']);
            }
            $item['weight'] = $arWeights[$item['createrID']][$item['number']];

            $item['location'] = $orderGoodLocation->get($item['incomeID'], $item['zapSkladID'], $item['providerPriceID']);
            $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);

            $expensesSum['quantity'] += $item['quantity'];
            $expensesSum['sum'] += $item['priceDiscount'] * $item['quantity'];

            $item['expenses'] = $expenseFetcher->findByGoodID($item['goodID']);

            foreach ($item['expenses'] as $expense) {
                $item['zapCard'] = $zapCardRepository->get($expense['zapCardID']);
                $item['dateofexpense'] = $expense['dateofadded'];
                break;
            }
        }

//        dump($expenses);

        $priceCommand = new Price\Command(0);
        $formPrice = $this->createForm(Price\Form::class, $priceCommand);


        $name = new Name\Command(0);
        $formName = $this->createForm(Name\Form::class, $name);

        $quantityCommand = new Quantity\Command(0);
        $formQuantity = $this->createForm(Quantity\Form::class, $quantityCommand);

        $quantityChangeCommand = new QuantityChange\Command(0);
        $formQuantityChange = $this->createForm(QuantityChange\Form::class, $quantityChangeCommand);

        return $this->render('app/orders/goods/index.html.twig', [
            'table_checkable' => true,
            'expenseDocument' => $expenseDocument,
            'pagination' => $pagination,
            'expenses' => $expenses,
            'expensesSum' => $expensesSum,
            'user' => $user,
            'sklads' => $zapSkladFetcher->assoc(),
            'sites' => $siteFetcher->assoc(),
            'filter' => $form->createView(),
            'formPrice' => $formPrice->createView(),
            'formName' => $formName->createView(),
            'formQuantity' => $formQuantity->createView(),
            'formQuantityChange' => $formQuantityChange->createView(),
            'sumOrder' => $sumOrder,
            'sumOrderProfit' => $sumOrderProfit,
            'isOrderPriceWrong' => $isOrderPriceWrong,
            'quantityInWarehouse' => $quantityInWarehouse ?? [],
            'quantityIncomes' => $quantityIncomes ?? [],
            'arReturning' => implode(',', $arReturning),
            'newSchetData' => $schetFetcher->getSumGoodsNewSchetByUser($user->getId()),
        ]);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param OrderGood $orderGood
     * @param Request $request
     * @param Price\Handler $handler
     * @param ValidatorInterface $validator
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function price(OrderGood $orderGood, Request $request, Price\Handler $handler, ValidatorInterface $validator, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_CHANGE_PRICE, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = Price\Command::fromEntity($orderGood);
        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        $errors = $validator->validate($command);

        if (count($errors) == 0) {
            try {
                $handler->handle($command, $manager);

                $price = str_replace(',', '.', $command->price);
                $discount = str_replace(',', '.', $command->discount);

                $data['idIdentification'] = [
                    ['value' => number_format($price, 2, ',', ' '), 'name' => 'good_price_' . $orderGood->getId()],
                    ['value' => number_format($discount, 0, ',', ' '), 'name' => 'good_discount_' . $orderGood->getId()],
                    ['value' => number_format(round($price - $price * $discount / 100), 2, ',', ' '), 'name' => 'good_priceDiscount_' . $orderGood->getId()]
                ];

            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param User $user
     * @param OrderRepository $orderRepository
     * @return Response
     */
    public function create(User $user, OrderRepository $orderRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $order = $orderRepository->getWorking($user);

        $command = new Create\Command($order);
        $form = $this->createForm(Create\Form::class, $command);

        $commandCustom = new CreateCustom\Command();
        $formCustom = $this->createForm(CreateCustom\Form::class, $commandCustom);

        return $this->render('app/orders/goods/create/form.html.twig', [
            'form' => $form->createView(),
            'formCustom' => $formCustom->createView(),
            'user' => $user,
            'order' => $order
        ]);

    }

    /**
     * @Route("/{id}/create/search", name=".create.search")
     * @param User $user
     * @param Request $request
     * @param PartPriceService $partPriceService
     * @return Response
     * @throws Exception
     */
    public function createSearch(User $user, Request $request, PartPriceService $partPriceService): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        if ($request->query->get('number')) {
            $number = new DetailNumber($request->query->get('number'));
            $prices = $partPriceService->fullPriceForOrder($number, $user->getOpt());
        } else {
            $prices = [];
        }

        return $this->render('app/orders/goods/create/searchResult.html.twig', [
            'prices' => $prices,
        ]);
    }

    /**
     * @Route("/{id}/create/update", name=".create.update")
     * @param User $user
     * @param Request $request
     * @param ManagerRepository $managerRepository
     * @param Create\Handler $handler
     * @return Response
     */
    public function createUpdate(User $user, Request $request, ManagerRepository $managerRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
//                $data['reload'] = true;
                $data['redirectToUrl'] = $this->generateUrl('order.goods', ['id' => $user->getId(), 'add' => 1]);
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['messages'][] = $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/createFile", name=".createFile")
     * @param User $user
     * @param Request $request
     * @param CreateFile\HandlerData $handlerData
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param ProviderPriceRepository $providerPriceRepository
     * @return Response
     */
    public function createFile(User $user, Request $request, CreateFile\HandlerData $handlerData, ZapSkladFetcher $zapSkladFetcher, ProviderPriceRepository $providerPriceRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $command = CreateFile\Command::fromUser($user);
        $form = $this->createForm(CreateFile\Form::class, $command);
        $form->handleRequest($request);

        $data = [];

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();
                if ($file) {

                    $data = $handlerData->handle($command, $user, $file);

                } else {
                    throw new DomainException('Файл не выбран');
                }
//                return $this->redirectToRoute('order.goods.createFile', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }


        return $this->render('app/orders/goods/createFile/create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'data' => $data,
            'sklads' => $zapSkladFetcher->assoc(),
            'zapSklad' => $command->zapSkladID ? $zapSkladFetcher->get($command->zapSkladID) : null,
            'providerPrice' => $command->providerPriceID ? $providerPriceRepository->get($command->providerPriceID) : null,
        ]);

    }

    /**
     * @Route("/{id}/insertFile", name=".insertFile")
     * @param User $user
     * @param Request $request
     * @param CreateFile\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function insertFile(User $user, Request $request, CreateFile\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $handler->handle($request, $user, $manager);
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

//        return $this->render('app/home.html.twig');
        return $this->redirectToRoute('order.goods', ['id' => $user->getId()]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param OrderGood $orderGood
     * @return Response
     */
    public function delete(OrderGood $orderGood): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_DELETE, 'Order');

        $command = new Delete\Command();
        $form = $this->createForm(Delete\Form::class, $command);

        return $this->render('app/orders/goods/delete/form.html.twig', [
            'form' => $form->createView(),
            'orderGood' => $orderGood
        ]);

    }

    /**
     * @Route("/{id}/delete/action", name=".delete.action")
     * @param OrderGood $orderGood
     * @param Request $request
     * @param Delete\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function deleteAction(OrderGood $orderGood, Request $request, Delete\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_DELETE, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Delete\Command();
        $form = $this->createForm(Delete\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $orderGood, $manager);
                $data['reload'] = true;
            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($form->getErrors(true) as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/confirm", name=".confirm")
     * @param User $user
     * @param Request $request
     * @param Confirm\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function confirm(User $user, Request $request, Confirm\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CONFIRM, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Confirm\Command();
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $messages = $handler->handle($command, $manager, $user);

            $data['messages'] = $messages;
            $data['unChecked'] = true;

//            foreach ($messages as $message) {
//                $this->addFlash($message['type'], $message['message']);
//            }

//            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }
}