<?php

namespace App\Controller\Card;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\FakePhoto\ZapCardFakePhoto;
use App\Model\Card\Entity\FakePhoto\ZapCardFakePhotoRepository;
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
 * @Route("/card/parts/fakePhotos", name="card.parts.fakePhotos")
 */
class ZapCardFakePhotosController extends AbstractController
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

        return $this->render('app/card/fakePhotos/index.html.twig', [
            'zapCard' => $zapCard,
            'zap_card_photo_folder' => $this->getParameter('admin_site') . $this->getParameter('zap_card_fake_photo') . '/'
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


        return $this->render('app/card/fakePhotos/create.html.twig', [
            'zapCard' => $zapCard,
        ]);
    }

    /**
     * @Route("/{id}/upload", name=".upload")
     * @param ZapCard $zapCard
     * @param Request $request
     * @param ZapCardFakePhotoRepository $zapCardFakePhotoRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function upload(ZapCard $zapCard, Request $request, ZapCardFakePhotoRepository $zapCardFakePhotoRepository, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $file = $request->files->get('file');

        if (!in_array($file->getClientMimeType(), ["image/bmp", "image/x-png", "image/gif", "image/jpeg", "image/jpg", "image/png"])) {
            return $this->json('Файл должен быть картинкой', 400);
        }

        if ($file->getSize() > 2048 * 1024) {
            return $this->json('Файл должен быть не больше 2Мб', 400);
        }

        $fileUploader = new FileUploader($this->getParameter('zap_card_fake_photo'));
        $simage = $fileUploader->uploadToAdmin($file, '', ['width' => ZapCardFakePhoto::PHOTO_SMALL_MAX_WIDTH, 'height' => ZapCardFakePhoto::PHOTO_SMALL_MAX_HEIGHT]);
        $bimage = $fileUploader->uploadToAdmin($file, '', ['width' => ZapCardFakePhoto::PHOTO_BIG_MAX_WIDTH, 'height' => ZapCardFakePhoto::PHOTO_BIG_MAX_HEIGHT]);
        $fileUploader->deleteUploadedFile($file);

        if ($simage && $bimage) {
            $zapCardFakePhoto = new ZapCardFakePhoto($zapCard, $simage, $bimage, count($zapCard->getFakePhotos()) == 0);
            $zapCardFakePhotoRepository->add($zapCardFakePhoto);
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
     * @param ZapCardFakePhoto $zapCardFakePhoto
     * @param ZapCardFakePhotoRepository $zapCardFakePhotoRepository
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(ZapCard $zapCard, ZapCardFakePhoto $zapCardFakePhoto, ZapCardFakePhotoRepository $zapCardFakePhotoRepository, Request $request, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {

            $simage = $zapCardFakePhoto->getSimage();
            $bimage = $zapCardFakePhoto->getBimage();

            $fileUploader = new FileUploader($this->getParameter('zap_card_fake_photo'));

            if ($simage != '') {
                $fileUploader->deleteFromAdmin($simage);
            }

            if ($bimage != '') {
                $fileUploader->deleteFromAdmin($bimage);
            }

            if ($zapCardFakePhoto->isMain()) {
                $zapCardPhotoMain = $zapCardFakePhotoRepository->findNotMain($zapCard, $zapCardFakePhoto);
                if ($zapCardPhotoMain) $zapCardPhotoMain->updateMain(true);
            }

            $em->remove($zapCardFakePhoto);
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
     * @param ZapCardFakePhoto $zapCardFakePhoto
     * @param ZapCardFakePhotoRepository $zapCardFakePhotoRepository
     * @param Request $request
     * @param Flusher $flusher
     * @return Response
     */
    public function main(ZapCard $zapCard, ZapCardFakePhoto $zapCardFakePhoto, ZapCardFakePhotoRepository $zapCardFakePhotoRepository, Request $request, Flusher $flusher): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'ZapCard');

        $zapCardFakePhotoRepository->updateMain($zapCard);
        $zapCardFakePhoto->updateMain(true);
        $flusher->flush();

        return $this->redirectToRoute('card.parts.fakePhotos', ['id' => $zapCard->getId()]);
    }
}
