<?php


namespace App\Controller\Provider;


use App\Model\Finance\Entity\Currency\CurrencyRepository;
use App\Model\Provider\UseCase\Provider\Edit;
use App\Model\Provider\UseCase\Provider\Email;
use App\Model\Provider\UseCase\Provider\Send;
use App\Model\Provider\UseCase\Provider\Opt;
use App\Model\Provider\UseCase\Provider\PriceCurrency;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceOptFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\Provider\ProviderVoter;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers", name="providers")
 */
class ProviderController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name=".edit")
     * @param Provider $provider
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(Provider $provider, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Provider');

        $command = Edit\Command::fromEntity($provider);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'main',
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{id}/email", name=".email")
     * @param Provider $provider
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function email(Provider $provider, Request $request, Email\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Provider');

        $command = Email\Command::fromEntity($provider);

        $form = $this->createForm(Email\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'email',
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{id}/send", name=".send")
     * @param Provider $provider
     * @param Request $request
     * @param Send\Handler $handler
     * @return Response
     */
    public function send(Provider $provider, Request $request, Send\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Provider');

        $command = Send\Command::fromEntity($provider);

        $form = $this->createForm(Send\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'send',
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{id}/opt", name=".opt")
     * @param Provider $provider
     * @param Request $request
     * @param Opt\Handler $handler
     * @param ProviderPriceOptFetcher $providerPriceOptFetcher
     * @param OptFetcher $optFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     */
    public function opt(Provider $provider, Request $request, Opt\Handler $handler, ProviderPriceOptFetcher $providerPriceOptFetcher, OptFetcher $optFetcher, ProviderPriceFetcher $providerPriceFetcher): Response
    {
        $this->denyAccessUnlessGranted(ProviderVoter::PROVIDER_OPT_CHANGE, $provider);

        $opts = $optFetcher->assoc();
        $providerPrices = $providerPriceFetcher->assocByProvider($provider);
        $profits = $providerPriceOptFetcher->findByProvider($provider);

        $command = Opt\Command::fromEntity($provider, $opts, $providerPrices, $profits);

        $form = $this->createForm(Opt\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'opt',
            'profits' => $profits,
            'opts' => $opts,
            'providerPrices' => $providerPrices,
            'provider' => $provider
        ]);
    }

    /**
     * @Route("/{id}/priceCurrency", name=".priceCurrency")
     * @param Provider $provider
     * @param Request $request
     * @param Send\Handler $handler
     * @return Response
     */
    public function priceCurrency(Provider $provider, Request $request, PriceCurrency\Handler $handler, CurrencyRepository $currencyRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'Provider');

        $command = PriceCurrency\Command::fromEntity($provider, $currencyRepository);

        $form = $this->createForm(PriceCurrency\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                $this->addFlash('success', 'Коэффициенты прайс-листов изменены');
                return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/show.html.twig', [
            'form' => $form->createView(),
            'edit' => 'priceCurrency',
            'provider' => $provider
        ]);
    }
}