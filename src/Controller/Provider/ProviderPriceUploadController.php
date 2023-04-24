<?php

namespace App\Controller\Provider;

use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Provider\Entity\LogPrice\LogPriceRepository;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAllRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Provider\UseCase\Price\Upload;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\Service\PriceUploader;
use DomainException;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/providers/prices/upload", name="providers.prices.upload")
 */
class ProviderPriceUploadController extends AbstractController
{
    /**
     * @Route("", name="")
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @return Response
     */
    public function index(ProviderPriceFetcher $providerPriceFetcher): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceUpload');

        $files = $providerPriceFetcher->findUploaded($this->getParameter('price_directory') . '/auto');
        $badFiles = $providerPriceFetcher->findUploadedBadFiles($this->getParameter('price_directory') . '/auto');
        $archiveFiles = $providerPriceFetcher->findUploadedArchiveFiles($this->getParameter('price_directory') . '/archive');

        return $this->render('app/providers/prices/upload/index.html.twig', [
            'files' => $files,
            'badFiles' => $badFiles,
            'archiveFiles' => $archiveFiles,
            'dir' => '/' . $this->getParameter('price_directory_www') . '/archive/',
        ]);
    }

    /**
     * @Route("/upload", name=".upload")
     * @param Request $request
     * @return Response
     */
    public function upload(Request $request): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceUpload');

        $command = new Upload\Command();

        $form = $this->createForm(Upload\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $file = $form->get('file')->getData();

                if ($file) {
                    $fileUploader = new PriceUploader($this->getParameter('price_directory') . '/auto');
                    $fileUploader->upload($file);
//                    if ($providerPrice = $providerPriceRepository->findByPrice($fileUploader->getFileName())) {
//                        $fileUploader->xlsToCsv($providerPrice);
//                        $arData = $fileUploader->price($providerPrice);
//                        dump($arData);
//                    }
                }

//                $handler->handle($command);
                $this->addFlash('success', "Прайс-лист загружен");
                return $this->redirectToRoute('providers.prices.upload');
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/providers/prices/upload/upload.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/file", name=".file")
     * @param Request $request
     * @param CreaterFetcher $createrFetcher
     * @param CreaterRepository $createrRepository
     * @param PriceUploaderFetcher $priceUploaderFetcher
     * @param ProviderPriceRepository $providerPriceRepository
     * @param LogPriceRepository $logPriceRepository
     * @param LogPriceAllRepository $logPriceAllRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function price(
        Request $request,
        CreaterFetcher $createrFetcher,
        CreaterRepository $createrRepository,
        PriceUploaderFetcher $priceUploaderFetcher,
        ProviderPriceRepository $providerPriceRepository,
        LogPriceRepository $logPriceRepository,
        LogPriceAllRepository $logPriceAllRepository,
        Flusher $flusher
    ): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '500M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'ProviderPriceUpload');

        try {
            $providerPrice = $providerPriceRepository->get($request->get('id'));

            $fileUploader = new PriceUploader($this->getParameter('price_directory') . '/auto');
            if ($fileUploader->setFileNameFromProviderPrice($providerPrice)) {
                $fileUploader->uploadPriceAndDelete(
                    $providerPrice,
                    $createrFetcher,
                    $createrRepository,
                    $priceUploaderFetcher,
                    $logPriceRepository,
                    $logPriceAllRepository,
                    $flusher
                );
            } else {
                $this->addFlash('danger', "Прайс-лист отсутствует");
                return $this->json(['code' => 404, 'message' => "Прайс-лист отсутствует"]);
            }
        } catch (EntityNotFoundException $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

//        $this->addFlash('success', "Прайс-лист загружается");
        return $this->json(['code' => 200, 'message' => "Прайс-лист загружается"]);
    }

    /**
     * @Route("/file/delete", name=".file.delete")
     * @param Request $request
     * @return Response
     */
    public function fileDelete(Request $request): Response
    {
        $filename = $request->get('filename');
        @unlink($this->getParameter('price_directory') . '/auto/' . $filename);

        $data = ['code' => 200, 'message' => 'Файл удален'];

        return $this->json($data);
    }

    /**
     * @Route("/email", name=".email")
     * @param Imap $imap
     * @param ProviderPriceRepository $providerPriceRepository
     * @return Response
     */
    public function email(Imap $imap, ProviderPriceRepository $providerPriceRepository): Response
    {
//        $emailPrice = new EmailPrice($imap, $providerPriceRepository);
//        $emailPrice->saveAttachments($this->getParameter('price_directory'));

        return $this->render('app/home.html.twig');
    }
}
