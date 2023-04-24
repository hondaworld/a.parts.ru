<?php


namespace App\Controller\User;


use App\Model\User\Entity\User\User;
use App\Model\User\Entity\User\UserRepository;
use App\Model\User\UseCase\User\Cashier;
use App\Model\User\UseCase\User\CashierFirmContr;
use App\Model\User\UseCase\User\CashierSchetFak;
use App\Model\User\UseCase\User\Getter;
use App\Model\User\UseCase\User\GetterFirmContr;
use App\Model\User\UseCase\User\Price;
use App\ReadModel\Beznal\BeznalFetcher;
use App\ReadModel\Contact\ContactFetcher;
use App\ReadModel\User\Filter;
use App\Security\Voter\User\UserVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/users", name="users")
 */
class UserSettingsController extends AbstractController
{
    /**
     * @Route("/{id}/cashier", name=".cashier")
     * @param User $user
     * @param Request $request
     * @param Cashier\Handler $handler
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @return Response
     */
    public function cashier(User $user, Request $request, Cashier\Handler $handler, UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['user']['id']) {
            $cash_userID = ($request->get('form')['user']['id']);
            $contacts = $contactFetcher->assocAllByUser($userRepository->get($cash_userID));
            $beznals = $beznalFetcher->assocAllByUser($userRepository->get($cash_userID));
        } else if ($user->getCashUser()) {
            $contacts = $contactFetcher->assocAllByUser($user->getCashUser());
            $beznals = $beznalFetcher->assocAllByUser($user->getCashUser());
        }

        $command = Cashier\Command::fromEntity($user, $contacts, $beznals);

        $form = $this->createForm(Cashier\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashier',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/cashier-firmcontr", name=".cashierFirmContr")
     * @param User $user
     * @param Request $request
     * @param CashierFirmContr\Handler $handler
     * @return Response
     */
    public function cashierFirmContr(User $user, Request $request, CashierFirmContr\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $command = CashierFirmContr\Command::fromEntity($user);

        $form = $this->createForm(CashierFirmContr\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashierFirmContr',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/cashier-schet-fak", name=".cashierSchetFak")
     * @param User $user
     * @param Request $request
     * @param CashierSchetFak\Handler $handler
     * @return Response
     */
    public function cashierSchetFak(User $user, Request $request, CashierSchetFak\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $command = CashierSchetFak\Command::fromEntity($user);

        $form = $this->createForm(CashierSchetFak\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'cashierSchetFak',
            'user' => $user
        ]);
    }
    /**
     * @Route("/{id}/getter", name=".getter")
     * @param User $user
     * @param Request $request
     * @param Getter\Handler $handler
     * @param UserRepository $userRepository
     * @param ContactFetcher $contactFetcher
     * @param BeznalFetcher $beznalFetcher
     * @return Response
     */
    public function getter(User $user, Request $request, Getter\Handler $handler, UserRepository $userRepository, ContactFetcher $contactFetcher, BeznalFetcher $beznalFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $contacts = [];
        $beznals = [];
        if ($request->get('form') && $request->get('form')['user']['id']) {
            $gruz_userID = ($request->get('form')['user']['id']);
            $contacts = $contactFetcher->assocAllByUser($userRepository->get($gruz_userID));
            $beznals = $beznalFetcher->assocAllByUser($userRepository->get($gruz_userID));
        } else if ($user->getGruzUser()) {
            $contacts = $contactFetcher->assocAllByUser($user->getGruzUser());
            $beznals = $beznalFetcher->assocAllByUser($user->getGruzUser());
        }

        $command = Getter\Command::fromEntity($user, $contacts, $beznals);

        $form = $this->createForm(Getter\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'getter',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/getter-firmcontr", name=".getterFirmContr")
     * @param User $user
     * @param Request $request
     * @param GetterFirmContr\Handler $handler
     * @return Response
     */
    public function getterFirmContr(User $user, Request $request, GetterFirmContr\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $command = GetterFirmContr\Command::fromEntity($user);

        $form = $this->createForm(GetterFirmContr\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'getterFirmContr',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/price", name=".price")
     * @param User $user
     * @param Request $request
     * @param Price\Handler $handler
     * @return Response
     */
    public function price(User $user, Request $request, Price\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_SETTINGS, $user);

        $command = Price\Command::fromEntity($user);

        $form = $this->createForm(Price\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.settings', ['id' => $user->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/settings.html.twig', [
            'form' => $form->createView(),
            'edit' => 'price',
            'user' => $user
        ]);
    }
}