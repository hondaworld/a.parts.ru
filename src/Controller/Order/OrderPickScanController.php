<?php

namespace App\Controller\Order;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Order\Service\OrderGoodLocation;
use App\Model\Order\UseCase\Order\QuantityPicking;
use App\Model\User\Entity\User\User;
use App\ReadModel\Expense\Filter;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\Order\ShippingPlaceFetcher;
use App\Security\Voter\Order\OrderVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/pick", name="order.pick")
 */
class OrderPickScanController extends AbstractController
{
    /**
     * @Route("/{id}/scan", name=".scan")
     * @param User $user
     * @param Request $request
     * @param OrderGoodFetcher $fetcher
     * @param ShippingPlaceFetcher $shippingPlaceFetcher
     * @param OrderGoodLocation $orderGoodLocation
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param QuantityPicking\Handler $handler
     * @return Response
     * @throws Exception
     */
    public function index(
        User                      $user,
        Request                   $request,
        OrderGoodFetcher          $fetcher,
        ShippingPlaceFetcher      $shippingPlaceFetcher,
        OrderGoodLocation         $orderGoodLocation,
        ExpenseDocumentRepository $expenseDocumentRepository,
        QuantityPicking\Handler   $handler
    ): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $places = $shippingPlaceFetcher->allNotShipping($expenseDocument);

        $command = new QuantityPicking\Command();
        $form = $this->createForm(QuantityPicking\Form::class, $command);
        $form->handleRequest($request);

        $searchNumber = $request->query->get('number') ? (new DetailNumber($request->query->get('number'))) : null;
        $scan = $request->query->get('scan') ?? 0;

        $expenses = $fetcher->allExpenses($user->getId());
        $expenseSklad = [];
        $expenseNumbers = [];

        foreach ($expenses as &$item) {
            $item['location'] = $orderGoodLocation->get($item['incomeID'], $item['zapSkladID'], $item['providerPriceID']);
            $item['priceDiscount'] = round($item['price'] - $item['price'] * $item['discount'] / 100);

            $expenseNumbers[$item['number']]['quantity'] = ($expenseNumbers[$item['number']]['quantity'] ?? 0) + $item['quantity'];
            $expenseNumbers[$item['number']]['quantityPicking'] = ($expenseNumbers[$item['number']]['quantityPicking'] ?? 0) + $item['quantityPicking'];
            $expenseNumbers[$item['number']]['expenses'][] = $item;
        }

        unset($item);
        foreach ($expenseNumbers as $number => $item) {
            if ($searchNumber != '' && $searchNumber->isEqual(new DetailNumber($number)) && $item['quantity'] != $item['quantityPicking']) {
                if ($item['quantity'] - $item['quantityPicking'] == 1) {
                    $command->quantityPicking = 1;
                } else {
                    if ($scan != 2) return $this->redirectToRoute('order.pick.scan', ['id' => $user->getId(), 'number' => $searchNumber->getValue(), 'scan' => 2]);
                }
                $expenseSklad = $item;
                foreach ($item['expenses'] as $expense) {
                    if ($expense['quantity'] - $expense['quantityPicking'] > 0) {
                        $expenseSklad['number'] = $expense['number'];
                        $expenseSklad['creater_name'] = $expense['creater_name'];
                        break;
                    }
                }
            }
        }

        if (!$expenseSklad && $searchNumber) {
            $this->addFlash('error', 'Номер не найден');
        }

        if ($expenseSklad && ($form->isSubmitted() && $form->isValid() || $command->quantityPicking)) {
            try {
                $handler->handle($command, $expenseSklad);
                if ($fetcher->hasPicking($user->getId())) {
                    return $this->redirectToRoute('order.pick.scan', ['id' => $user->getId(), 'scan' => 1]);
                } else {
                    $this->addFlash('success', 'Все детали собраны');
                    return $this->redirectToRoute('order.pick.scan', ['id' => $user->getId()]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }


        return $this->render('app/orders/order/pick/index.html.twig', [
            'user' => $user,
            'expenseDocument' => $expenseDocument,
            'expenseSklad' => $expenseSklad,
            'locations' => $locations ?? [],
            'expenses' => $expenses ?? [],
            'form' => $form->createView(),
            'searchNumber' => $searchNumber,
            'places' => $places,
        ]);
    }

    /**
     * @Route("/{id}/scan/delete", name=".scan.delete")
     * @param User $user
     * @param Request $request
     * @param OrderGoodRepository $orderGoodRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, Request $request, OrderGoodRepository $orderGoodRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        try {
            $goodID = $request->query->getInt('goodID');
            $orderGood = $orderGoodRepository->get($goodID);

            if ($orderGood->getOrder()->getUser()->getId() == $user->getId()) {
                $orderGood->unPicking();
                $flusher->flush();
                $data['redirectToUrl'] = $this->generateUrl('order.pick.scan', ['id' => $user->getId()]);
            } else {
                return $this->json(['code' => 500, 'message' => "ID клиентов не совпадают"]);
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
