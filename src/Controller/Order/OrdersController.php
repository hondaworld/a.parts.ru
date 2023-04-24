<?php


namespace App\Controller\Order;


use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Order\Order;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Order\Service\DocumentService;
use App\Model\Order\Service\OrderGoodLocation;
use App\Model\Order\Service\OrderGoodService;
use App\Model\Order\Service\OrderPriceService;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\Order\UseCase\Order\Create;
use App\Model\Order\UseCase\Order\CreateSearch;
use App\ReadModel\Card\ZapCardReserveFetcher;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\Expense\ExpenseFetcher;
use App\ReadModel\Expense\ExpenseSkladFetcher;
use App\ReadModel\Firm\SchetFetcher;
use App\ReadModel\Income\IncomeDocumentFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Order\Filter;
use App\ReadModel\Order\OrderAlertTypeFetcher;
use App\ReadModel\Order\OrderFetcher;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\Order\OrderListFetcher;
use App\ReadModel\Reports\ReportRegionProfitFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/orders", name="orders")
 */
class OrdersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param OrderFetcher $fetcher
     * @param UserRepository $userRepository
     * @param OrderAlertTypeFetcher $orderAlertTypeFetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, OrderFetcher $fetcher, UserRepository $userRepository, OrderAlertTypeFetcher $orderAlertTypeFetcher, ExpenseDocumentFetcher $expenseDocumentFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Order');

        $settings = $settings->get('orders');

        $filter = new Filter\Order\Filter();

        $form = $this->createForm(Filter\Order\Form::class, $filter);
        $form->handleRequest($request);

        $param = $filter->param;
        $orders = [];

        $alertTypes = $orderAlertTypeFetcher->assoc();

        if ($filter->orderID) {
            $orders = $fetcher->getByOrder($filter->orderID);
            $param = 'findByOrder';
        } elseif ($filter->user) {
            $users = $userRepository->findByValue($filter->user);
            foreach ($users as $userID => $user) {
                $orders[$userID]['user'] = $user;
                $lastOrdrer = $user->getLastOrder();
                if ($lastOrdrer) {
                    $orders[$userID]['dateofadded'] = $lastOrdrer->getDateofadded();
                }
            }
            $param = 'findByUser';
        } else {
            switch ($param) {
                case 'newByUser':
                    $orders = $fetcher->getNewOrders($filter, $settings, 1);
                    break;
                case 'newByManager':
                    $orders = $fetcher->getNewOrders($filter, $settings, 0);
                    break;
                case 'newByCron':
                    $orders = $fetcher->getNewOrders($filter, $settings, 2);
                    break;
                case 'not_sent':
                    $orders = $fetcher->getNotSent($filter, $settings);
                    break;
                case 'in_work':
                    $orders = $fetcher->getInWork($filter, $settings);
                    break;
                case 'not_paid':
                    $orders = $fetcher->getNotPaid($filter, $settings);
                    break;
                case 'picking':
                    $orders = $fetcher->getPick($filter, $settings, 1);
                    break;
                case 'picked':
                    $orders = $fetcher->getPick($filter, $settings, 2);
                    break;
                case 'expired':
                    $orders = $fetcher->getExpired($filter, $settings);
                    break;
                case 'paid_credit_card':
                    $orders = $fetcher->getPaidCreditCard();
                    break;
                case 'not_ordered':
                    $orders = $fetcher->getNotOrdered();
                    break;
                case 'reseller':
                    $orders = $fetcher->getWithResellers($filter, $settings);
                    break;
                default:
                    if ($filter->number) {
                        $orders = $fetcher->getByFilter($filter, $settings);
                    } else {
                        $orders = null;
                    }
            }

            foreach (array_keys($alertTypes) as $typeID) {
                if ($param == 'alert_' . $typeID) {
                    $orders = $fetcher->getAlerts($filter, $settings, $typeID);
                }
            }
        }

        if ($orders && !$filter->user) {
            $users = $userRepository->findByUsers(array_keys($orders));
            foreach ($orders as $userID => &$order) {
                $order['user'] = $users[$userID] ?? null;
            }
        }

        if (in_array($param, ['service', 'delivery'])) {
            $orders = [];
            switch ($param) {
                case 'service':
                    $users = $userRepository->findWithService();
                    $isShippings = $expenseDocumentFetcher->assocIsShippingByUsers(array_keys($users));
                    foreach ($users as $userID => $user) {
                        $orders[$userID] = [
                            'dateofadded' => $user->getDateofservice(),
                            'isShipping' => $isShippings[$userID] ?? 0,
                            'user' => $user
                        ];
                    }
                    break;
                case 'delivery':
                    $users = $userRepository->findWithDelivery();
                    foreach ($users as $userID => $user) {
                        $orders[$userID] = [
                            'dateofadded' => $user->getDateofdelivery(),
                            'user' => $user
                        ];
                    }
                    break;
            }
        }

        return $this->render('app/orders/index.html.twig', [
            'orders' => $orders ?? null,
            'alertTypes' => $alertTypes,
            'filter' => $form->createView(),
            'param' => $param
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @return Response
     */
    public function create(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $command = new CreateSearch\Command();
        $form = $this->createForm(CreateSearch\Form::class, $command);

        return $this->render('app/orders/create_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Order $order
     * @param Request $request
     * @param OrderRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Order $order, Request $request, OrderRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_NEW_DELETE, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        if ($order->getStatus() != Order::ORDER_STATUS_NEW) {
            return $this->json(['code' => 500, 'message' => 'Статус заказа должен быть "Новый"']);
        } else {
            try {
                $em->remove($order);
                $flusher->flush();
                $data['message'] = 'Заказ удален';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/activate", name=".activate")
     * @param Order $order
     * @param Flusher $flusher
     * @return Response
     */
    public function activate(Order $order, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $order->activate();
        $flusher->flush();

        return $this->redirectToRoute('order.goods', ['id' => $order->getUser()->getId()]);
    }

    /**
     * @Route("/create/search", name=".create.search")
     * @param Request $request
     * @param UserRepository $userRepository
     * @return Response
     */
    public function createSearch(Request $request, UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        if ($request->query->get('phonemob')) {
            $phonemob = $request->query->get('phonemob');
            $user = $userRepository->findByPhoneMobile($phonemob);
            if ($user) {
                $command = Create\Command::fromUser($user);
            } else {
                $command = new Create\Command($phonemob);
            }
            $form = $this->createForm(Create\Form::class, $command);
        }

        return $this->render('app/orders/create_form_search.html.twig', [
            'form' => isset($form) ? $form->createView() : null,
            'user' => $user ?? null,
        ]);
    }

    /**
     * @Route("/create/action", name=".create.action")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function createAction(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Create\Command('');
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $user = $handler->handle($command);
                $data['redirectToUrl'] = $this->generateUrl('order.goods', ['id' => $user->getId()]);
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
     * @Route("/show/{id}/", name=".show")
     * @param User $user
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function show(User $user, ExpenseDocumentRepository $expenseDocumentRepository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        return $this->render('app/orders/show.html.twig', [
            'user' => $user,
            'expenseDocument' => $expenseDocument,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/view/{id}/", name=".view")
     * @param Order $order
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param WeightFetcher $weightFetcher
     * @param ExpenseSkladFetcher $expenseSkladFetcher
     * @param ExpenseFetcher $expenseFetcher
     * @param OrderGoodLocation $orderGoodLocation
     * @param ZapCardReserveFetcher $zapCardReserveFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardRepository $zapCardRepository
     * @param OrderGoodService $orderGoodService
     * @param OrderPriceService $orderPriceService
     * @param IncomeDocumentFetcher $incomeDocumentFetcher
     * @param ExpenseDocumentFetcher $expenseDocumentFetcher
     * @param SchetFetcher $schetFetcher
     * @param DocumentService $documentService
     * @param Flusher $flusher
     * @return Response
     * @throws Exception
     */
    public function view(
        Order                  $order,
        Request                $request,
        OrderGoodFetcher       $fetcher,
        WeightFetcher          $weightFetcher,
        ExpenseSkladFetcher    $expenseSkladFetcher,
        ExpenseFetcher         $expenseFetcher,
        OrderGoodLocation      $orderGoodLocation,
        ZapCardReserveFetcher  $zapCardReserveFetcher,
        IncomeFetcher          $incomeFetcher,
        ZapCardRepository      $zapCardRepository,
        OrderGoodService       $orderGoodService,
        OrderPriceService      $orderPriceService,
        IncomeDocumentFetcher  $incomeDocumentFetcher,
        ExpenseDocumentFetcher $expenseDocumentFetcher,
        SchetFetcher           $schetFetcher,
        DocumentService        $documentService,
        Flusher                $flusher
    ): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Order');

        $pagination = $fetcher->allSimple($order->getId(), $request->query->getInt('page', 1));


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

            $incomeDocuments = $incomeDocumentFetcher->findByIds($arIncomeDocuments);
            $expenseDocuments = $expenseDocumentFetcher->findByIds($arExpenseDocuments);
            $schets = $schetFetcher->findByIds($arSchet);

            foreach ($items as &$item) {
                $item['styleClasses'] = $orderGoodService->getStyleClasses($item, $expenseDocuments);
                $item['document'] = $documentService->document($item, $expenseDocuments, $incomeDocuments);
                $item['schet'] = $documentService->schet($item, $schets);
            }
            $pagination->setItems($items);
        }

        return $this->render('app/orders/view.html.twig', [
            'user' => $order->getUser(),
            'order' => $order,
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/countNewOrders", name=".countNewOrders")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countNewOrders(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getNewOrders();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countNotSent", name=".countNotSent")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countNotSent(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getNotSent();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countInWork", name=".countInWork")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countInWork(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getInWork();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countNotPaid", name=".countNotPaid")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countNotPaid(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getNotPaid();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countPick", name=".countPick")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countPick(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getPick();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countExpired", name=".countExpired")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countExpired(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getExpired();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countService", name=".countService")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countService(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getService();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countDelivery", name=".countDelivery")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countDelivery(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getDelivery();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countReseller", name=".countReseller")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countReseller(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getReseller();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countAlerts", name=".countAlerts")
     * @param OrderListFetcher $fetcher
     * @param OrderAlertTypeFetcher $orderAlertTypeFetcher
     * @return Response
     */
    public function countAlerts(OrderListFetcher $fetcher, OrderAlertTypeFetcher $orderAlertTypeFetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getAlerts($orderAlertTypeFetcher->assoc());
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countPaidCreditCard", name=".countPaidCreditCard")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countPaidCreditCard(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getPaidCreditCard();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countNotOrdered", name=".countNotOrdered")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countNotOrdered(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getNotOrdered();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order);
        }

        return $this->json($counts);
    }

    /**
     * @Route("/countNewOrdersLast5Minutes", name=".countNewOrdersLast5Minutes")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function countNewOrdersLast5Minutes(OrderListFetcher $fetcher): Response
    {
        $min_from = floor(date("i") / 5) * 5 - 5;
        $min_till = $min_from + 5;

        $date_from = $_COOKIE["orders_date_till"] ?? date("Y-m-d H:i:s", mktime(date("H"), $min_from, 0, date("m"), date("d"), date("Y")));
        $date_till = date("Y-m-d H:i:s", mktime(date("H"), $min_till, 0, date("m"), date("d"), date("Y")));

        $message = '';

        if (($_COOKIE["orders_date_till"] ?? '') != $date_till) {
            setcookie("orders_date_till", $date_till);

            $count = $fetcher->getNewOrdersLast5Minutes($date_from, $date_till);
            if ($count > 0) {
                $message = 'Появилось ' . $count . ' новых заказов';
            }
        }

        return $this->json($message);
    }

    /**
     * @Route("/overlayOrders", name=".overlayOrders")
     * @param OrderListFetcher $fetcher
     * @return Response
     */
    public function overlayOrders(OrderListFetcher $fetcher): Response
    {
        $counts = [];
        $orders = $fetcher->getNewOrders();
        foreach ($orders as $k => $order) {
            $counts[$k] = count($order) . ' шт.';
        }
        $counts['today'] = $fetcher->getTodayOrders() . ' шт.';

        return $this->json($counts);
    }

    /**
     * @Route("/overlaySales", name=".overlaySales")
     * @param ReportRegionProfitFetcher $fetcher
     * @param ManagerRepository $managerRepository
     * @return Response
     * @throws \Exception
     */
    public function overlaySales(ReportRegionProfitFetcher $fetcher, ManagerRepository $managerRepository): Response
    {
        $manager = $managerRepository->get($this->getUser()->getId());
//        $manager = $managerRepository->get(4);
        $result = $fetcher->today($manager);

        $result['msk']['profit_string'] = number_format($result['msk']['profit'], 2, ',', ' ');
        $result['msk']['income_string'] = number_format($result['msk']['income'], 2, ',', ' ');
        $result['spb']['profit_string'] = number_format($result['spb']['profit'], 2, ',', ' ');
        $result['spb']['income_string'] = number_format($result['spb']['income'], 2, ',', ' ');
        $result['region']['profit_string'] = number_format($result['region']['profit'], 2, ',', ' ');
        $result['region']['income_string'] = number_format($result['region']['income'], 2, ',', ' ');
        $result['profit_string'] = number_format($result['profit'], 2, ',', ' ');
        $result['income_string'] = number_format($result['income'], 2, ',', ' ');

        return $this->json($result);
    }
}