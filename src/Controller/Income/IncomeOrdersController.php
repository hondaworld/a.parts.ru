<?php


namespace App\Controller\Income;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\UseCase\Order\Mail;
use App\Model\Income\UseCase\Order\Create;
use App\Model\Income\UseCase\Order\DeleteIncome;
use App\Model\Income\Service\IncomeOrder\IncomeOrderExcelPriceFabric;
use App\Model\Manager\Entity\Manager\ManagerRepository;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Income\IncomeOrderFetcher;
use App\ReadModel\Income\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Security\Voter\User\UserVoter;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Swift_Attachment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/income/orders", name="income.orders")
 */
class IncomeOrdersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param IncomeOrderFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, IncomeOrderFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');

        $settings = $settings->get('incomeOrder');

        $filter = new Filter\IncomeOrder\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\IncomeOrder\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/income/incomeOrder/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/createForm", name=".createForm")
     * @return Response
     */
    public function createF(): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'IncomeOrder');

        $command = new Create\Command();
        $form = $this->createForm(Create\Form::class, $command);

        return $this->render('app/income/incomeOrder/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param Create\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function create(Request $request, ValidatorInterface $validator, Create\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'IncomeOrder');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $manager = $managerRepository->get($this->getUser()->getId());

        $data = ['code' => 200, 'message' => ''];

        $command = new Create\Command();
        $command->zapSkladID = $request->request->get('form')['zapSkladID'];
        $command->cols = $request->request->get('cols');

        $errors = $validator->validate($command);
        if (count($errors) == 0) {
            try {
                $messages = $handler->handle($command, $manager);

                if ($messages) {
                    foreach ($messages as $message) {
                        $this->addFlash($message['type'], $message['message']);
                    }
                }

                $data['reload'] = true;

            } catch (DomainException $e) {
                $data['code'] = 404;
                $data['message'] = $e->getMessage();
            }
        } else {
            $data['code'] = 404;
            foreach ($errors as $error) {
                $data['message'] .= $error->getMessage() . ' ';
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/mail", name=".mail")
     * @param IncomeOrder $incomeOrder
     * @param Request $request
     * @param ManagerRepository $managerRepository
     * @param Mail\Handler $handler
     * @return Response
     */
    public function mail(IncomeOrder $incomeOrder, Request $request, ManagerRepository $managerRepository, Mail\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');

        $manager = $managerRepository->get($this->getUser()->getId());

        try {
            $status = $incomeOrder->getIsOrdered();
            $handler->handle($incomeOrder, $this->getParameter('price_directory'), $manager);

            if ($status == 0) {
                $this->addFlash('success', 'Заказ отправлен на ' . $incomeOrder->getProvider()->getIncomeOrderEmail());
            } elseif ($status == 1) {
                $this->addFlash('success', 'Заказ отправлен повторно');
            }
        } catch (DomainException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('income.orders', ['page' => $request->getSession()->get('page/incomeOrder') ?: 1]);
    }

    /**
     * @Route("/{id}/excel", name=".excel")
     * @param IncomeOrder $incomeOrder
     * @param Request $request
     * @param IncomeOrderFetcher $fetcher
     * @param ManagerRepository $managerRepository
     * @param Mail\Handler $handler
     * @return Response
     */
    public function excel(IncomeOrder $incomeOrder, Request $request, IncomeOrderFetcher $fetcher, ManagerRepository $managerRepository, Mail\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');

        $path = IncomeOrderExcelPriceFabric::get($incomeOrder, $this->getParameter('price_directory'))->create();

        return $this->file($path);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param IncomeOrder $incomeOrder
     * @param Request $request
     * @param DeleteIncome\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function delete(IncomeOrder $incomeOrder, Request $request, DeleteIncome\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        if ($incomeOrder->isOrdered()) {
            return $this->json(['code' => 500, 'message' => 'Заказ уже отправлен. Удаление невозможно.']);
        } elseif ($incomeOrder->isDeleted()) {
            return $this->json(['code' => 500, 'message' => 'Заказ уже удален. Удаление номера невозможно, т.к. после заказа были другие заказы.']);
        }
        try {
            $isDeleteIncome = $request->query->getBoolean('isDeleteIncome');
            foreach ($incomeOrder->getIncomes() as $income) {
                $handler->handle($incomeOrder, $income, $manager, $em, $isDeleteIncome);
            }

            $data['message'] = 'Запись удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{id}/incomes", name=".incomes")
     * @param IncomeOrder $incomeOrder
     * @param Request $request
     * @param IncomeFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function incomes(IncomeOrder $incomeOrder, Request $request, IncomeFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');

        $settings = $settings->get('incomeOrderIncomes');

        $pagination = $fetcher->allByIncomeOrder(
            $incomeOrder,
            $settings
        );

        return $this->render('app/income/incomeOrder/incomes.html.twig', [
            'pagination' => $pagination,
            'incomeOrder' => $incomeOrder,
        ]);
    }

    /**
     * @Route("/{incomeOrderID}/{id}/deleteIncome", name=".deleteIncome")
     * @ParamConverter("incomeOrder", options={"id" = "incomeOrderID"})
     * @param IncomeOrder $incomeOrder
     * @param Income $income
     * @param Request $request
     * @param DeleteIncome\Handler $handler
     * @param ManagerRepository $managerRepository
     * @return Response
     */
    public function deleteIncome(IncomeOrder $incomeOrder, Income $income, Request $request, DeleteIncome\Handler $handler, ManagerRepository $managerRepository): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'IncomeOrder');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $manager = $managerRepository->get($this->getUser()->getId());

        $em = $this->getDoctrine()->getManager();
        if ($incomeOrder->isOrdered()) {
            return $this->json(['code' => 500, 'message' => 'Заказ уже отправлен. Удаление невозможно.']);
        } elseif ($incomeOrder->isDeleted()) {
            return $this->json(['code' => 500, 'message' => 'Заказ уже удален. Удаление номера невозможно, т.к. после заказа были другие заказы.']);
        }
        try {
            $isDeleteIncome = $request->query->getBoolean('isDeleteIncome');
            $isRedirect = $handler->handle($incomeOrder, $income, $manager, $em, $isDeleteIncome);

            if ($isRedirect) $data['redirectToUrl'] = $this->generateUrl('income.orders', ['page' => $request->getSession()->get('page/incomeOrder') ?: 1]);

            $data['message'] = 'Запись удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}