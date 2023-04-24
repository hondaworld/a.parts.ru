<?php

namespace App\Controller\User;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\ShopPayType\ShopPayType;
use App\Model\User\Entity\ShopPayType\ShopPayTypeRepository;
use App\Model\User\UseCase\ShopPayType\Edit;
use App\Model\User\UseCase\ShopPayType\Create;
use App\Model\Flusher;
use App\ReadModel\User\ShopPayTypeFetcher;
use App\Security\Voter\StandartActionsVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/shop/pay-types", name="shop.pay.types")
 */
class ShopPayTypesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param ShopPayTypeFetcher $fetcher
     * @return Response
     */
    public function index(ShopPayTypeFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopPayType');

        $payTypes = $fetcher->all();

        return $this->render('app/users/payTypes/index.html.twig', [
            'payTypes' => $payTypes,
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopPayType');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.pay.types');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/payTypes/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param ShopPayType $payType
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ShopPayType $payType, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopPayType');

        $command = Edit\Command::fromEntity($payType);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('shop.pay.types');
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/users/payTypes/edit.html.twig', [
            'form' => $form->createView(),
            'payType' => $payType
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ShopPayTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function delete(int $id, ShopPayTypeRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ShopPayType');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $shopPayType = $repository->get($id);

            if (count($shopPayType->getUsers()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить метод оплаты, используемый у клиентов']);
            }

            $em->remove($shopPayType);
            $flusher->flush();
            $data['message'] = 'Метод оплаты клиентов удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ShopPayTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function hide(Request $request, ShopPayTypeRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopPayType = $repository->get($request->query->getInt('id'));
            $shopPayType->hide();
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
     * @param ShopPayTypeRepository $repository
     * @param Flusher $flusher
     * @return Response
     * @throws \Doctrine\ORM\EntityNotFoundException
     */
    public function unHide(Request $request, ShopPayTypeRepository $repository, Flusher $flusher): Response
    {
        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $shopPayType = $repository->get($request->query->getInt('id'));
            $shopPayType->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
