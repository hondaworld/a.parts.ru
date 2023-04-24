<?php

namespace App\Command;

use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\Opt\OptRepository;
use App\ReadModel\Reseller\AvitoNoticeFetcher;
use Doctrine\DBAL\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AvitoNoticesCommand extends Command
{
    protected static $defaultName = 'app:avito-notices';
    protected static $defaultDescription = 'Обновление объявлений Авито';
    private ParameterBagInterface $parameterBag;
    private AvitoNoticeFetcher $fetcher;
    private ZapCardPriceService $zapCardPriceService;
    private OptRepository $optRepository;
    private ZapCardRepository $zapCardRepository;

    public function __construct(ParameterBagInterface $parameterBag, AvitoNoticeFetcher $fetcher, ZapCardPriceService $zapCardPriceService, OptRepository $optRepository, ZapCardRepository $zapCardRepository)
    {
        parent::__construct();
        $this->parameterBag = $parameterBag;
        $this->fetcher = $fetcher;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->optRepository = $optRepository;
        $this->zapCardRepository = $zapCardRepository;
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        try {
            $spreadsheet = new Spreadsheet();
            $aSheet = $spreadsheet->getActiveSheet();
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

            $notices = $this->fetcher->allForExcel(ZapSklad::OSN_SKLAD_ID);

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
                    $aSheet->setCellValue("K" . $i, $this->zapCardPriceService->priceOpt($this->zapCardRepository->get($notice['zapCardID']), $this->optRepository->get(Opt::DEFAULT_OPT_ID)));
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

            $writer->save($this->parameterBag->get('upload_directory') . '/avito.xlsx');
            $io->success('Avito файл создан');
            return Command::SUCCESS;
        } catch (Exception|\PhpOffice\PhpSpreadsheet\Exception $e) {
            $io->error($e->getMessage());
        }

        return Command::FAILURE;
    }
}
