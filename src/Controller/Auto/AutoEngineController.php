<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Engine\AutoEngine;
use App\Model\Auto\Entity\Engine\AutoEngineRepository;
use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\Engine\Edit;
use App\Model\Auto\UseCase\Engine\Create;
use App\Model\Flusher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\Converter\CharsConverter;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/auto/engine", name="auto.engine")
 */
class AutoEngineController extends AbstractController
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

        return $this->render('app/auto/engine/create.html.twig', [
            'form' => $form->createView(),
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_generationID}/{id}/edit", name=".edit")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param AutoEngine $autoEngine
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoGeneration $autoGeneration, AutoEngine $autoEngine, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($autoEngine);

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

        return $this->render('app/auto/engine/edit.html.twig', [
            'form' => $form->createView(),
            'autoGeneration' => $autoGeneration,
            'autoEngine' => $autoEngine
        ]);
    }

    /**
     * @Route("/{auto_generationID}/{id}/delete", name=".delete")
     * @ParamConverter("autoGeneration", options={"id" = "auto_generationID"})
     * @param AutoGeneration $autoGeneration
     * @param AutoEngine $autoEngine
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoGeneration $autoGeneration, AutoEngine $autoEngine, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($autoEngine);
            $flusher->flush();
            $data['message'] = 'Двигатель удален';

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
     * @param AutoEngineRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(AutoGeneration $autoGeneration, Request $request, AutoEngineRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoEngine = $repository->get($request->query->getInt('id'));
            $autoEngine->hide();
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
     * @param AutoEngineRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(AutoGeneration $autoGeneration, Request $request, AutoEngineRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoEngine = $repository->get($request->query->getInt('id'));
            $autoEngine->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
