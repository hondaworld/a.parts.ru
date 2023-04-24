<?php

namespace App\Controller\Order;

use App\Model\User\Entity\User\User;
use App\ReadModel\Order\Filter;
use App\ReadModel\Order\OrderManagerOperationFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/order/manager/operations", name="order.manager.operations")
 */
class OrderManagerOperationsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param OrderManagerOperationFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function index(User $user, Request $request, OrderManagerOperationFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::MANAGER_ORDER_OPERATIONS, 'Order');

        $settings = $settings->get('orderManagerOperation');

        $filter = new Filter\ManagerOperation\Filter($user->getId());
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\ManagerOperation\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $user,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/orders/managerOperations/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => false,
            'user' => $user,
        ]);
    }
}
