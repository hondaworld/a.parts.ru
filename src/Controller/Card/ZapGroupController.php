<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Category\ZapCategoryRepository;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Model\Card\Entity\Group\ZapGroupRepository;
use App\Model\EntityNotFoundException;
use App\Model\Card\UseCase\Group\Edit;
use App\Model\Card\UseCase\Group\Create;
use App\Model\Card\UseCase\Group\Photo;
use App\Model\Flusher;
use App\ReadModel\Card\ZapGroupFetcher;
use App\Security\Voter\Card\ZapCategoryVoter;
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
 * @Route("/card/categories/groups", name="card.categories.groups")
 */
class ZapGroupController extends AbstractController
{
    /**
     * @Route("/{zapCategoryID}/", name="")
     * @param ZapCategory $zapCategory
     * @param ZapGroupFetcher $fetcher
     * @return Response
     */
    public function index(ZapCategory $zapCategory, ZapGroupFetcher $fetcher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ZapCategory');

        $groups = $fetcher->allByCategory($zapCategory);

        return $this->render('app/card/groups/index.html.twig', [
            'groups' => $groups,
            'zapCategory' => $zapCategory,
            'table_sortable' => true,
            'zap_group_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('zap_group_photo') . '/'
        ]);
    }

    /**
     * @Route("/{zapCategoryID}/create", name=".create")
     * @param ZapCategory $zapCategory
     * @param Request $request
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(ZapCategory $zapCategory, Request $request, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCategoryVoter::ZAP_GROUP_CHANGE, $zapCategory);

        $command = new Create\Command($zapCategory);

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.categories.groups', ['zapCategoryID' => $zapCategory->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/groups/create.html.twig', [
            'form' => $form->createView(),
            'zapCategory' => $zapCategory
        ]);
    }

    /**
     * @Route("/{zapCategoryID}/{id}/edit", name=".edit")
     * @ParamConverter("zapCategory", options={"id" = "zapCategoryID"})
     * @param ZapCategory $zapCategory
     * @param ZapGroup $zapGroup
     * @param Request $request
     * @param Edit\Handler $handler
     * @return Response
     */
    public function edit(ZapCategory $zapCategory, ZapGroup $zapGroup, Request $request, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCategoryVoter::ZAP_GROUP_CHANGE, $zapCategory);

        $command = Edit\Command::fromEntity($zapGroup);

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('card.categories.groups', ['zapCategoryID' => $zapCategory->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/groups/edit.html.twig', [
            'form' => $form->createView(),
            'zapCategory' => $zapCategory,
            'zapGroup' => $zapGroup
        ]);
    }

    /**
     * @Route("/{zapCategoryID}/{id}/photo", name=".photo")
     * @ParamConverter("zapCategory", options={"id" = "zapCategoryID"})
     * @param ZapCategory $zapCategory
     * @param ZapGroup $zapGroup
     * @param Request $request
     * @param Photo\Handler $handler
     * @return Response
     */
    public function photo(ZapCategory $zapCategory, ZapGroup $zapGroup, Request $request, Photo\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(ZapCategoryVoter::ZAP_GROUP_CHANGE, $zapCategory);

        $command = Photo\Command::fromEntity($zapGroup, $this->getParameter('admin_site') . $this->getParameter('zap_group_photo') . '/');

        $form = $this->createForm(Photo\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $attach = $form->get('photo')->getData();
                if ($attach) {
                    $fileUploader = new FileUploader($this->getParameter('zap_group_photo'));
//                    $fileUploader->resize($newFilename, Manager::PHOTO_MAX_WIDTH, Manager::PHOTO_MAX_HEIGHT);
                    $newFilename = $fileUploader->uploadToAdminAndDelete($attach, $zapGroup->getPhoto(), ['width' => ZapGroup::PHOTO_MAX_WIDTH, 'height' => ZapGroup::PHOTO_MAX_HEIGHT]);
                    if ($newFilename) {
                        $command->photo = $newFilename;
                    }
                }

                $handler->handle($command);
                $this->addFlash('success', "Фото загружено");

                return $this->redirectToRoute('card.categories.groups', ['zapCategoryID' => $zapCategory->getId()]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/card/groups//editPhoto.html.twig', [
            'zapCategory' => $zapCategory,
            'zapGroup' => $zapGroup,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/{zapCategoryID}/{id}/photo/delete", name=".photo.delete")
     * @ParamConverter("zapCategory", options={"id" = "zapCategoryID"})
     * @param ZapCategory $zapCategory
     * @param ZapGroup $zapGroup
     * @param Flusher $flusher
     * @return Response
     */
    public function photoDelete(ZapCategory $zapCategory, ZapGroup $zapGroup, Flusher $flusher): Response
    {
        $photo = $zapGroup->getPhoto();

        if ($photo != '') {
            $fileUploader = new FileUploader($this->getParameter('zap_group_photo'));
            $fileUploader->deleteFromAdmin($photo);
            $zapGroup->removePhoto();

            $flusher->flush();
        }

        return $this->json([]);
    }

    /**
     * @Route("/{zapCategoryID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCategory", options={"id" = "zapCategoryID"})
     * @param ZapCategory $zapCategory
     * @param ZapGroup $zapGroup
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCategory $zapCategory, ZapGroup $zapGroup, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(ZapCategoryVoter::ZAP_GROUP_CHANGE, $zapCategory);
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            if (count($zapGroup->getZapCards()) > 0) {
                return $this->json(['code' => 500, 'message' => 'Невозможно удалить группу, содержащую детали']);
            }

            if ($zapGroup->getPhoto() != '') {
                $fileUploader = new FileUploader($this->getParameter('zap_group_photo'));
                $fileUploader->deleteFromAdmin($zapGroup->getPhoto());
                $zapGroup->removePhoto();

                $flusher->flush();
            }

            $em->remove($zapGroup);
            $flusher->flush();
            $data['message'] = 'Группа товаров удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }
}
