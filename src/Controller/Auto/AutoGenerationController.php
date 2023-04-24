<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Generation\AutoGenerationRepository;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\Generation\Edit;
use App\Model\Auto\UseCase\Generation\Create;
use App\Model\Auto\UseCase\Generation\Photo;
use App\Model\Auto\UseCase\Generation\DescriptionSpare;
use App\Model\Auto\UseCase\Generation\DescriptionAcs;
use App\Model\Auto\UseCase\Generation\DescriptionTuning;
use App\Model\Flusher;
use App\ReadModel\Auto\AutoEngineFetcher;
use App\ReadModel\Auto\AutoModificationFetcher;
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
 * @Route("/auto/generation", name="auto.generation")
 */
class AutoGenerationController extends AbstractController
{
//    /**
//     * @Route("/{auto_modelID}/", name="")
//     * @param AutoModel $autoModel
//     * @param AutoModelFetcher $fetcher
//     * @return Response
//     */
//    public function index(AutoModel $autoModel, AutoModelFetcher $fetcher): Response
//    {
//        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
//
//        $models = $fetcher->allByMarka($autoMarka);
//
//        return $this->render('app/auto/model/index.html.twig', [
//            'models' => $models,
//            'autoMarka' => $autoMarka,
//            'auto_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/'
//        ]);
//    }

    /**
     * @Route("/{auto_modelID}/{id}/show", name=".show")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param AutoModificationFetcher $autoModificationFetcher
     * @param AutoEngineFetcher $autoEngineFetcher
     * @return Response
     */
    public function show(AutoModel $autoModel, AutoGeneration $autoGeneration, AutoModificationFetcher $autoModificationFetcher, AutoEngineFetcher $autoEngineFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $modifications = $autoModificationFetcher->allByGeneration($autoGeneration);

        $engines = $autoEngineFetcher->allByGeneration($autoGeneration);

        return $this->render('app/auto/generation/show.html.twig', [
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration,
            'modifications' => $modifications,
            'engines' => $engines,
            'table_checkable' => true,
            'auto_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/',
            'edit' => false,
        ]);
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
                $autoGeneration = $handler->handle($command, $autoModel);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/create.html.twig', [
            'form' => $form->createView(),
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/edit", name=".edit")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($autoGeneration);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/name/edit.html.twig', [
            'form' => $form->createView(),
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/description/spare", name=".description.spare")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param DescriptionSpare\Handler $handler
     * @return Response
     */
    public function descriptionSpare(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, DescriptionSpare\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionSpare\Command::fromEntity($autoGeneration);

        $form = $this->createForm(DescriptionSpare\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/description/spare/edit.html.twig', [
            'form' => $form->createView(),
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/description/acs", name=".description.acs")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param DescriptionAcs\Handler $handler
     * @return Response
     */
    public function descriptionAcs(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, DescriptionAcs\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionAcs\Command::fromEntity($autoGeneration);

        $form = $this->createForm(DescriptionAcs\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/description/acs/edit.html.twig', [
            'form' => $form->createView(),
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/description/tuning", name=".description.tuning")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param DescriptionTuning\Handler $handler
     * @return Response
     */
    public function descriptionTuning(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, DescriptionTuning\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionTuning\Command::fromEntity($autoGeneration);

        $form = $this->createForm(DescriptionTuning\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/description/tuning/edit.html.twig', [
            'form' => $form->createView(),
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/photo", name=".photo")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param Photo\Handler $handler
     * @return Response
     */
    public function photo(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, Photo\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Photo\Command::fromEntity($autoGeneration, $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/');

        $form = $this->createForm(Photo\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('photo')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                    $newFilename = $fileUploader->uploadToAdminAndDelete($attach, $autoGeneration->getPhoto(), ['width' => AutoModel::PHOTO_MAX_WIDTH, 'height' => AutoModel::PHOTO_MAX_HEIGHT]);
                    if ($newFilename) {
                        $command->photo = $newFilename;
                    }
                }

                $handler->handle($command);
                $this->addFlash('success', "Фото загружено");

                return $this->redirectToRoute('auto.generation.show', ['auto_modelID' => $autoModel->getId(), 'id' => $autoGeneration->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/generation/photo/edit.html.twig', [
            'autoModel' => $autoModel,
            'autoGeneration' => $autoGeneration,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/photo/delete", name=".photo.delete")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(AutoModel $autoModel, AutoGeneration $autoGeneration, Request $request, Flusher $flusher): Response
    {
        $photo = $autoGeneration->getPhoto();

        if ($photo != '') {
            $fileUploader = new FileUploader($this->getParameter('auto_photo'));
            $fileUploader->deleteFromAdmin($photo);
            $autoGeneration->removePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{auto_modelID}/{id}/delete", name=".delete")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param AutoGeneration $autoGeneration
     * @param AutoGenerationRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoModel $autoModel, AutoGeneration $autoGeneration, AutoGenerationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($autoGeneration->getModifications()) > 0 || count($autoGeneration->getEngines()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить поколение, содержащую модификации']);
            }

            if ($autoGeneration->getPhoto() != '') {
                $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                $fileUploader->deleteFromAdmin($autoGeneration->getPhoto());
                $autoGeneration->removePhoto();

                $flusher->flush();
            }

            $em->remove($autoGeneration);
            $flusher->flush();
            $data['message'] = 'Поколение удалено';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modelID}/hide", name=".hide")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param Request $request
     * @param AutoGenerationRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(AutoModel $autoModel, Request $request, AutoGenerationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoGeneration = $repository->get($request->query->getInt('id'));
            $autoGeneration->hide();
            $flusher->flush();
            $data['action'] = 'hide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{auto_modelID}/unHide", name=".unHide")
     * @ParamConverter("autoModel", options={"id" = "auto_modelID"})
     * @param AutoModel $autoModel
     * @param Request $request
     * @param AutoGenerationRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(AutoModel $autoModel, Request $request, AutoGenerationRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoGeneration = $repository->get($request->query->getInt('id'));
            $autoGeneration->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
