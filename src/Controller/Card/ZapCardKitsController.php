<?php

namespace App\Controller\Card;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\Detail\Entity\Kit\ZapCardKitRepository;
use App\Model\EntityNotFoundException;
use App\Model\Detail\UseCase\Kit\Edit;
use App\Model\Detail\UseCase\Kit\Create;
use App\Model\Detail\UseCase\Kit\Copy;
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
 * @Route("/card/kits", name="card.kits")
 */
class ZapCardKitsController extends AbstractController
{

    /**
     * @Route("/{id}/sort", name=".sort")
     * @param int $id
     * @param Request $request
     * @param ZapCardKitRepository $repository
     * @param Flusher $flusher
     * @param ModelSorter $sorter
     * @return Response
     */
    public function sort(int $id, Request $request, ZapCardKitRepository $repository, Flusher $flusher, ModelSorter $sorter): Response
    {
        $data = ['code' => 200, 'message' => ''];

        try {
            $kit = $repository->get($id);

            $oldSort = $kit->getSort();
            $newSort = $sorter->getNewSort($kit->getId(), $oldSort);

            if ($newSort != $oldSort) {
                $repository->removeSort($kit->getAutoModel(), $oldSort);
                $repository->addSort($kit->getAutoModel(), $newSort);

                $kit->changeSort($newSort);
                $flusher->flush();
            }

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modelID}/create", name=".create")
     * @param AutoModel $autoModel
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(AutoModel $autoModel, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $autoModel);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoModel->getMarka()->getId(), 'id' => $autoModel->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/kit/create.html.twig', [
            'autoModel' => $autoModel,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/copy", name=".copy")
     * @param ZapCardKit $zapCardKit
     * @param Request $request
     * @param Copy\Handler $handler
     * @return Response
     */
    public function copy(ZapCardKit $zapCardKit, Request $request, Copy\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $autoModel = $zapCardKit->getAutoModel();
        $command = new Copy\Command($zapCardKit);

        $form = $this->createForm(Copy\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoModel->getMarka()->getId(), 'id' => $autoModel->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/kit/copy.html.twig', [
            'autoModel' => $autoModel,
            'kit' => $zapCardKit,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/edit", name=".edit")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param ZapCardKit $zapCardKit
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoModel $autoModel, ZapCardKit $zapCardKit, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($zapCardKit);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoModel->getMarka()->getId(), 'id' => $autoModel->getId()]);
            } catch (\DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/kit/edit.html.twig', [
            'autoModel' => $autoModel,
            'form' => $form->createView(),
            'kit' => $zapCardKit
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param ZapCardKitRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, ZapCardKitRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $zapCardKit = $repository->get($id);
            $repository->removeSort($zapCardKit->getAutoModel(), $zapCardKit->getSort());
            $em->remove($zapCardKit);
            $flusher->flush();
            $data['message'] = 'Комплект ЗЧ удален';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param ZapCardKitRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, ZapCardKitRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardKit = $repository->get($request->query->getInt('id'));
            $zapCardKit->hide();
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
     * @param ZapCardKitRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, ZapCardKitRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $zapCardKit = $repository->get($request->query->getInt('id'));
            $zapCardKit->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
