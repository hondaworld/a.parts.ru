<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Photo\ZapCardPhoto;
use App\Model\Card\Entity\Photo\ZapCardPhotoRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\ReadModel\Card\Filter;
use App\Security\Voter\StandartActionsVoter;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/card/parts/photos", name="card.parts.photos")
 */
class ZapCardPhotosController extends AbstractController
{
    /**
     * @Route("/{id}/", name="")
     * @param ZapCard $zapCard
     * @param Request $request
     * @return Response
     */
    public function index(ZapCard $zapCard, Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::SHOW, 'ZapCard');


        return $this->render('app/card/photos/index.html.twig', [
            'zapCard' => $zapCard,
            'zap_card_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('zap_card_photo') . '/'
        ]);
    }

    /**
     * @Route("/{id}/create", name=".create")
     * @param ZapCard $zapCard
     * @param Request $request
     * @return Response
     */
    public function create(ZapCard $zapCard, Request $request): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');


        return $this->render('app/card/photos/create.html.twig', [
            'zapCard' => $zapCard,
        ]);
    }

    /**
     * @Route("/{id}/upload", name=".upload")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ZapCardPhotoRepository $zapCardPhotoRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function upload(ZapCard $zapCard, Request $request, ZapCardPhotoRepository $zapCardPhotoRepository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $file = $request->files->get('file');

        if (!in_array($file->getClientMimeType(), ["image/bmp", "image/x-png", "image/gif", "image/jpeg", "image/jpg", "image/png"])) {
            return $this->json('Файл должен быть картинкой', 400);
        }

        if ($file->getSize() > 4096 * 1024) {
            return $this->json('Файл должен быть не больше 4Мб', 400);
        }

        $fileUploader = new FileUploader($this->getParameter('zap_card_photo'));
        $simage = $fileUploader->uploadToAdmin($file, '', ['width' => ZapCardPhoto::PHOTO_SMALL_MAX_WIDTH, 'height' => ZapCardPhoto::PHOTO_SMALL_MAX_HEIGHT]);
        $bimage = $fileUploader->uploadToAdmin($file, '', ['width' => ZapCardPhoto::PHOTO_BIG_MAX_WIDTH, 'height' => ZapCardPhoto::PHOTO_BIG_MAX_HEIGHT]);
        $fileUploader->deleteUploadedFile($file);

        if ($simage && $bimage) {
            $zapCardPhoto = new ZapCardPhoto($zapCard, $simage, $bimage, count($zapCard->getPhotos()) == 0);
            $zapCardPhotoRepository->add($zapCardPhoto);
            $flusher->flush();
        } else {
            return $this->json('Ошибка загрузки файла', 400);
        }

        return $this->json([]);
    }

    /**
     * @Route("/{zapCardID}/{id}/delete", name=".delete")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapCardPhoto $zapCardPhoto
     * @param ZapCardPhotoRepository $zapCardPhotoRepository
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCard $zapCard, ZapCardPhoto $zapCardPhoto, ZapCardPhotoRepository $zapCardPhotoRepository, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {

            $simage = $zapCardPhoto->getSimage();
            $bimage = $zapCardPhoto->getBimage();

            $fileUploader = new FileUploader($this->getParameter('zap_card_photo'));

            if ($simage != '') {
                $fileUploader->deleteFromAdmin($simage);
            }

            if ($bimage != '') {
                $fileUploader->deleteFromAdmin($bimage);
            }

            if ($zapCardPhoto->isMain()) {
                $zapCardPhotoMain = $zapCardPhotoRepository->findNotMain($zapCard, $zapCardPhoto);
                if ($zapCardPhotoMain) $zapCardPhotoMain->updateMain(true);
            }

            $em->remove($zapCardPhoto);
            $flusher->flush();
            $data['message'] = 'Фотография удалена';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/{zapCardID}/{id}/main", name=".main")
     * @ParamConverter("zapCard", options={"id" = "zapCardID"})
     * @param ZapCard $zapCard
     * @param ZapCardPhoto $zapCardPhoto
     * @param ZapCardPhotoRepository $zapCardPhotoRepository
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function main(ZapCard $zapCard, ZapCardPhoto $zapCardPhoto, ZapCardPhotoRepository $zapCardPhotoRepository, Request $request, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $zapCardPhotoRepository->updateMain($zapCard);
        $zapCardPhoto->updateMain(true);
        $flusher->flush();

        return $this->redirectToRoute('card.parts.photos', ['id' => $zapCard->getId()]);
    }
}
