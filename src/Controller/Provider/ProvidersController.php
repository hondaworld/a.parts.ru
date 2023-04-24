<?php


namespace App\Controller\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Provider\Entity\Opt\ProviderPriceOptRepository;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\UserRepository;
use App\Model\Provider\UseCase\Provider\Create;
use App\ReadModel\Provider\ProviderFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceOptFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers", name="providers")
 */
class ProvidersController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ProviderFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, ProviderFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'Provider');

        $settings = $settings->get('providers');

        $filter = new Filter\Provider\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Provider\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/providers/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @param ZapSkladRepository $zapSkladRepository
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler, ZapSkladRepository $zapSkladRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'Provider');
        $command = new Create\Command($zapSkladRepository);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $provider = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'Provider')) {
                    return $this->redirectToRoute('providers.show', ['id' => $provider->getId()]);
                } else {
                    return $this->redirectToRoute('providers', ['page' => $request->getSession()->get('page/providers') ?: 1]);
                }
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param Provider $provider
     * @param ProviderPriceOptFetcher $providerPriceOptFetcher
     * @param OptFetcher $optFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function show(Provider $provider, ProviderPriceOptFetcher $providerPriceOptFetcher, OptFetcher $optFetcher, ProviderPriceFetcher $providerPriceFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'Provider');

        $opts = $optFetcher->assoc();
        $providerPrices = $providerPriceFetcher->assocByProvider($provider);
        $profits = $providerPriceOptFetcher->findByProvider($provider);

        return $this->render('app/providers/show.html.twig', [
            'provider' => $provider,
            'profits' => $profits,
            'opts' => $opts,
            'providerPrices' => $providerPrices,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param Provider $provider
     * @param Request $request
     * @param ProviderRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(Provider $provider, Request $request, ProviderRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'Provider');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        if (count($provider->getPrices()) > 0) {
            return $this->json(['code' => 500, 'message' => 'Невозможно удалить поставщика, содержащего прайс-листы']);
        } else {
            try {
                $em->remove($provider);
                $flusher->flush();
                $data['message'] = 'Поставщик удален';

            } catch (EntityNotFoundException $e) {
                return $this->json(['code' => 404, 'message' => $e->getMessage()]);
            }
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ProviderRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ProviderRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $provider = $repository->get($request->query->getInt('id'));
            $provider->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/unHide", name=".unHide")
     * @param Request $request
     * @param ProviderRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ProviderRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $provider = $repository->get($request->query->getInt('id'));
            $provider->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}