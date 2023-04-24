<?php


namespace App\Controller\User;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\ExcludeProviders;
use App\Model\User\UseCase\User\ShowHidePrices;
use App\Model\User\UseCase\User\Manager;
use App\Model\User\UseCase\User\EmailPrice;
use App\Model\User\UseCase\User\Discount;
use App\Model\User\UseCase\User\Debt;
use App\Model\User\UseCase\User\Review;
use App\Model\User\UseCase\User\ApiType;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\User\Filter;
use App\Security\Voter\User\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/users", name="users")
 */
class UserDopController extends AbstractController
{
    /**
     * @Route("/{id}/exclude-providers", name=".excludeProviders")
     * @param User $user
     * @param Request $request
     * @param ExcludeProviders\Handler $handler
     * @param ProviderFetcher $providerFetcher
     * @return Response
     */
    public function excludeProviders(User $user, Request $request, ExcludeProviders\Handler $handler, ProviderFetcher $providerFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $providersList = $providerFetcher->allExisting();

        $command = ExcludeProviders\Command::fromEntity($user, $providersList);

        $form = $this->createForm(ExcludeProviders\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'excludeProviders',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/show-hide-prices", name=".showHidePrices")
     * @param User $user
     * @param Request $request
     * @param ShowHidePrices\Handler $handler
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     */
    public function showHidePrices(User $user, Request $request, ShowHidePrices\Handler $handler, ProviderPriceFetcher $providerPriceFetcher): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $pricesList = $providerPriceFetcher->assocClientsHide();

        $command = ShowHidePrices\Command::fromEntity($user, $pricesList);

        $form = $this->createForm(ShowHidePrices\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'showHidePrices',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/manager", name=".manager")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function manager(User $user, Request $request, Manager\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = Manager\Command::fromEntity($user);

        $form = $this->createForm(Manager\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'manager',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/email-price", name=".emailPrice")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function emailPrice(User $user, Request $request, EmailPrice\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = EmailPrice\Command::fromEntity($user);

        $form = $this->createForm(EmailPrice\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'emailPrice',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/api-type", name=".apiType")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function apiType(User $user, Request $request, ApiType\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = ApiType\Command::fromEntity($user);

        $form = $this->createForm(ApiType\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'apiType',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/discount", name=".discount")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function discount(User $user, Request $request, Discount\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = Discount\Command::fromEntity($user);

        $form = $this->createForm(Discount\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'discount',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/debt", name=".debt")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function debt(User $user, Request $request, Debt\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = Debt\Command::fromEntity($user);

        $form = $this->createForm(Debt\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'debt',
            'user' => $user
        ]);
    }

    /**
     * @Route("/{id}/review", name=".review")
     * @param User $user
     * @param Request $request
     * @param Manager\Handler $handler
     * @return Response
     */
    public function review(User $user, Request $request, Review\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(UserVoter::USER_DOP, $user);

        $command = Review\Command::fromEntity($user);

        $form = $this->createForm(Review\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('users.dop', ['id' => $user->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/dop.html.twig', [
            'form' => $form->createView(),
            'edit' => 'review',
            'user' => $user
        ]);
    }
}