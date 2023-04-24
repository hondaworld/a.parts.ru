<?php

namespace App\Controller\Shop;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Shop\Entity\ShopType\ShopType;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;
use App\Model\Shop\UseCase\ShopType\Create;
use App\Model\Shop\UseCase\ShopType\Edit;
use App\ReadModel\Shop\ShopTypeFetcher;
use \App\Security\Voter\StandartActionsVoter;
use DomainException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/types", name="shop.types")
 */
class ShopTypesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ShopTypeFetcher $fetcher
     * @return Response
     */
    public function index(ShopTypeFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopType');

        $shopTypes = $fetcher->all();

        return $this->render('app/shop/shopTypes/index.html.twig', [
            'shopTypes' => $shopTypes,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'ShopType');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.types');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopTypes/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ShopType $shopType
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ShopType $shopType, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ShopType');

        $command = Edit\Command::fromDocument($shopType);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.types');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/shop/shopTypes/edit.html.twig', [
            'form' => $form->createView(),
            'shopType' => $shopType,
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ShopTypeRepository $shopTypeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ShopTypeRepository $shopTypeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'ShopType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopType = $shopTypeRepository->get($id);
            if (count($shopType->getZapCards()) > 0 || $shopType->isNoneDelete()) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить вид товаров, прикрепленный к деталям']);
            } else {
                $em->remove($shopType);
                $flusher->flush();
                $data['message'] = 'Вид товаров удален';
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ShopTypeRepository $shopTypeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ShopTypeRepository $shopTypeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::HIDE, 'ShopType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopType = $shopTypeRepository->get($request->query->getInt('id'));
            $shopType->hide();
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
     * @param ShopTypeRepository $shopTypeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ShopTypeRepository $shopTypeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::UNHIDE, 'ShopType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopType = $shopTypeRepository->get($request->query->getInt('id'));
            $shopType->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
