<?php

namespace App\Controller\Order;

use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\OrderShippingFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/shippings", name="order.shippings")
 */
class OrderShippingsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param OrderShippingFetcher $orderShippingFetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function index(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, OrderShippingFetcher $orderShippingFetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_SHIPPING, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $settings = $settings->get('orderShipping');

        $pagination = $orderShippingFetcher->all(
            $user,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/orders/shipping/index.html.twig', [
            'expenseDocument' => $expenseDocument,
            'user' => $user,
            'pagination' => $pagination,
            'user_shipping_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_shipping_attach') . '/'
        ]);
    }
}
