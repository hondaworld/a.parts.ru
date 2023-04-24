<?php


namespace App\Controller\Order;


use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\BalanceHistory\Create;
use App\Model\User\UseCase\BalanceHistory\Edit;
use App\Model\User\UseCase\BalanceHistory\EditFinanceType;
use App\Model\User\UseCase\BalanceHistory\Attach;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserBalanceHistoryFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Security\Voter\User\UserVoter;
use App\Service\FileUploader;
use DomainException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/paids", name="order.paids")
 */
class OrderPaidsController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param UserBalanceHistoryFetcher $fetcher
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param OrderGoodFetcher $orderGoodFetcher
     * @param ManagerRepository $managerRepository
     * @param Create\Handler $handler
     * @return Response
     * @throws Exception
     */
    public function index(User $user, Request $request, UserBalanceHistoryFetcher $fetcher, ExpenseDocumentRepository $expenseDocumentRepository, OrderGoodFetcher $orderGoodFetcher, ManagerRepository $managerRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_PAID, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $manager = $managerRepository->get($this->getUser()->getId());

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $messages = $handler->handle($command, $user, $manager);
                foreach ($messages as $message) {
                    $this->addFlash($message['type'], $message['message']);
                }
                return $this->redirectToRoute('order.paids', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $lastOperations = $fetcher->lastOperations($user);
        $sumOperations = $fetcher->sumOperations($user);
        $sum = $orderGoodFetcher->sumExpenses($user->getId());

        $operations = [];
        $financeTypes = [];

        foreach ($sumOperations as $operation) {
            $operations[$operation['firm']][$operation['finance_type']] = $operation['balance'];
            if (!in_array($operation['finance_type'], $financeTypes)) $financeTypes[] = $operation['finance_type'];
        }

        return $this->render('app/orders/paid/index.html.twig', [
            'user' => $user,
            'lastOperations' => $lastOperations,
            'operations' => $operations,
            'financeTypes' => $financeTypes,
            'sum' => $sum,
            'form' => $form->createView(),
            'user_balance_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_balance_attach') . '/'
        ]);
    }

    /**
     * @Route("/{userID}/{id}/edit", name=".edit")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserBalanceHistory $userBalanceHistory
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(User $user, UserBalanceHistory $userBalanceHistory, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE_CHANGE, $user);

        $command = Edit\Command::fromEntity($userBalanceHistory);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('order.paids', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/paid/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/editFinanceType", name=".editFinanceType")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserBalanceHistory $userBalanceHistory
     * @param Request $request
     * @param EditFinanceType\Handler $handler
     * @return Response
     */
    public function editFinanceType(User $user, UserBalanceHistory $userBalanceHistory, Request $request, EditFinanceType\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE_CHANGE_FINANCE_TYPE, $user);

        $command = EditFinanceType\Command::fromEntity($userBalanceHistory);

        $form = $this->createForm(EditFinanceType\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('order.paids', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/paid/editFinanceType.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/attach", name=".attach")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserBalanceHistory $userBalanceHistory
     * @param Request $request
     * @param Attach\Handler $handler
     * @return Response
     */
    public function attach(User $user, UserBalanceHistory $userBalanceHistory, Request $request, Attach\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE_CHANGE_FINANCE_TYPE, $user);

        $command = Attach\Command::fromEntity($userBalanceHistory, $this->getParameter('admin_site') . $this->getParameter('user_balance_attach') . '/');

        $form = $this->createForm(Attach\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('attach')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('user_balance_attach'));
                    $newFilename = $fileUploader->uploadToAdminAndDelete($attach, $userBalanceHistory->getAttach());
//                    $fileUploader->delete($manager->getPhoto());
                    if ($newFilename) {
                        $command->attach = $newFilename;
                        $handler->handle($command);
                        $this->addFlash('success', "Платежка загружена");
                        return $this->redirectToRoute('order.paids', ['id' => $user->getId()]);
                    } else {
                        $this->addFlash('error', "Файл не загружен");
                    }
                } else {
                    $this->addFlash('error', "Файл не выбран");
                }


            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/paid/editAttach.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{userID}/{id}/attach/delete", name=".attach.delete")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserBalanceHistory $userBalanceHistory
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(User $user, UserBalanceHistory $userBalanceHistory, Flusher $flusher): Response
    {
        $attach = $userBalanceHistory->getAttach();

        if ($attach != '') {
            $fileUploader = new FileUploader($this->getParameter('user_balance_attach'));
            $fileUploader->deleteFromAdmin($attach);
            $userBalanceHistory->removeAttach();

            $flusher->flush();
        }

        return $this->json([]);
    }
}