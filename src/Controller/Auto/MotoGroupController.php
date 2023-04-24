<?php

namespace App\Controller\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Model\AutoModelRepository;
use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use App\Model\Auto\Entity\MotoGroup\MotoGroupRepository;
use App\Model\EntityNotFoundException;
use App\Model\Auto\UseCase\MotoGroup\Edit;
use App\Model\Auto\UseCase\MotoGroup\Create;
use App\Model\Flusher;
use App\ReadModel\Auto\MotoGroupFetcher;
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
 * @Route("/auto/moto-group", name="auto.moto.group")
 */
class MotoGroupController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param MotoGroupFetcher $fetcher
     * @return Response
     */
    public function index(MotoGroupFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');

        $groups = $fetcher->all();

        return $this->render('app/auto/motoGroup/index.html.twig', [
            'groups' => $groups,
            'table_checkable' => true,
            'auto_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/'
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
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('photo')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                    $newFilename = $fileUploader->uploadToAdmin($attach, '', ['width' => MotoGroup::PHOTO_MAX_WIDTH, 'height' => MotoGroup::PHOTO_MAX_HEIGHT]);
                    if ($newFilename) {
                        $command->photo = $newFilename;
                    }
                }

                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.group');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoGroup/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param MotoGroup $motoGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(MotoGroup $motoGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');

        $command = Edit\Command::fromEntity($motoGroup, $this->getParameter('admin_site') . $this->getParameter('auto_photo') . '/');

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('photo')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                    $newFilename = $fileUploader->uploadToAdminAndDelete($attach, $motoGroup->getPhoto(), ['width' => MotoGroup::PHOTO_MAX_WIDTH, 'height' => MotoGroup::PHOTO_MAX_HEIGHT]);
                    if ($newFilename) {
                        $command->photo = $newFilename;
                    }
                } else {
                    $command->photo = $motoGroup->getPhoto();
                }

                $handler->handle($command);
                return $this->redirectToRoute('auto.moto.group');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/auto/motoGroup/edit.html.twig', [
            'form' => $form->createView(),
            'motoGroup' => $motoGroup
        ]);
    }

    /**
     * @Route("/{id}/photo/delete", name=".photo.delete")
     * @param MotoGroup $motoGroup
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(MotoGroup $motoGroup, Request $request, Flusher $flusher): Response
    {
        $photo = $motoGroup->getPhoto();

        if ($photo != '') {
            $fileUploader = new FileUploader($this->getParameter('auto_photo'));
            $fileUploader->deleteFromAdmin($photo);
            $motoGroup->removePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param MotoGroup $motoGroup
     * @param MotoGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(MotoGroup $motoGroup, MotoGroupRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($motoGroup->getMotoModels()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить группу, содержащую модели мотоциклов']);
            }

            if ($motoGroup->getPhoto() != '') {
                $fileUploader = new FileUploader($this->getParameter('auto_photo'));
                $fileUploader->deleteFromAdmin($motoGroup->getPhoto());
                $motoGroup->removePhoto();

                $flusher->flush();
            }

            $em->remove($motoGroup);
            $flusher->flush();
            $data['message'] = 'Модель удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/hide", name=".hide")
     * @param Request $request
     * @param MotoGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function hide(Request $request, MotoGroupRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $motoGroup = $repository->get($request->query->getInt('id'));
            $motoGroup->hide();
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
     * @param MotoGroupRepository $repository
     * @param Flusher $flusher
     * @return Response
     */
    public function unHide(Request $request, MotoGroupRepository $repository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'MotoGroup');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'action' => '', 'message' => ''];

        try {
            $motoGroup = $repository->get($request->query->getInt('id'));
            $motoGroup->unHide();
            $flusher->flush();
            $data['action'] = 'unHide';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
