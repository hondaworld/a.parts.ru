<?php

namespace App\Controller\Order;

use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\User\User;
use App\Security\Voter\Order\OrderVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Order\UseCase\Order\Picking;
use App\Model\Order\UseCase\Order\Picked;
use App\Model\Order\UseCase\Order\PickDel;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderPickController extends AbstractController
{
    /**
     * @Route("/{id}/picking", name=".picking")
     * @param User $user
     * @param Picking\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function picking(User $user, Picking\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        try {
            $handler->handle($user, $manager);

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/picked", name=".picked")
     * @param User $user
     * @param Picked\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function picked(User $user, Picked\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        try {
            $handler->handle($user, $manager);

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/pickDel", name=".pickDel")
     * @param User $user
     * @param PickDel\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function pickDel(User $user, PickDel\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PICK, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        try {
            $handler->handle($user, $manager);

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }
}