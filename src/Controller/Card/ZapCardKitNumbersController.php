<?php

namespace App\Controller\Card;

use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumber;
use App\Model\Detail\Entity\KitNumber\ZapCardKitNumberRepository;
use App\Model\EntityNotFoundException;
use App\Model\Detail\UseCase\KitNumber\Edit;
use App\Model\Detail\UseCase\KitNumber\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\ModelSorter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/kit/numbers", name="card.kit.numbers")
 */
class ZapCardKitNumbersController extends AbstractController
{
    /**
     * @Route("/{kitID}/", name="")
     * @ParamConverter("zapCardKit", options={"id" = "kitID"})
     * @param ZapCardKit $zapCardKit
     * @return Response
     */
    public function index(ZapCardKit $zapCardKit): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');


        return $this->render('app/auto/kitNumber/index.html.twig', [
            'table_checkable' => true,
            'table_sortable' => true,
            'kit' => $zapCardKit,
        ]);
    }

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param Request $request
     * @param ZapCardKitNumberRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, Request $request, ZapCardKitNumberRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $kitNumber = $repository->get($id);

            $oldSort = $kitNumber->getSort();
            $newSort = $sorter->getNewSort($kitNumber->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $repository->removeSort($kitNumber->getKit(), $oldSort);
                $repository->addSort($kitNumber->getKit(), $newSort);

                $kitNumber->changeSort($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{kitID}/create", name=".create")
     * @ParamConverter("zapCardKit", options={"id" = "kitID"})
     * @param ZapCardKit $zapCardKit
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(ZapCardKit $zapCardKit, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $zapCardKit);
                return $this->redirectToRoute('card.kit.numbers', ['kitID' => $zapCardKit->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/kitNumber/create.html.twig', [
            'kit' => $zapCardKit,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{kitID}/{id}/edit", name=".edit")
     * @ParamConverter("zapCardKit", options={"id" = "kitID"})
     * @param ZapCardKit $zapCardKit
     * @param ZapCardKitNumber $zapCardKitNumber
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCardKit $zapCardKit, ZapCardKitNumber $zapCardKitNumber, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($zapCardKitNumber);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.kit.numbers', ['kitID' => $zapCardKit->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/kitNumber/edit.html.twig', [
            'kit' => $zapCardKit,
            'form' => $form->createView(),
            'number' => $zapCardKitNumber
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ZapCardKitNumberRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ZapCardKitNumberRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $zapCardKitNumber = $repository->get($id);
            $repository->removeSort($zapCardKitNumber->getKit(), $zapCardKitNumber->getSort());
            $em->remove($zapCardKitNumber);
            $flusher->flush();
            $data['message'] = 'Деталь удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{kitID}/hide", name=".hide")
     * @ParamConverter("zapCardKit", options={"id" = "kitID"})
     * @param Request $request
     * @param ZapCardKitNumberRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ZapCardKitNumberRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardKitNumber = $repository->get($request->query->getInt('id'));
            $zapCardKitNumber->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{kitID}/unHide", name=".unHide")
     * @ParamConverter("zapCardKit", options={"id" = "kitID"})
     * @param Request $request
     * @param ZapCardKitNumberRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ZapCardKitNumberRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardKitNumber = $repository->get($request->query->getInt('id'));
            $zapCardKitNumber->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
