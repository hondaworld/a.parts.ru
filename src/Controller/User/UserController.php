<?php


namespace App\Controller\User;


use App\Model\Flusher;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\PhoneMobile;
use App\Model\User\UseCase\User\Password;
use App\Model\User\UseCase\User\Name;
use App\Model\User\UseCase\User\Email;
use App\Model\User\UseCase\User\Dop;
use App\Model\User\UseCase\User\Opt;
use App\Model\User\UseCase\User\Ur;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\User\Filter;
use App\ReadModel\User\UserEmailStatusFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Security\Voter\User\UserVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users", name="users")
 */
class UserController extends AbstractController
{
    /**
     * @Route("/{id}/phone-mobile", name=".phoneMobile")
     * @param User $user
     * @param Request $request
     * @param PhoneMobile\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function phoneMobile(User $user, Request $request, PhoneMobile\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = PhoneMobile\Command::fromEntity($user);

        $form = $this->createForm(PhoneMobile\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'phoneModile',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/password", name=".password")
     * @param User $user
     * @param Request $request
     * @param Password\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function password(User $user, Request $request, Password\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = new Password\Command($user->getId());

        $form = $this->createForm(Password\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Пароль изменен');
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'password',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/name", name=".name")
     * @param User $user
     * @param Request $request
     * @param Name\Handler $handler
     * @param TownFetcher $townFetcher
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function name(User $user, Request $request, Name\Handler $handler, TownFetcher $townFetcher, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = Name\Command::fromEntity($user, $townFetcher);

        $form = $this->createForm(Name\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'name',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/email", name=".email")
     * @param User $user
     * @param Request $request
     * @param Email\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function email(User $user, Request $request, Email\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = Email\Command::fromEntity($user, $emailStatusFetcher);

        $form = $this->createForm(Email\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'email',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/dop-data", name=".dopData")
     * @param User $user
     * @param Request $request
     * @param Dop\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function dopData(User $user, Request $request, Dop\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = Dop\Command::fromEntity($user);

        $form = $this->createForm(Dop\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'dop',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/opt", name=".opt")
     * @param User $user
     * @param Request $request
     * @param Opt\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function opt(User $user, Request $request, Opt\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_OPT_CHANGE, $user);

        $command = Opt\Command::fromEntity($user);

        $form = $this->createForm(Opt\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'opt',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/ur", name=".ur")
     * @param User $user
     * @param Request $request
     * @param Ur\Handler $handler
     * @param UserEmailStatusFetcher $emailStatusFetcher
     * @return Response
     */
    public function ur(User $user, Request $request, Ur\Handler $handler, UserEmailStatusFetcher $emailStatusFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'User');

        $command = Ur\Command::fromEntity($user);

        $form = $this->createForm(Ur\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'ur',
            'emailStatuses' => $emailStatusFetcher->assoc(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/activate", name=".activate")
     * @param User $user
     * @param Flusher $flusher
     * @return Response
     */
    public function activate(User $user, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'User');

        $user->activate();

        $flusher->flush();

        return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
    }

    /**
     * @Route("/{id}/hide", name=".hide")
     * @param User $user
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(User $user, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'User');

        $user->hide();

        $flusher->flush();

        return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
    }

    /**
     * @Route("/{id}/unHide", name=".unHide")
     * @param User $user
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(User $user, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'User');

        $user->unhide();

        $flusher->flush();

        return $this->redirectToRoute('users.show', ['id' => $user->getId()]);
    }
}