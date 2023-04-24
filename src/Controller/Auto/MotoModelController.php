<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Model\Auto\Entity\MotoModel\MotoModelRepository;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\MotoModel\Edit;
use App\Model\Auto\UseCase\MotoModel\Create;
use App\Model\Auto\UseCase\MotoModel\DescriptionSpare;
use App\Model\Auto\UseCase\MotoModel\DescriptionAcs;
use App\Model\Auto\UseCase\MotoModel\DescriptionTuning;
use App\Model\Flusher;
use App\ReadModel\Auto\AutoGenerationFetcher;
use App\ReadModel\Auto\MotoModelFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\FileUploader;
use DomainException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/auto/moto-model", name="auto.moto.model")
 */
class MotoModelController extends AbstractController
{
    /**
     * @Route("/{auto_markaID}/", name="")
     * @param AutoMarka $autoMarka
     * @param MotoModelFetcher $fetcher
     * @return Response
     */
    public function index(AutoMarka $autoMarka, MotoModelFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $models = $fetcher->allByMarka($autoMarka);

        return $this->render('app/auto/motoModel/index.html.twig', [
            'models' => $models,
            'autoMarka' => $autoMarka,
            'table_checkable' => true
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/show", name=".show")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param MotoModel $motoModel
     * @return Response
     */
    public function show(AutoMarka $autoMarka, MotoModel $motoModel): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        return $this->render('app/auto/motoModel/show.html.twig', [
            'autoMarka' => $autoMarka,
            'motoModel' => $motoModel,
            'edit' => false,
        ]);
    }

    /**
     * @Route("/{auto_markaID}/create", name=".create")
     * @param AutoMarka $autoMarka
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(AutoMarka $autoMarka, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $motoModel = $handler->handle($command, $autoMarka);
                return $this->redirectToRoute('auto.moto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $motoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoModel/create.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/edit", name=".edit")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param MotoModel $motoModel
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoMarka $autoMarka, MotoModel $motoModel, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($motoModel);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $motoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoModel/name/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'motoModel' => $motoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/spare", name=".description.spare")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param MotoModel $motoModel
     * @param Request $request
     * @param DescriptionSpare\Handler $handler
     * @return Response
     */
    public function descriptionSpare(AutoMarka $autoMarka, MotoModel $motoModel, Request $request, DescriptionSpare\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionSpare\Command::fromEntity($motoModel);

        $form = $this->createForm(DescriptionSpare\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $motoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoModel/description/spare/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'motoModel' => $motoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/acs", name=".description.acs")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param MotoModel $motoModel
     * @param Request $request
     * @param DescriptionAcs\Handler $handler
     * @return Response
     */
    public function descriptionAcs(AutoMarka $autoMarka, MotoModel $motoModel, Request $request, DescriptionAcs\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionAcs\Command::fromEntity($motoModel);

        $form = $this->createForm(DescriptionAcs\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $motoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoModel/description/acs/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'motoModel' => $motoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/tuning", name=".description.tuning")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $motoModel
     * @param Request $request
     * @param DescriptionTuning\Handler $handler
     * @return Response
     */
    public function descriptionTuning(AutoMarka $autoMarka, MotoModel $motoModel, Request $request, DescriptionTuning\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionTuning\Command::fromEntity($motoModel);

        $form = $this->createForm(DescriptionTuning\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $motoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoModel/description/tuning/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'motoModel' => $motoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/delete", name=".delete")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param MotoModel $motoModel
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoMarka $autoMarka, MotoModel $motoModel, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $em->remove($motoModel);
            $flusher->flush();
            $data['message'] = 'Модель мотоцикла удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_markaID}/hide", name=".hide")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param Request $request
     * @param MotoModelRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(AutoMarka $autoMarka, Request $request, MotoModelRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $motoModel = $repository->get($request->query->getInt('id'));
            $motoModel->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_markaID}/unHide", name=".unHide")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param Request $request
     * @param MotoModelRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(AutoMarka $autoMarka, Request $request, MotoModelRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $motoModel = $repository->get($request->query->getInt('id'));
            $motoModel->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
