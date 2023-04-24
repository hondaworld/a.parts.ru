<?php

namespace App\Command;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
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

class AvitoStocksCommand extends Command
{
    protected static $defaultName = 'app:avito-stocks';
    protected static $defaultDescription = 'Обновление количества в объявлениях Авито';
    private ParameterBagInterface $parameterBag;
    private AvitoNoticeFetcher $fetcher;

    public function __construct(ParameterBagInterface $parameterBag, AvitoNoticeFetcher $fetcher)
    {
        parent::__construct();
        $this->parameterBag = $parameterBag;
        $this->fetcher = $fetcher;
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

            $aSheet->setCellValue("A1", "Date");
            $aSheet->setCellValue("B1", (new \DateTime())->format('Y-m-d\TH:i:s'));
            $aSheet->setCellValue("A3", "Id");
            $aSheet->setCellValue("B3", "AvitoId");
            $aSheet->setCellValue("C3", "Stock");

            $i = 4;

            $notices = $this->fetcher->allForExcel(ZapSklad::OSN_SKLAD_ID);

            foreach ($notices as $notice) {
                if ($notice['quantity'] > 0) {
                    $aSheet->setCellValue("A" . $i, $notice['id']);
                    $aSheet->setCellValue("B" . $i, $notice['avito_id']);
                    $aSheet->setCellValue("C" . $i, $notice['quantity']);
                    $i++;
                }
            }

            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

            $writer->save($this->parameterBag->get('upload_directory') . '/avito_stock.xlsx');
            $io->success('Avito stock файл создан');
            return Command::SUCCESS;
        } catch (Exception|\PhpOffice\PhpSpreadsheet\Exception $e) {
            $io->error($e->getMessage());
        }

        return Command::FAILURE;
    }
}
