<?php


namespace App\Controller\Detail;


use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExcludeRepository;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\Detail\Entity\ProviderExclude\DetailProviderExcludeRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Detail\UseCase\PriceExclude\Create;
use App\ReadModel\Detail\DetailProviderPriceExcludeFetcher;
use App\ReadModel\Detail\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/exclude/prices", name="exclude.prices")
 */
class ExcludePricesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param Request $request
     * @param DetailProviderPriceExcludeFetcher $fetcher
     * @param ManagerSettings $settings
     * @return Response
     */
    public function index(Request $request, DetailProviderPriceExcludeFetcher $fetcher, ManagerSettings $settings): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'DetailProviderPriceExclude');

        $settings = $settings->get('priceExclude');

        $filter = new Filter\PriceExclude\Filter();
        $filter->inPage = isset($settings['inPage']) ? $settings['inPage'] : $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\PriceExclude\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        return $this->render('app/detail/priceExclude/index.html.twig', [
            'pagination' => $pagination,
            'filter' => $form->createView(),
            'table_checkable' => true,
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'DetailProviderPriceExclude');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('exclude.prices', ['page' => $request->getSession()->get('page/priceExclude') ?: 1]);

            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/detail/priceExclude/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param DetailProviderPriceExclude $detailProviderPriceExclude
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(DetailProviderPriceExclude $detailProviderPriceExclude, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DetailProviderPriceExclude');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($detailProviderPriceExclude);
            $flusher->flush();
            $data['message'] = 'Закрытый прайс удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/deleteSelected", name=".deleteSelected")
     * @param Request $request
     * @param DetailProviderPriceExcludeRepository $detailProviderPriceExcludeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function deleteSelected(Request $request, DetailProviderPriceExcludeRepository $detailProviderPriceExcludeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'DetailProviderPriceExclude');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $detailProviderPriceExclude = $detailProviderPriceExcludeRepository->get($request->query->getInt('id'));
            $em->remove($detailProviderPriceExclude);
            $flusher->flush();
            $data['action'] = 'delete';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}