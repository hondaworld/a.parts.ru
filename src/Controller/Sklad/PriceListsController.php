<?php


namespace App\Controller\Sklad;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\Sklad\Entity\PriceList\PriceListRepository;
use App\Model\Sklad\UseCase\PriceList\Create;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Sklad\PriceGroupFetcher;
use App\ReadModel\Sklad\PriceListFetcher;
use App\ReadModel\Sklad\PriceListOptFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/price-list/price-lists", name="price.list.price.lists")
 */
class PriceListsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param PriceListFetcher $fetcher
     * @return Response
     */
    public function index(PriceListFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'PriceList');

        $priceLists = $fetcher->all();

        return $this->render('app/sklads/priceLists/index.html.twig', [
            'priceLists' => $priceLists,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'PriceList');
        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $ppriceList = $handler->handle($command);
                if ($this->isGranted(StandartActionsVoter::SHOW, 'PriceList')) {
                    return $this->redirectToRoute('price.list.price.lists.show', ['id' => $ppriceList->getId()]);
                } else {
                    return $this->redirectToRoute('price.list.price.lists');
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/priceLists/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/show", name=".show")
     * @param PriceList $priceList
     * @param PriceListOptFetcher $priceListOptFetcher
     * @param OptFetcher $optFetcher
     * @param PriceGroupFetcher $priceGroupFetcher
     * @return Response
     */
    public function show(PriceList $priceList, PriceListOptFetcher $priceListOptFetcher, OptFetcher $optFetcher, PriceGroupFetcher $priceGroupFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'PriceList');

        $opts = $optFetcher->assoc();
        $profits = $priceListOptFetcher->findByPriceList($priceList);
        $priceGroups = $priceGroupFetcher->all($priceList->getId());

        return $this->render('app/sklads/priceLists/show.html.twig', [
            'priceList' => $priceList,
            'profits' => $profits,
            'opts' => $opts,
            'priceGroups' => $priceGroups,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param PriceList $priceList
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(PriceList $priceList, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'PriceList');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();

        try {
            $priceList->clearZapCards();
            $em->remove($priceList);
            $flusher->flush();
            $data['message'] = 'Прайс-лист удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param PriceListRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, PriceListRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'PriceList');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $priceList = $repository->get($request->query->getInt('id'));
            $priceList->hide();
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
     * @param PriceListRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, PriceListRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'PriceList');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $priceList = $repository->get($request->query->getInt('id'));
            $priceList->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}