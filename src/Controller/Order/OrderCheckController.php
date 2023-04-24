<?php


namespace App\Controller\Order;


use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\ReadModel\Expense\ExpenseDocumentFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserBalanceHistoryFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Model\Order\UseCase\Check\Advance;
use App\Model\Order\UseCase\Check\Pay;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/check", name="order.check")
 */
class OrderCheckController extends AbstractController
{
    /**
     * @Route("/{id}/advance", name=".advance")
     * @param UserBalanceHistory $userBalanceHistory
     * @return Response
     */
    public function advance(UserBalanceHistory $userBalanceHistory): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK, 'Order');

        $command = new Advance\Command($userBalanceHistory->getId(), $this->getUser()->getId());
        $form = $this->createForm(Advance\Form::class, $command);

        return $this->render('app/orders/checks/advance/form.html.twig', [
            'form' => $form->createView(),
            'userBalanceHistory' => $userBalanceHistory
        ]);
    }

    /**
     * @Route("/{id}/advance/update", name=".advance.update")
     * @param UserBalanceHistory $userBalanceHistory
     * @param Request $request
     * @param Advance\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function advanceUpdate(UserBalanceHistory $userBalanceHistory, Request $request, Advance\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Advance\Command($userBalanceHistory->getId(), $this->getUser()->getId());
        $form = $this->createForm(Advance\Form::class, $command);
        $form->handleRequest($request);

        $manager = $managerRepository->get($this->getUser()->getId());

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);
//
                foreach ($messages as $message) {
                    $this->addFlash($message['type'], $message['message']);
                }

                $data['reload'] = true;
//                $data['messages'] = $messages;
//                $data['dataIdentification'] = [
//                    [
//                        'value' => $orderGood->getId(),
//                        'name' => 'goodID'
//                    ]
//                ];
//                $data['ident'] = 'location';
//                $data['value'] = $zapSklad->getNameShort();
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
     * @Route("/{id}/pay", name=".pay")
     * @param ExpenseDocument $expenseDocument
     * @param UserBalanceHistoryFetcher $userBalanceHistoryFetcher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function pay(ExpenseDocument $expenseDocument, UserBalanceHistoryFetcher $userBalanceHistoryFetcher): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK, 'Order');

        $command = new Pay\Command($expenseDocument, $this->getUser()->getId());
        $form = $this->createForm(Pay\Form::class, $command);

        $lastPay = $userBalanceHistoryFetcher->lastPay($expenseDocument->getUser());

        return $this->render('app/orders/checks/pay/form.html.twig', [
            'form' => $form->createView(),
            'lastPay' => $lastPay,
            'expenseDocument' => $expenseDocument
        ]);
    }

    /**
     * @Route("/{id}/pay/update", name=".pay.update")
     * @param ExpenseDocument $expenseDocument
     * @param Request $request
     * @param Pay\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function payUpdate(ExpenseDocument $expenseDocument, Request $request, Pay\Handler $handler, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_CHECK, 'Order');

        $data = ['code' => 200, 'message' => ''];

        $command = new Pay\Command($expenseDocument, $this->getUser()->getId());
        $form = $this->createForm(Pay\Form::class, $command);
        $form->handleRequest($request);

        $manager = $managerRepository->get($this->getUser()->getId());

        if ($form->isValid()) {
            try {
                $messages = $handler->handle($command, $manager);

                foreach ($messages as $message) {
                    $this->addFlash($message['type'], $message['message']);
                }

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
}