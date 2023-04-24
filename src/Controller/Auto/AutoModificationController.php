<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Auto\Entity\Modification\AutoModificationRepository;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\Modification\Edit;
use App\Model\Auto\UseCase\Modification\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/auto/modification", name="auto.modification")
 */
class AutoModificationController extends AbstractController
{
    /**
     * @Route("/{auto_generationID}/create", name=".create")
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(AutoGeneration $autoGeneration, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command, $autoGeneration);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoGeneration->getModel()->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/modification/create.html.twig', [
            'form' => $form->createView(),
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_generationID}/{id}/edit", name=".edit")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param AutoModification $autoModification
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoGeneration $autoGeneration, AutoModification $autoModification, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($autoModification);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoGeneration->getModel()->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/modification/edit.html.twig', [
            'form' => $form->createView(),
            'autoGeneration' => $autoGeneration,
            'autoModification' => $autoModification
        ]);
    }

    /**
     * @Route("/{auto_generationID}/{id}/delete", name=".delete")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param AutoModification $autoModification
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoGeneration $autoGeneration, AutoModification $autoModification, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($autoModification->getWorkPeriods()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить модификацию, содержащую ТО']);
            }

            $em->remove($autoModification);
            $flusher->flush();
            $data['message'] = 'Модификация удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_generationID}/hide", name=".hide")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param AutoModificationRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(AutoGeneration $autoGeneration, Request $request, AutoModificationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoModification = $repository->get($request->query->getInt('id'));
            $autoModification->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_generationID}/unHide", name=".unHide")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param AutoModificationRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(AutoGeneration $autoGeneration, Request $request, AutoModificationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoModification = $repository->get($request->query->getInt('id'));
            $autoModification->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
