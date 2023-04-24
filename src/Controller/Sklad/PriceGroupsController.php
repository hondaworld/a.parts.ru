<?php


namespace App\Controller\Sklad;


use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\Provider\ProviderRepository;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;
use App\Model\Sklad\Entity\PriceList\PriceList;
use App\Model\Sklad\Entity\PriceList\PriceListRepository;
use App\Model\Sklad\UseCase\PriceGroup\Create;
use App\Model\Sklad\UseCase\PriceGroup\Edit;
use App\ReadModel\Provider\Filter;
use App\ReadModel\Sklad\PriceGroupFetcher;
use App\ReadModel\Sklad\PriceListFetcher;
use App\ReadModel\Sklad\PriceListOptFetcher;
use App\ReadModel\User\OptFetcher;
use App\Security\Voter\Sklad\PriceListVoter;
use App\Security\Voter\StandartActionsVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/price-list/price-groups", name="price.list.price.groups")
 */
class PriceGroupsController extends AbstractController
{
    /**
     * @Route("/{price_listID}/create", name=".create")
     * @param PriceList $priceList
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(PriceList $priceList, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(PriceListVoter::GROUP_CHANGE, $priceList);
        $command = new Create\Command($priceList);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.price.lists.show', ['id' => $priceList->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/priceGroups/create.html.twig', [
            'form' => $form->createView(),
            'priceList' => $priceList
        ]);
    }

    /**
     * @Route("/{price_listID}/{id}/edit", name=".edit")
     * @ParamConverter("priceList", options={"id" = "price_listID"})
     * @param PriceList $priceList
     * @param PriceListOptFetcher $priceListOptFetcher
     * @param OptFetcher $optFetcher
     * @param PriceListFetcher $priceListFetcher
     * @return Response
     */
    public function edit(PriceList $priceList, PriceGroup $priceGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(PriceListVoter::GROUP_CHANGE, $priceList);

        $command = Edit\Command::fromEntity($priceGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('price.list.price.lists.show', ['id' => $priceList->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/sklads/priceGroups/edit.html.twig', [
            'form' => $form->createView(),
            'priceGroup' => $priceGroup,
            'priceList' => $priceList
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param PriceGroup $priceGroup
     * @param Request $request
     * @param PriceListRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(PriceGroup $priceGroup, Request $request, PriceListRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(PriceListVoter::GROUP_CHANGE, $priceGroup->getPriceList());
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();

        try {
            $priceGroup->clearZapCards();
            $em->remove($priceGroup);
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
     * @param PriceGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, PriceGroupRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $priceGroup = $repository->get($request->query->getInt('id'));

            try {
                $this->denyAccessUnlessGranted(PriceListVoter::GROUP_CHANGE, $priceGroup->getPriceList());
            } catch (AccessDeniedException $e) {
                return $this->json(['code' => 403, 'message' => $e->getMessage()]);
            }

            $priceGroup->hide();
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
     * @param PriceGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, PriceGroupRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $priceGroup = $repository->get($request->query->getInt('id'));

            try {
                $this->denyAccessUnlessGranted(PriceListVoter::GROUP_CHANGE, $priceGroup->getPriceList());
            } catch (AccessDeniedException $e) {
                return $this->json(['code' => 403, 'message' => $e->getMessage()]);
            }

            $priceGroup->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}