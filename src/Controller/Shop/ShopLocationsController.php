<?php

namespace App\Controller\Shop;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\Location\ShopLocation;
use App\Model\Shop\Entity\Location\ShopLocationRepository;
use App\ReadModel\Shop\Filter;
use App\Model\Shop\UseCase\ShopLocation\Create;
use App\Model\Shop\UseCase\ShopLocation\Edit;
use App\ReadModel\Shop\ShopLocationFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\ReadModel\Sklad\ZapSkladLocationFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ManagerSettings;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/locations", name="shop.locations")
 */
class ShopLocationsController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ShopLocationFetcher $fetcher
     * @param Request $request
     * @param ManagerSettings $settings
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param ZapSkladLocationFetcher $zapSkladLocationFetcher
     * @return Response
     * @throws Exception
     */
    public function index(ShopLocationFetcher $fetcher, Request $request, ManagerSettings $settings, ZapSkladFetcher $zapSkladFetcher, ZapSkladLocationFetcher $zapSkladLocationFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopLocation');

        $settings = $settings->get('shopLocation');

        $filter = new Filter\Location\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\Location\Form::class, $filter);
        $form->handleRequest($request);


        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $sklads = $zapSkladFetcher->assoc();

        $items = $pagination->getItems();
        foreach ($items as &$item) {
            $numbers = $zapSkladLocationFetcher->findNumbersByLocation($item['locationID']);
            foreach ($numbers as $number) {
                $item['numbers'][$number['zapSkladID']][] = [
                    'zapCardID' => $number['zapCardID'],
                    'number' => $number['number'],
                ];
            }
        }
        $pagination->setItems($items);

        return $this->render('app/shop/shopLocation/index.html.twig', [
            'pagination' => $pagination,
            'sklads' => $sklads,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopLocation');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.locations');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopLocation/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ShopLocation $shopLocation
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ShopLocation $shopLocation, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ShopLocation');

        $command = Edit\Command::fromDocument($shopLocation);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.locations');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopLocation/edit.html.twig', [
            'form' => $form->createView(),
            'shopLocation' => $shopLocation,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ShopLocationRepository $shopLocationRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ShopLocationRepository $shopLocationRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopLocation');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopLocation = $shopLocationRepository->get($id);

            if (count($shopLocation->getZapSkladLocations()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить ячейку, привязанную к деталям']);
            }

            $em->remove($shopLocation);
            $flusher->flush();
            $data['message'] = 'Складская ячейка удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ShopLocationRepository $shopLocationRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ShopLocationRepository $shopLocationRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'ShopLocation');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopLocation = $shopLocationRepository->get($request->query->getInt('id'));
            $shopLocation->hide();
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
     * @param ShopLocationRepository $shopLocationRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ShopLocationRepository $shopLocationRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'ShopLocation');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopLocation = $shopLocationRepository->get($request->query->getInt('id'));
            $shopLocation->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
