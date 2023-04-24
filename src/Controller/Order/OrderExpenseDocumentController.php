<?php


namespace App\Controller\Order;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\Document\ExpenseDocumentRepository;
use App\Model\Expense\Service\ExpenseDocumentChecker;
use App\Model\Expense\Service\ExpenseDocumentPrintService;
use App\Model\Expense\Service\SchetFakPrintService;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\User\User;
use App\Model\Order\UseCase\ExpenseDocument\FinanceType;
use App\Model\Order\UseCase\ExpenseDocument\SmsCode;
use App\Model\Order\UseCase\ExpenseDocument\Create;
use App\Model\Order\UseCase\ExpenseDocument\Test;
use App\ReadModel\Order\OrderGoodFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserBalanceHistoryFetcher;
use App\Security\Voter\Order\OrderVoter;
use App\Service\Sms\SmsSender;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/order/expenseDocument", name="order.expenseDocument")
 */
class OrderExpenseDocumentController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param UserBalanceHistoryFetcher $userBalanceHistoryFetcher
     * @param OrderGoodFetcher $orderGoodFetcher
     * @param ManagerRepository $managerRepository
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function index(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, UserBalanceHistoryFetcher $userBalanceHistoryFetcher, OrderGoodFetcher $orderGoodFetcher, ManagerRepository $managerRepository): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);
        $sum = $orderGoodFetcher->sumExpenses($user->getId());
        $countDebtDays = $userBalanceHistoryFetcher->getDebtDays($user);
        $dateDebtDays = (new \DateTime())->modify('-' . $countDebtDays . ' days');

        $expenses = $orderGoodFetcher->allExpenses($user->getId());

        $financeTypeBalance = 0;
        if ($expenseDocument->getFinanceType()) {
            $financeTypeBalance = $userBalanceHistoryFetcher->sumOperationsByFirmAndType($user, $expenseDocument->getFinanceType());
        }
        $expenseDocumentChecker = new ExpenseDocumentChecker($request, $expenses, $expenseDocument, $user, $sum, $financeTypeBalance);

        $isCheckPrint = $expenseDocumentChecker->check();
        $isTorg12Print = $expenseDocumentChecker->torg12();
        $isTorg12TestPrint = $expenseDocumentChecker->torg12Test();

        $command = new Test\Command();
        $form = $this->createForm(Test\Form::class, $command);

        return $this->render('app/orders/expenseDocument/index.html.twig', [
            'user' => $user,
            'expenseDocument' => $expenseDocument,
            'sum' => $sum,
            'expenses' => $expenses,
            'isCheckPrint' => $isCheckPrint,
            'isTorg12Print' => $isTorg12Print,
            'isTorg12TestPrint' => $isTorg12TestPrint,
            'financeTypeBalance' => $financeTypeBalance,
            'countDebtDays' => $countDebtDays,
            'dateDebtDays' => $dateDebtDays,
            'form' => $form->createView(),
//            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ManagerRepository $managerRepository
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, ManagerRepository $managerRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $manager = $managerRepository->get($this->getUser()->getId());
        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $command = new Create\Command($request);
        try {
            $handler->handle($command, $expenseDocument, $user, $manager);

            return $this->redirectToRoute('order.expenseDocument.done', ['id' => $user->getId(), 'expenseDocumentID' => $expenseDocument->getId()]);
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('order.expenseDocument', ['id' => $user->getId()]);
//        return $this->render('app/home.html.twig', ['news' => []]);

    }

    /**
     * @Route("/{id}/done", name=".done")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param ExpenseDocumentPrintService $expenseDocumentPrintService
     * @param SchetFakPrintService $schetFakPrintService
     * @return Response
     */
    public function done(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, ExpenseDocumentPrintService $expenseDocumentPrintService, SchetFakPrintService $schetFakPrintService): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $expenseDocumentID = $request->query->getInt('expenseDocumentID');
        $expenseDocument = $expenseDocumentRepository->get($expenseDocumentID);

        $isNakladnaya = false;
        $isChek = false;
        $isSchetFak = false;

        if ($expenseDocument->getDocumentType()->getId() == DocumentType::RN) {
            $expenseDocumentPrintService->getNakladnaya($expenseDocument);
            $isNakladnaya = true;
        }

        if ($expenseDocument->getDocumentType()->getId() == DocumentType::TCH) {
            $expenseDocumentPrintService->getCheck($expenseDocument);
            $isChek = true;
        }

        if (!$expenseDocument->isSimpleCheck() && $expenseDocument->getSchetFak()) {
            $schetFakPrintService->getSchetFak($expenseDocument->getSchetFak());
            $schetFak = $expenseDocument->getSchetFak();
            $isSchetFak = true;
        }

        return $this->render('app/orders/expenseDocument/done.html.twig', [
            'user' => $user,
            'expenseDocument' => $expenseDocument,
            'schetFak' => $schetFak ?? null,
            'isNakladnaya' => $isNakladnaya,
            'isChek' => $isChek,
            'isSchetFak' => $isSchetFak,
        ]);
    }

    /**
     * @Route("/{id}/data", name=".data")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param OrderGoodFetcher $orderGoodFetcher
     * @param FinanceType\Handler $handler
     * @return Response
     */
    public function data(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, OrderGoodFetcher $orderGoodFetcher, FinanceType\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);
        $sum = $orderGoodFetcher->sumExpenses($user->getId());

        $command = FinanceType\Command::fromEntity($expenseDocument);

        $form = $this->createForm(FinanceType\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('order.expenseDocument', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/expenseDocument/data/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'expenseDocument' => $expenseDocument,
        ]);
    }

    /**
     * @Route("/{id}/sendSms", name=".sendSms")
     * @param User $user
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param SmsSender $smsSender
     * @param ManagerRepository $managerRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function sendSms(User $user, ExpenseDocumentRepository $expenseDocumentRepository, SmsSender $smsSender, ManagerRepository $managerRepository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);
        $expenseDocument->generateSmsCode();
        $smsSender->sendFromParts($managerRepository->get($this->getUser()->getId()), $user, $expenseDocument->getSmsCode());

        $this->addFlash('success', 'SMS код отправлен');

        $flusher->flush();

        return $this->redirectToRoute('order.expenseDocument.sms', ['id' => $user->getId()]);
    }

    /**
     * @Route("/{id}/sms", name=".sms")
     * @param User $user
     * @param Request $request
     * @param ExpenseDocumentRepository $expenseDocumentRepository
     * @param SmsCode\Handler $handler
     * @return Response
     */
    public function sms(User $user, Request $request, ExpenseDocumentRepository $expenseDocumentRepository, SmsCode\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(OrderVoter::ORDER_EXPENSE_DOCUMENT, 'Order');

        $expenseDocument = $expenseDocumentRepository->getOrCreate($user);

        $command = new SmsCode\Command();

        $form = $this->createForm(SmsCode\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $expenseDocument);
                return $this->redirectToRoute('order.expenseDocument', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/orders/expenseDocument/sms/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'expenseDocument' => $expenseDocument,
        ]);
    }
}