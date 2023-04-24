<?php

namespace App\Controller\Reseller;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use App\Model\Reseller\Entity\Avito\AvitoNoticeRepository;
use App\Model\Reseller\UseCase\AvitoNotice\Edit\Handler;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Reseller\AvitoNoticeFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Security\Voter\StandartActionsVoter;
use App\ReadModel\Reseller\Filter;
use App\Model\Reseller\UseCase\AvitoNotice\Create;
use App\Model\Reseller\UseCase\AvitoNotice\Edit;
use App\Service\ManagerSettings;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @Route("/resellers/avito/notices", name="resellers.avito.notices")
 */
class AvitoNoticesController extends AbstractController
{
    /**
     * @Route("/", name="")
     * @param AvitoNoticeFetcher $fetcher
     * @param Request $request
     * @param ManagerSettings $settings
     * @param ZapCardPriceService $zapCardPriceService
     * @param OptRepository $optRepository
     * @param ZapCardRepository $zapCardRepository
     * @return Response
     */
    public function index(AvitoNoticeFetcher $fetcher, Request $request, ManagerSettings $settings, ZapCardPriceService $zapCardPriceService, OptRepository $optRepository, ZapCardRepository $zapCardRepository): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::INDEX, 'AvitoNotice');

        $settings = $settings->get('avitoNotices');

        $filter = new Filter\AvitoNotice\Filter();
        $filter->inPage = $settings['inPage'] ?? $fetcher::PER_PAGE;

        $form = $this->createForm(Filter\AvitoNotice\Form::class, $filter);
        $form->handleRequest($request);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            $settings
        );

        $items = $pagination->getItems();
        foreach ($items as &$item) {
            $item['price'] = $zapCardPriceService->priceOpt($zapCardRepository->get($item['zapCardID']), $optRepository->get(Opt::DEFAULT_OPT_ID));
        }
        $pagination->setItems($items);

        return $this->render('app/reseller/avito/notices/index.html.twig', [
            'filter' => $form->createView(),
            'types' => AvitoNotice::TYPES,
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("/create", name=".create")
     * @param Request $request
     * @param ZapCardRepository $zapCardRepository
     * @param Create\Handler $handler
     * @return Response
     */
    public function create(Request $request, ZapCardRepository $zapCardRepository, Create\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::CREATE, 'AvitoNotice');

        $command = new Create\Command();

        $form = $this->createForm(Create\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $zapCards = $zapCardRepository->findByNumber(new DetailNumber($command->number));
                if (empty($zapCards)) {
                    throw new DomainException("Номер в номенклатуре не найден");
                } elseif (count($zapCards) == 1) {
                    $command->zapCard = $zapCards[0];
                } elseif ($command->zapCardID) {
                    foreach ($zapCards as $zapCard) {
                        if ($zapCard->getId() == $command->zapCardID) {
                            $command->zapCard = $zapCard;
                        }
                    }
                }

                if ($command->zapCard) {
                    $avitoNotice = $handler->handle($command, $this->getParameter('admin_site') . $this->getParameter('zap_card_photo') . '/');
                    return $this->redirectToRoute('resellers.avito.notices.edit', ['id' => $avitoNotice->getId()]);
                }
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/reseller/avito/notices/create.html.twig', [
            'form' => $form->createView(),
            'zapCards' => $zapCards ?? null
        ]);
    }

    /**
     * @Route("/{id}/edit", name=".edit")
     * @param AvitoNotice $avitoNotice
     * @param Request $request
     * @param AvitoNoticeFetcher $avitoNoticeFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param OptRepository $optRepository
     * @param Handler $handler
     * @return Response
     * @throws Exception
     */
    public function edit(AvitoNotice $avitoNotice, Request $request, AvitoNoticeFetcher $avitoNoticeFetcher, IncomeFetcher $incomeFetcher, ZapSkladFetcher $zapSkladFetcher, ZapCardPriceService $zapCardPriceService, OptRepository $optRepository, Edit\Handler $handler): Response
    {
        $this->denyAccessUnlessGranted(StandartActionsVoter::EDIT, 'AvitoNotice');

        $command = Edit\Command::fromAvitoNotice($avitoNotice);

        if ($request->get('form') && isset($request->get('form')['make']) && $request->get('form')['make'] != '') {
            $command->models = $avitoNoticeFetcher->assocModels($request->get('form')['make']);
        } else if ($command->make) {
            $command->models = $avitoNoticeFetcher->assocModels($command->make);
        }

        if ($request->get('form') && isset($request->get('form')['model']) && $request->get('form')['model'] != '') {
            $command->generations = $avitoNoticeFetcher->assocGenerations($request->get('form')['model']);
        } else if ($command->model) {
            $command->generations = $avitoNoticeFetcher->assocGenerations($command->model);
        }

        if ($request->get('form') && isset($request->get('form')['generation']) && $request->get('form')['generation'] != '') {
            $command->modifications = $avitoNoticeFetcher->assocModifications($request->get('form')['generation']);
        } else if ($command->generation) {
            $command->modifications = $avitoNoticeFetcher->assocModifications($command->generation);
        }

        $sklads = $zapSkladFetcher->assoc();
        $quantity = $incomeFetcher->findQuantityInWarehouseByZapCard($avitoNotice->getZapCard()->getId());

        $price = $zapCardPriceService->priceOpt($avitoNotice->getZapCard(), $optRepository->get(Opt::DEFAULT_OPT_ID));

        $form = $this->createForm(Edit\Form::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $handler->handle($command);
                return $this->redirectToRoute('resellers.avito.notices', ['page' => $request->getSession()->get('page/avitoNotices') ?: 1]);
            } catch (DomainException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('app/reseller/avito/notices/edit.html.twig', [
            'form' => $form->createView(),
            'avitoNotice' => $avitoNotice,
            'quantity' => $quantity,
            'price' => $price,
            'sklads' => $sklads
        ]);
    }

    /**
     * @Route("/{id}/delete", name=".delete")
     * @param int $id
     * @param AvitoNoticeRepository $avitoNoticeRepository
     * @param Flusher $flusher
     * @return Response
     */
    public function delete(int $id, AvitoNoticeRepository $avitoNoticeRepository, Flusher $flusher): Response
    {
        try {
            $this->denyAccessUnlessGranted(StandartActionsVoter::DELETE, 'AvitoNotice');
        } catch (AccessDeniedException $e) {
            return $this->json(['code' => 403, 'message' => $e->getMessage()]);
        }

        $data = ['code' => 200, 'message' => ''];

        $em = $this->getDoctrine()->getManager();
        try {
            $avitoNotice = $avitoNoticeRepository->get($id);

            $em->remove($avitoNotice);
            $flusher->flush();
            $data['message'] = 'Объявление удалено';

        } catch (EntityNotFoundException $e) {
            return $this->json(['code' => 404, 'message' => $e->getMessage()]);
        }

        return $this->json($data);
    }

    /**
     * @Route("/auto/models", name=".auto.models")
     * @param Request $request
     * @param AvitoNoticeFetcher $avitoNoticeFetcher
     * @return Response
     */
    public function autoModels(Request $request, AvitoNoticeFetcher $avitoNoticeFetcher): Response
    {
        $make_id = $request->query->get('id');

        if ($make_id == "") {
            $models = [];
        } else {
            try {
                $models = $avitoNoticeFetcher->assocModels($make_id);
            } catch (DomainException|Exception $e) {
                $models = [];
            }
        }

        return $this->render('app/reseller/avito/notices/auto/modelOptions.html.twig', [
            'models' => $models
        ]);
    }

    /**
     * @Route("/auto/generations", name=".auto.generations")
     * @param Request $request
     * @param AvitoNoticeFetcher $avitoNoticeFetcher
     * @return Response
     */
    public function autoGenerations(Request $request, AvitoNoticeFetcher $avitoNoticeFetcher): Response
    {
        $model_id = $request->query->get('id');

        if ($model_id == "") {
            $generations = [];
        } else {
            try {
                $generations = $avitoNoticeFetcher->assocGenerations($model_id);
            } catch (DomainException|Exception $e) {
                $generations = [];
            }
        }

        return $this->render('app/reseller/avito/notices/auto/generationOptions.html.twig', [
            'generations' => $generations
        ]);
    }

    /**
     * @Route("/auto/modifications", name=".auto.modifications")
     * @param Request $request
     * @param AvitoNoticeFetcher $avitoNoticeFetcher
     * @return Response
     */
    public function autoModifications(Request $request, AvitoNoticeFetcher $avitoNoticeFetcher): Response
    {
        $generation_id = $request->query->get('id');

        if ($generation_id == "") {
            $modifications = [];
        } else {
            try {
                $modifications = $avitoNoticeFetcher->assocModifications($generation_id);
            } catch (DomainException|Exception $e) {
                $modifications = [];
            }
        }

        return $this->render('app/reseller/avito/notices/auto/modificationOptions.html.twig', [
            'modifications' => $modifications
        ]);
    }

    /**
     * @Route("/excel", name=".excel")
     * @param AvitoNoticeFetcher $fetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param OptRepository $optRepository
     * @param ZapCardRepository $zapCardRepository
     * @return Response
     */
    public function excel(AvitoNoticeFetcher $fetcher, ZapCardPriceService $zapCardPriceService, OptRepository $optRepository, ZapCardRepository $zapCardRepository): Response
    {
        try {
            $spreadsheet = new Spreadsheet();
            $aSheet = $spreadsheet->getActiveSheet();
            $aSheet->getPageMargins()->setTop(0);
            $aSheet->getPageMargins()->setLeft(0);
            $aSheet->getPageMargins()->setRight(0);
            $aSheet->getPageMargins()->setBottom(0);
            $aSheet
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

            $aSheet->setCellValue("A1", "Id");
            $aSheet->setCellValue("B1", "AvitoId");
            $aSheet->setCellValue("C1", "AdStatus"); // Free
            $aSheet->setCellValue("D1", "ContactPhone");
            $aSheet->setCellValue("E1", "Address");
            $aSheet->setCellValue("F1", "Category"); // Запчасти и аксессуары
            $aSheet->setCellValue("G1", "TypeId");
            $aSheet->setCellValue("H1", "AdType"); // Товар приобретен на продажу
            $aSheet->setCellValue("I1", "Title");
            $aSheet->setCellValue("J1", "Description");
            $aSheet->setCellValue("K1", "Price");
            $aSheet->setCellValue("L1", "Condition"); // Новое
            $aSheet->setCellValue("M1", "OEM");
            $aSheet->setCellValue("N1", "Brand");
            $aSheet->setCellValue("O1", "ImageUrls");
            $aSheet->setCellValue("P1", "Originality"); // Оригинал
            $aSheet->setCellValue("Q1", "ProductType"); // Для автомобилей
            $aSheet->setCellValue("R1", "Make");
            $aSheet->setCellValue("S1", "Model");
            $aSheet->setCellValue("T1", "Generation");
            $aSheet->setCellValue("U1", "Modification");


            $i = 2;

            $notices = $fetcher->allForExcel(ZapSklad::OSN_SKLAD_ID);

            foreach ($notices as $notice) {
                if ($notice['quantity'] > 0) {
                    $aSheet->setCellValue("A" . $i, $notice['id']);
                    $aSheet->setCellValue("B" . $i, $notice['avito_id']);
                    $aSheet->setCellValue("C" . $i, "Free"); // Free
                    $aSheet->setCellValue("D" . $i, $notice['contact_phone']);
                    $aSheet->setCellValue("E" . $i, $notice['address']);
                    $aSheet->setCellValue("F" . $i, "Запчасти и аксессуары"); // Запчасти и аксессуары
                    $aSheet->setCellValue("G" . $i, $notice['type_id']);
                    $aSheet->setCellValue("H" . $i, "Товар приобретен на продажу"); // Товар приобретен на продажу
                    $aSheet->setCellValue("I" . $i, $notice['title']);
                    $aSheet->setCellValue("J" . $i, $notice['description']);
                    $aSheet->setCellValue("K" . $i, $zapCardPriceService->priceOpt($zapCardRepository->get($notice['zapCardID']), $optRepository->get(Opt::DEFAULT_OPT_ID)));
                    $aSheet->setCellValue("L" . $i, "Новое"); // Новое
                    $aSheet->setCellValue("M" . $i, $notice['oem']);
                    $aSheet->setCellValue("N" . $i, $notice['brand']);
                    $aSheet->setCellValue("O" . $i, $notice['image_urls']);
                    $aSheet->setCellValue("P" . $i, "Оригинал"); // Оригинал
                    $aSheet->setCellValue("Q" . $i, "Для автомобилей"); // Для автомобилей
                    $aSheet->setCellValue("R" . $i, $notice['make_name']);
                    $aSheet->setCellValue("S" . $i, $notice['model_name']);
                    $aSheet->setCellValue("T" . $i, $notice['generation_name']);
                    $aSheet->setCellValue("U" . $i, $notice['modification_name']);
                    $i++;
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            $writer->save($this->getParameter('upload_directory') . '/avito.xlsx');
        } catch (Exception|\PhpOffice\PhpSpreadsheet\Exception $e) {
        }
        return $this->json([]);
    }

    /**
     * @Route("/excelStock", name=".excelStock")
     * @param AvitoNoticeFetcher $fetcher
     * @return Response
     */
    public function excelStock(AvitoNoticeFetcher $fetcher): Response
    {
        try {
            $spreadsheet = new Spreadsheet();
            $aSheet = $spreadsheet->getActiveSheet();
            $aSheet->getPageMargins()->setTop(0);
            $aSheet->getPageMargins()->setLeft(0);
            $aSheet->getPageMargins()->setRight(0);
            $aSheet->getPageMargins()->setBottom(0);
            $aSheet
                ->getPageSetup()
                ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

            $aSheet->setCellValue("A1", "Date");
            $aSheet->setCellValue("B1", (new DateTime())->format('Y-m-d\TH:i:s'));

            $aSheet->setCellValue("A3", "Id");
            $aSheet->setCellValue("B3", "AvitoId");
            $aSheet->setCellValue("C3", "Stock");


            $i = 4;

            $notices = $fetcher->allForExcel(ZapSklad::OSN_SKLAD_ID);

            foreach ($notices as $notice) {
                if ($notice['quantity'] > 0) {
                    $aSheet->setCellValue("A" . $i, $notice['id']);
                    $aSheet->setCellValue("B" . $i, $notice['avito_id']);
                    $aSheet->setCellValue("C" . $i, $notice['quantity']);
                    $i++;
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            $writer->save($this->getParameter('upload_directory') . '/avito_stock.xlsx');
        } catch (Exception|\PhpOffice\PhpSpreadsheet\Exception $e) {
        }
        return $this->json([]);
    }

    /**
     * @Route("/uploadAutoCatalog", name=".uploadAutoCatalog")
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function uploadAutoCatalog(EntityManagerInterface $em): Response
    {
        ini_set('max_execution_time', '900');
        ini_set('memory_limit', '4000M');
        ini_set('post_max_size', '60M');
        ini_set('upload_max_filesize', '60M');

        $connection = $em->getConnection();
        try {
            $connection->createQueryBuilder()->delete('avito_makes')->executeStatement();
            $connection->createQueryBuilder()->delete('avito_models')->executeStatement();
            $connection->createQueryBuilder()->delete('avito_generations')->executeStatement();
            $connection->createQueryBuilder()->delete('avito_modifications')->executeStatement();
        } catch (Exception $e) {
        }

        $xml = simplexml_load_file($this->getParameter('images_directory') . '/../../Autocatalog.xml');
        $makes = $xml->children();
        foreach ($makes as $make) {
            try {
                $connection->createQueryBuilder()->insert('avito_makes')->values(
                    [
                        'id' => '?',
                        'name' => '?'
                    ]
                )->setParameters(
                    [
                        $make->attributes()['id'],
                        $make->attributes()['name']
                    ]
                )->executeStatement();
            } catch (Exception $e) {
            }
            if ($make->Model) {
                foreach ($make->Model as $model) {
                    try {
                        $connection->createQueryBuilder()->insert('avito_models')->values(
                            [
                                'id' => '?',
                                'name' => '?',
                                'avito_make_id' => '?'
                            ]
                        )->setParameters(
                            [
                                $model->attributes()['id'],
                                $model->attributes()['name'],
                                $make->attributes()['id']
                            ]
                        )->executeStatement();
                    } catch (Exception $e) {
                    }

                    if ($model->Generation) {
                        foreach ($model->Generation as $generation) {
                            try {
                                $connection->createQueryBuilder()->insert('avito_generations')->values(
                                    [
                                        'id' => '?',
                                        'name' => '?',
                                        'avito_model_id' => '?'
                                    ]
                                )->setParameters(
                                    [
                                        $generation->attributes()['id'],
                                        $generation->attributes()['name'],
                                        $model->attributes()['id']
                                    ]
                                )->executeStatement();
                            } catch (Exception $e) {
                            }

                            if ($generation->Modification) {
                                foreach ($generation->Modification as $modification) {
                                    try {
                                        $connection->createQueryBuilder()->insert('avito_modifications')->values(
                                            [
                                                'id' => '?',
                                                'name' => '?',
                                                'avito_generation_id' => '?'
                                            ]
                                        )->setParameters(
                                            [
                                                $modification->attributes()['id'],
                                                $modification->attributes()['name'],
                                                $generation->attributes()['id']
                                            ]
                                        )->executeStatement();
                                    } catch (Exception $e) {
                                    }

                                }
                            }
                        }
                    }

                }
            }
        }
        return $this->json([]);
    }
}
