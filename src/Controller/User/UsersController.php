<?php


namespace App\Controller\User;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Order\UseCase\Good\ReserveFromMail;
use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\UseCase\User\Create;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserEmailStatusFetcher;
use App\ReadModel\User\UserFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Security\Voter\User\UserVoter;
use App\Service\Email\EmailOrder;
use App\Service\Email\EmailSender;
use App\Service\ManagerSettings;
use DomainException;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users", name="users")
 */
class UsersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param UserFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, UserFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'User');

        $settings = $settings->get('users');

        $filter = new Filter\User\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\User\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/users/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @param TownFetcher $townFetcher
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler, TownFetcher $townFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'User');
        $command = new Create\Command($townFetcher);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'User')) {
                    return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
                } else {
                    return $this->redirectToRoute('users', ['page' => $request->getSession()->get('page/users') ?: 1]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param User $user
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function show(User $user, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'User');
//
//        try {
//            $user->
//        }

        return $this->render('app/users/show.html.twig', [
            'user' => $user,
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/dop", name=".dop")
     * @param User $user
     * @return Response
     */
    public function dop(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        return $this->render('app/users/dop.html.twig', [
            'user' => $user,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/settings", name=".settings")
     * @param User $user
     * @return Response
     */
    public function settings(User $user): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        return $this->render('app/users/settings.html.twig', [
            'user' => $user,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param UserRepository $users
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, UserRepository $users, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'User');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }
        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $user = $users->get($id);
            if (count($user->getOrders()) > 0) {
                $data = ['code' => 500, 'message' => 'У клиента есть заказы'];
            } else {
                $user->clearCashUsers();
                $user->clearGruzUsers();
                $em->remove($user);
                $flusher->flush();
                $data['message'] = 'Клиент удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

//    /**
//     * @Route("/email", name=".email")
//     * @param Imap $imap
//     * @param UserRepository $userRepository
//     * @param EmailSender $emailSender
//     * @param CreaterFetcher $createrFetcher
//     * @return Response
//     */
//    public function email(Imap $imap, UserRepository $userRepository, EmailSender $emailSender, CreaterFetcher $createrFetcher, ReserveFromMail\Handler $handler): Response
//    {
//        $emailOrder = new EmailOrder($imap, $userRepository, $createrFetcher, $handler, $emailSender);
//        $emailOrder->saveAttachments($this->getParameter('price_directory'));
//
//        return $this->render('app/home.html.twig', ['news' => []]);
//    }
}