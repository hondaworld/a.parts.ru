<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\Model\Edit;
use App\Model\Auto\UseCase\Model\Create;
use App\Model\Auto\UseCase\Model\Photo;
use App\Model\Auto\UseCase\Model\DescriptionSpare;
use App\Model\Auto\UseCase\Model\DescriptionAcs;
use App\Model\Auto\UseCase\Model\DescriptionTuning;
use App\Model\Auto\UseCase\Model\DescriptionService;
use App\Model\Flusher;
use App\ReadModel\Auto\AutoGenerationFetcher;
use App\ReadModel\Auto\AutoModelFetcher;
use App\ReadModel\Card\ZapCardKitFetcher;
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
 * @Route("/auto/model", name="auto.model")
 */
class AutoModelController extends AbstractController
{
    /**
     * @Route("/{auto_markaID}/", name="")
     * @param AutoMarka $autoMarka
     * @param AutoModelFetcher $fetcher
     * @return Response
     */
    public function index(AutoMarka $autoMarka, AutoModelFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $models = $fetcher->allByMarka($autoMarka);

        return $this->render('app/auto/model/index.html.twig', [
            'models' => $models,
            'autoMarka' => $autoMarka,
            'table_checkable' => true,
            'auto_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/'
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/show", name=".show")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param AutoGenerationFetcher $autoGenerationFetcher
     * @param ZapCardKitFetcher $zapCardKitFetcher
     * @return Response
     * @throws \Doctrine\DBAL\Exception
     */
    public function show(AutoMarka $autoMarka, AutoModel $autoModel, AutoGenerationFetcher $autoGenerationFetcher, ZapCardKitFetcher $zapCardKitFetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $generations = $autoGenerationFetcher->allByModel($autoModel);

        $kits = $zapCardKitFetcher->all($autoModel, []);

        return $this->render('app/auto/model/show.html.twig', [
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel,
            'generations' => $generations,
            'table_checkable' => true,
            'table_sortable' => true,
            'auto_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/',
            'edit' => false,
            'kits' => $kits,
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
                $autoModel = $handler->handle($command, $autoMarka);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/create.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/edit", name=".edit")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Edit\Command::fromEntity($autoModel);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/name/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/spare", name=".description.spare")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param DescriptionSpare\Handler $handler
     * @return Response
     */
    public function descriptionSpare(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, DescriptionSpare\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionSpare\Command::fromEntity($autoModel);

        $form = $this->createForm(DescriptionSpare\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/description/spare/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/acs", name=".description.acs")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param DescriptionAcs\Handler $handler
     * @return Response
     */
    public function descriptionAcs(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, DescriptionAcs\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionAcs\Command::fromEntity($autoModel);

        $form = $this->createForm(DescriptionAcs\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/description/acs/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/tuning", name=".description.tuning")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param DescriptionTuning\Handler $handler
     * @return Response
     */
    public function descriptionTuning(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, DescriptionTuning\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionTuning\Command::fromEntity($autoModel);

        $form = $this->createForm(DescriptionTuning\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/description/tuning/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/description/service", name=".description.service")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param DescriptionService\Handler $handler
     * @return Response
     */
    public function descriptionService(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, DescriptionService\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = DescriptionService\Command::fromEntity($autoModel);

        $form = $this->createForm(DescriptionService\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/description/service/edit.html.twig', [
            'form' => $form->createView(),
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/photo", name=".photo")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param Photo\Handler $handler
     * @return Response
     */
    public function photo(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, Photo\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');

        $command = Photo\Command::fromEntity($autoModel, $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/');

        $form = $this->createForm(Photo\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('photo')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                    $newFilename = $fileUploader->uploadToAdminAndDelete($attach, $autoModel->getPhoto(), ['width' => AutoModel::PHOTO_MAX_WIDTH, 'height' => AutoModel::PHOTO_MAX_HEIGHT]);
                    if ($newFilename) {
                        $command->photo = $newFilename;
                    }
                }

                $handler->handle($command);
                $this->addFlash('success', "Фото загружено");

                return $this->redirectToRoute('auto.model.show', ['auto_markaID' => $autoMarka->getId(), 'id' => $autoModel->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/model/photo/edit.html.twig', [
            'autoMarka' => $autoMarka,
            'autoModel' => $autoModel,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/photo/delete", name=".photo.delete")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(AutoMarka $autoMarka, AutoModel $autoModel, Request $request, Flusher $flusher): Response
    {
        $photo = $autoModel->getPhoto();

        if ($photo != '') {
            $fileUploader = new FileUploader($this->getParameter('auto_photo'));
            $fileUploader->deleteFromAdmin($photo);
            $autoModel->removePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{auto_markaID}/{id}/delete", name=".delete")
     * @ParamConverter("autoMarka", options={"id" = "auto_markaID"})
     * @param AutoMarka $autoMarka
     * @param AutoModel $autoModel
     * @param AutoModelRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(AutoMarka $autoMarka, AutoModel $autoModel, AutoModelRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($autoModel->getGenerations()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить модель, содержащую поколения']);
            }
            if (count($autoModel->getAutos()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить модель, прикрепленную к автомобилю клиента']);
            }

            if ($autoModel->getPhoto() != '') {
                $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                $fileUploader->deleteFromAdmin($autoModel->getPhoto());
                $autoModel->removePhoto();

                $flusher->flush();
            }

            $em->remove($autoModel);
            $flusher->flush();
            $data['message'] = 'Модель удалена';

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
     * @param AutoModelRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(AutoMarka $autoMarka, Request $request, AutoModelRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoModel = $repository->get($request->query->getInt('id'));
            $autoModel->hide();
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
     * @param AutoModelRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(AutoMarka $autoMarka, Request $request, AutoModelRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AutoMarka');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $autoModel = $repository->get($request->query->getInt('id'));
            $autoModel->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
