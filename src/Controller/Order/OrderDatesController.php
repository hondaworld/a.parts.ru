<?php

namespace App\Controller\Order;

use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\User\User;
use App\Security\Voter\Order\OrderVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Order\UseCase\Good\DateOfService;
use App\Model\Order\UseCase\Good\DateOfDelivery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderDatesController extends AbstractController
{
    /**
     * @Route("/{id}/dateOfService", name=".dateOfService")
     * @param User $user
     * @return Response
     */
    public function dateOfServiceForm(User $user): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHANGE_DATES, 'Order');

        $command = new DateOfService\Command($user->getDateofservice());
        $form = $this->createForm(DateOfService\Form::class, $command);

        return $this->render('app/orders/goods/dateOfService/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);

    }

    /**
     * @Route("/{id}/dateOfService/update", name=".dateOfService.update")
     * @param User $user
     * @param Request $request
     * @param DateOfService\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function dateOfServiceUpdate(User $user, Request $request, DateOfService\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHANGE_DATES, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new DateOfService\Command($user->getDateofservice());
        $form = $this->createForm(DateOfService\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                $data['dataIdentification'] = [
                    [
                        'value' => $user->getId(),
                        'name' => 'userID'
                    ]
                ];
                $data['ident'] = 'dateofservice';
                $data['value'] = $user->getDateofservice() ? $user->getDateofservice()->format('d.m.Y') : '<span class="text-muted font-italic">не задан</span>';
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
     * @Route("/{id}/dateOfDelivery", name=".dateOfDelivery")
     * @param User $user
     * @return Response
     */
    public function dateOfDeliveryForm(User $user): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHANGE_DATES, 'Order');

        $command = new DateOfDelivery\Command($user->getDateofdelivery());
        $form = $this->createForm(DateOfDelivery\Form::class, $command);

        return $this->render('app/orders/goods/dateOfDelivery/form.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);

    }

    /**
     * @Route("/{id}/dateOfDelivery/update", name=".dateOfDelivery.update")
     * @param User $user
     * @param Request $request
     * @param DateOfDelivery\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function dateOfDeliveryUpdate(User $user, Request $request, DateOfDelivery\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHANGE_DATES, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new DateOfDelivery\Command($user->getDateofdelivery());
        $form = $this->createForm(DateOfDelivery\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $handler->handle($command, $user, $manager);
                $data['dataIdentification'] = [
                    [
                        'value' => $user->getId(),
                        'name' => 'userID'
                    ]
                ];
                $data['ident'] = 'dateofdelivery';
                $data['value'] = $user->getDateofdelivery() ? $user->getDateofdelivery()->format('d.m.Y') : '<span class="text-muted font-italic">не задана</span>';
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
}