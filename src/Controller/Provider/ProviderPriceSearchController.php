<?php


namespace App\Controller\Provider;


use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Provider\UseCase\Search\Create;
use App\Model\Provider\UseCase\Search\Create\Handler;
use App\Model\Provider\UseCase\Search\Edit;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderPriceSearchFetcher;
use App\Security\Voter\StandartActionsVoter;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/providers/prices/search", name="providers.prices.search")
 */
class ProviderPriceSearchController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param ProviderPriceSearchFetcher $fetcher
     * @param WeightFetcher $weightFetcher
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, ProviderPriceSearchFetcher $fetcher, WeightFetcher $weightFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceSearch');

        $filter = new Filter\Search\Filter();

        $form = $this->createForm(Filter\Search\Form::class, $filter);
        $form->handleRequest($request);

        $all = $fetcher->all($filter, $weightFetcher);

        return $this->render('app/providers/search/index.html.twig', [
            'all' => $all,
            'filter' => $form->createView(),
        ]);
    }

    /**
     * @Route("/createSearch", name=".createSearch")
     * @param Request $request
     * @param Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ProviderPriceSearch');

        $number = $request->get('number');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('providers.prices.search', ['form' => ['number' => $command->number]]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/search/create.html.twig', [
            'form' => $form->createView(),
            'number' => $number,
        ]);
    }

    /**
     * @Route("/editSearch", name=".editSearch")
     * @param Request $request
     * @param Edit\Handler $handler
     * @param CreaterRepository $createrRepository
     * @param ProviderPriceRepository $providerPriceRepository
     * @param PriceUploaderFetcher $priceUploaderFetcher
     * @return Response
     */
    public function edit(Request $request, Edit\Handler $handler, CreaterRepository $createrRepository, ProviderPriceRepository $providerPriceRepository, PriceUploaderFetcher $priceUploaderFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ProviderPriceSearch');

        $providerPriceID = $request->get('providerPriceID');
        $createrID = $request->get('createrID');
        $number = $request->get('number');

        $creater = $createrRepository->get($createrID);
        $providerPrice = $providerPriceRepository->get($providerPriceID);

        $data = $priceUploaderFetcher->findPriceNumber($creater->getTableName(), $number, $providerPriceID, $createrID);
        if (count($data) == 0) {
            return $this->redirectToRoute('providers.prices.search', ['form' => ['number' => $number]]);
        }

        $command = new Edit\Command($data[0]['price'], $data[0]['quantity']);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $number, $providerPrice, $creater);
                return $this->redirectToRoute('providers.prices.search', ['form' => ['number' => $number]]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/search/edit.html.twig', [
            'form' => $form->createView(),
            'creater' => $creater,
            'providerPrice' => $providerPrice,
            'number' => $number,
        ]);
    }

    /**
     * @Route("/deleteSearch", name=".deleteSearch")
     * @param Request $request
     * @param CreaterRepository $createrRepository
     * @param Flusher $flusher
     * @param PriceUploaderFetcher $priceUploaderFetcher
     * @return Response
     */
    public function delete(Request $request, CreaterRepository $createrRepository, Flusher $flusher, PriceUploaderFetcher $priceUploaderFetcher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ProviderPriceSearch');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $providerPriceID = $request->get('providerPriceID');
        $createrID = $request->get('createrID');
        $number = $request->get('number');

        $creater = $createrRepository->get($createrID);

        $data = ['code' => 200, 'message' => ''];

        try {
            $priceUploaderFetcher->deletePrice($creater->getTableName(), $providerPriceID, $createrID, $number);
            $flusher->flush();
            $data['message'] = 'Деталь удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}