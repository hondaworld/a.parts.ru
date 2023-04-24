<?php

namespace App\Controller\Order;

use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Security\Voter\Order\OrderVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Model\Order\UseCase\Good\Reserve;
use App\Model\Order\UseCase\Good\ReserveDelete;
use App\Model\Order\UseCase\Good\Expense;
use App\Model\Order\UseCase\Good\ExpenseDelete;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/goods", name="order.goods")
 */
class OrderGoodReserveController extends AbstractController
{
    /**
     * @Route("/reserve", name=".reserve")
     * @param Request $request
     * @param Reserve\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function reserve(Request $request, Reserve\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_RESERVE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Reserve\Command();
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $messages = $handler->handle($command, $manager);

//            $data['messages'] = $messages;

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/reserve/delete", name=".reserve.delete")
     * @param Request $request
     * @param ReserveDelete\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function reserveDel(Request $request, ReserveDelete\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_RESERVE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new ReserveDelete\Command();
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $messages = $handler->handle($command, $manager);

//            $data['messages'] = $messages;

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/expense", name=".expense")
     * @param ExpenseDocument $expenseDocument
     * @param Request $request
     * @param Expense\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function expense(ExpenseDocument $expenseDocument, Request $request, Expense\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_EXPENSE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Expense\Command();
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $messages = $handler->handle($command, $manager, $expenseDocument);

//            $data['messages'] = $messages;

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/expense/delete", name=".expense.delete")
     * @param ExpenseDocument $expenseDocument
     * @param Request $request
     * @param ExpenseDelete\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function expenseDel(ExpenseDocument $expenseDocument, Request $request, ExpenseDelete\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_GOOD_EXPENSE, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new ExpenseDelete\Command();
        $command->cols = $request->request->get('cols');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $messages = $handler->handle($command, $manager, $expenseDocument);

//            $data['messages'] = $messages;

            foreach ($messages as $message) {
                $this->addFlash($message['type'], $message['message']);
            }

            $data['reload'] = true;
        } catch (DomainException $e) {
            $data['code'] = 404;
            $data['message'] = $e->getMessage();
        }

        return $this->json($data);
    }
}