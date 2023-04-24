<?php


namespace App\Controller\User;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\FirmRepository;
use App\Model\Flusher;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\BalanceHistory\Edit;
use App\Model\User\UseCase\BalanceHistory\EditFinanceType;
use App\Model\User\UseCase\BalanceHistory\Attach;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserBalanceHistoryFetcher;
use App\Security\Voter\User\UserVoter;
use App\Service\FileUploader;
use App\Service\ManagerSettings;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users/balance/history", name="users.balance.history")
 */
class UserBalanceHistoryController extends AbstractController
{

    /**
     * @Route("/{userID}/", name="")
     * @param User $user
     * @param Request $request
     * @param UserBalanceHistoryFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     * @throws \Exception
     */
    public function index(User $user, Request $request, UserBalanceHistoryFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE, $user);

        $settings = $settings->get('userBalanceHistory');

        $filter = new Filter\UserBalanceHistory\Filter($user->getId());
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\UserBalanceHistory\Form::class, $filter);
        $form->handleRequest($request);

        $act = new Filter\UserBalanceAct\Filter($user->getId());
        $formAct = $this->createForm(Filter\UserBalanceAct\Form::class, $act);

        $pagination = $fetcher->allByUser(
            $user,
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/users/balanceHistory/index.html.twig', [
            'user' => $user,
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'act' => $formAct->createView(),
            'user_balance_attach_folder' => $this->getParameter('admin_site') . $this->getParameter('user_balance_attach') . '/'
        ]);
    }

    /**
     * @Route("/{userID}/act", name=".act")
     * @param User $user
     * @param Request $request
     * @param UserBalanceHistoryFetcher $fetcher
     * @param FirmRepository $firmRepository
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function act(User $user, Request $request, UserBalanceHistoryFetcher $fetcher, FirmRepository $firmRepository): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE, $user);

        $act = new Filter\UserBalanceAct\Filter($user->getId());
        $act->firmID = $request->query->get('form')['firmID'];
        $act->dateofadded['date_from'] = $request->query->get('form')['dateofadded']['date_from'];
        $act->dateofadded['date_till'] = $request->query->get('form')['dateofadded']['date_till'];
        if (!$act->dateofadded['date_from'] || !$act->dateofadded['date_till']) {
            $act->DefaultDate();
        }

        $date_from = new \DateTime($act->dateofadded['date_from']);
        $date_till = new \DateTime($act->dateofadded['date_till']);

        $firm = $firmRepository->get($act->firmID);
        $balance = $fetcher->act($user, $act);

        return $this->render('app/users/balanceHistory/act.html.twig', [
            'balance' => $balance,
            'user' => $user,
            'firm' => $firm,
            'date_from' => $date_from,
            'date_till' => $date_till
        ]);
    }

//    /**
//     * @Route("/{userID}/create", name=".create")
//     * @param User $user
//     * @param Request $request
//     * @return Response
//     */
//    public function create(User $user, Request $request, Create\Handler $handler): Response
//    {
//        $this->denyAccessUnlessGranted(UserVoter::USER_CONTACTS_CHANGE, $user);
//
//        $command = new Create\Command($user);
//
//        $form = $this->createForm(Create\Form::class, $command);
//        $form->handleRequest($request);
//
//        if ($form->isSubmitted() && $form->isValid()) {
//            try {
//                $handler->handle($command);
//                return $this->redirectToRoute('users.contacts', ['userID' => $user->getId()]);
//            } catch (\DomainException $e) {
//                $this->addFlash('error', $e->getMessage());
//            }
//        }
//
//        return $this->render('app/contacts/users/create.html.twig', [
//            'user' => $user,
//            'form' => $form->createView()
//        ]);
//    }

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
                return $this->redirectToRoute('users.balance.history', ['userID' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/balanceHistory/edit.html.twig', [
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
                return $this->redirectToRoute('users.balance.history', ['userID' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/balanceHistory/editFinanceType.html.twig', [
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
                        return $this->redirectToRoute('users.balance.history', ['userID' => $user->getId()]);
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

        return $this->render('app/users/balanceHistory/editAttach.html.twig', [
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

    /**
     * @Route("/{userID}/{id}/delete", name=".delete")
     * @ParamConverter("user", options={"id" = "userID"})
     * @param User $user
     * @param UserBalanceHistory $userBalanceHistory
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(User $user, UserBalanceHistory $userBalanceHistory, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(UserVoter::USER_BALANCE_CHANGE, $user);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {

            if ($userBalanceHistory->getExpenseDocument()) {
                $data = ['code' => 500, 'message' => 'Невозможно удалить платеж, привязанный к РН'];
            } elseif ($userBalanceHistory->getCheck()) {
                $data = ['code' => 500, 'message' => 'Невозможно удалить платеж с распечатанным чеком'];
            } else {
                $user->changeBalance(-$userBalanceHistory->getBalance());

                if ($userBalanceHistory->getAttach() != '') {
                    $fileUploader = new FileUploader($this->getParameter('user_balance_attach'));
                    $fileUploader->deleteFromAdmin($userBalanceHistory->getAttach());
                    $userBalanceHistory->removeAttach();

                    $flusher->flush();
                }

                $em->remove($userBalanceHistory);
                $flusher->flush();
                $data['message'] = 'Запись удалена';
                $data['reload'] = true;
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}