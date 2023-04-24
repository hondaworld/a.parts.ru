<?php

namespace App\Controller\Order;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Order\Service\OrderGoodLocation;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\OrderGoodFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Model\Order\UseCase\Good\Label;
use App\Model\Order\UseCase\Good\Blank;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/print", name="order.print")
 */
class OrderPrintController extends AbstractController
{
    /**
     * @Route("/{id}/check", name=".check")
     * @param User $user
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ZapCardRepository $zapCardRepository
     * @return Response
     */
    public function check(User $user, Request $request, OrderGoodFetcher $fetcher, ExpenseDocumentRepository $expenseDocumentRepository, ZapCardRepository $zapCardRepository): Response
    {
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $expenses = $fetcher->allExpenses($user->getId());

        $delivery = ($request->query->get('isDelivery') ? 400 : 0);

        $sum = 0;
        $sumDiscount = 0;

        foreach ($expenses as &$item) {
            $item['zapCard'] = $zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
            $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
            $sumDiscount += $item['priceDiscount'] * $item['quantity'];
            $sum += $item['price'] * $item['quantity'];
        }

        $discount = $sum - $sumDiscount;
        $sumWithDostavka = $sumDiscount + $delivery;

        return $this->render('app/orders/order/print/check/index.html.twig', [
            'expenseDocument' => $expenseDocument,
            'user' => $user,
            'expenses' => $expenses,
            'sum' => $sum,
            'sumDiscount' => $sumDiscount,
            'sumWithDostavka' => $sumWithDostavka,
            'discount' => $discount,
            'delivery' => $delivery
        ]);
    }

    /**
     * @Route("/{id}/locations", name=".locations")
     * @param User $user
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ZapCardRepository $zapCardRepository
     * @param OrderGoodLocation $orderGoodLocation
     * @return Response
     */
    public function locations(User $user, Request $request, OrderGoodFetcher $fetcher, ExpenseDocumentRepository $expenseDocumentRepository, ZapCardRepository $zapCardRepository, OrderGoodLocation $orderGoodLocation): Response
    {
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $expenses = $fetcher->allExpenses($user->getId());

        $orderID = $request->query->getInt('orderID') > 0 ? $request->query->getInt('orderID') : null;

        foreach ($expenses as $k => &$item) {
            if ($orderID && $item['orderID'] != $orderID) {
                unset($expenses[$k]);
            } else {
                $item['zapCard'] = $zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
                $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
                $item['skladName'] = $orderGoodLocation->getSkladName($item['incomeID'], $item['zapSkladID'], $item['providerPriceID']);
                $item['skladLocation'] = $orderGoodLocation->getSkladLocation($item['zapCard'], $item['zapSkladID'], $item['providerPriceID']);
            }
        }

        usort($expenses, function ($a, $b) {
            if ($a['skladLocation'] == $b['skladLocation']) return strcmp($a['number'], $b['number']);
            return strcmp($a['skladLocation'], $b['skladLocation']);
        });

        return $this->render('app/orders/order/print/location/index.html.twig', [
            'expenseDocument' => $expenseDocument,
            'user' => $user,
            'expenses' => $expenses,
            'orderID' => $orderID,
        ]);
    }

    /**
     * @Route("/{id}/shippingLabel", name=".shippingLabel")
     * @param User $user
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @return Response
     */
    public function shippingLabel(User $user, ExpenseDocumentRepository $expenseDocumentRepository): Response
    {
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $arr = [
            'user' => [
                'name' => '',
                'town' => '',
                'phone' => ''
            ],
            'firm' => [
                'name' => '',
                'town' => '',
                'phone' => ''
            ]
        ];

        if ($expenseDocument->getGruzFirmcontr()) {
            $arr['user']['name'] = $expenseDocument->getGruzFirmcontr()->getUr()->getOrganization();
            $arr['user']['phone'] = $expenseDocument->getGruzFirmcontr()->getPhone();
            $arr['user']['town'] = $expenseDocument->getGruzFirmcontr()->getTown()->getNameWithRegion();
        } else {
            if ($expenseDocument->getGruzUser()) {
                $user = $expenseDocument->getGruzUser();
                $contact = $expenseDocument->getGruzUserContact();
            } else {
                $user = $expenseDocument->getExpUser();
                $contact = $expenseDocument->getExpUserContact();
            }

            if ($user) {
                $arr['user']['name'] = $user->getFullNameOrOrganization();
                $arr['user']['phone'] = $user->getPhonemob();
                if ($contact) {
                    $arr['user']['town'] = $contact->getTown()->getNameWithRegion();
                }
            }
        }

        if ($expenseDocument->getGruzFirm()) {
            $firm = $expenseDocument->getGruzFirm();
            $contact = $expenseDocument->getGruzFirmContact();
        } else {
            $firm = $expenseDocument->getExpFirm();
            $contact = $expenseDocument->getExpFirmContact();
        }

        if ($firm) {
            $arr['firm']['name'] = $firm->getName();
        }
        if ($contact) {
            $arr['firm']['town'] = $contact->getTown()->getNameWithRegion();
            if ($contact->getPhonemob() != '') {
                $arr['firm']['phone'] = $contact->getPhonemob();
            } elseif ($contact->getPhone() != '') {
                $arr['firm']['phone'] = $contact->getPhone();
            }
        }

        return $this->render('app/orders/order/print/shippingLabel/index.html.twig', [
            'expenseDocument' => $expenseDocument,
            'arr' => $arr,
        ]);
    }

    /**
     * @Route("/shippingSizes", name=".shippingSizes")
     * @return Response
     */
    public function shippingSizes(): Response
    {

        return $this->render('app/orders/order/print/shippingSizes/index.html.twig', []);
    }

    /**
     * @Route("/{id}/labels", name=".labels")
     * @param User $user
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param ZapCardRepository $zapCardRepository
     * @param OrderGoodLocation $orderGoodLocation
     * @return Response
     */
    public function labels(
        User              $user,
        Request           $request,
        OrderGoodFetcher  $fetcher,
        ZapCardRepository $zapCardRepository,
        OrderGoodLocation $orderGoodLocation
    ): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_LABELS, 'Order');

        $expenses = $fetcher->allExpenses($user->getId());
        $goods = [];

        foreach ($expenses as &$item) {
            $item['zapCard'] = $zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
            $item['skladLocation'] = $orderGoodLocation->getSkladLocation($item['zapCard'], $item['zapSkladID'], $item['providerPriceID']);
            $goods[$item['goodID']] = [
                'quantity' => $item['quantity'],
                'isCheck' => is_numeric($item['zapSkladID']),
            ];
        }

        $incomeCommand = new Label\Command($goods);
        $form = $this->createForm(Label\Form::class, $incomeCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $goods = $incomeCommand->goods;
                dump($expenses);
                return $this->render('app/orders/order/print/label/print.html.twig', [
                    'goods' => $goods,
                    'expenses' => $expenses
                ]);

            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/order/print/label/index.html.twig', [
            'user' => $user,
            'expenses' => $expenses,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/blankForm", name=".blankForm")
     * @param User $user
     * @return Response
     */
    public function blankForm(User $user): Response
    {
        $command = new Blank\Command();
        $form = $this->createForm(Blank\Form::class, $command);

        return $this->render('app/orders/order/print/blank/form.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);

    }

    /**
     * @Route("/{id}/blank", name=".blank")
     * @param User $user
     * @return Response
     */
    public function blank(User $user, Request $request, OrderGoodFetcher $fetcher, ExpenseDocumentRepository $expenseDocumentRepository, ZapCardRepository $zapCardRepository): Response
    {
        $command = new Blank\Command();
        $form = $this->createForm(Blank\Form::class, $command);
        $form->handleRequest($request);

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $expenses = $fetcher->allByOrderGoods($user->getId(), explode(',', $command->cols));

        $sum = 0;
        $sumDiscount = 0;

        foreach ($expenses as &$item) {
            $item['zapCard'] = $zapCardRepository->getByNumberAndCreaterID($item['number'], $item['createrID']);
            $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);
            $sumDiscount += $item['priceDiscount'] * $item['quantity'];
            $sum += $item['price'] * $item['quantity'];
            if ($command->isHideNumbers) {
                $item['number'] = (new DetailNumber($item['number']))->getHideValue();
            }
        }

        $discount = $sum - $sumDiscount;

        return $this->render('app/orders/order/print/blank/index.html.twig', [
            'isHideNumbers' => $command->isHideNumbers,
            'isShowSrok' => $command->isShowSrok,
            'expenseDocument' => $expenseDocument,
            'user' => $user,
            'expenses' => $expenses,
            'sum' => $sum,
            'sumDiscount' => $sumDiscount,
            'discount' => $discount,
        ]);
    }
}
