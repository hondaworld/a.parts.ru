<?php

namespace App\Model\Sklad\Service;

use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Detail\CreaterFetcher;
use App\Service\Price\PartPriceService;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExcelHelperSummary
{
    private array $creaters;
    private string $fileName;
    private PartPriceService $partPriceService;
    private string $fileDir;

    public function __construct(
        ParameterBagInterface $parameterBag,
        PartPriceService      $partPriceService,
        CreaterFetcher        $createrFetcher
    )
    {
        $this->partPriceService = $partPriceService;
        $this->creaters = $createrFetcher->assoc();
        $this->fileDir = $parameterBag->get('price_directory') . '/email/';
    }

    /**
     * @param Opt $opt
     * @param array $providerPrices
     * @throws \Doctrine\DBAL\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveAndGet(Opt $opt, array $providerPrices = [264, 277, 227, 247]): void
    {
        $writer = $this->createExcel($opt, $providerPrices);
        $path = $this->fileDir . $this->fileName;
        @unlink($path);
        $writer->save($path);
        $this->generateContentType();
        $writer->save('php://output');
    }

    /**
     * @param Opt $opt
     * @param array $providerPrices
     * @throws \Doctrine\DBAL\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save(Opt $opt, array $providerPrices = [264, 277, 227, 247]): void
    {
        $writer = $this->createExcel($opt, $providerPrices);
        $path = $this->fileDir . $this->fileName;
        @unlink($path);
        $writer->save($path);
    }

    /**
     * @param Opt $opt
     * @param array $providerPrices
     * @throws Exception
     */
    public function get(Opt $opt, array $providerPrices): void
    {
        $writer = $this->createExcel($opt, $providerPrices);
        $this->generateContentType();
        $writer->save('php://output');
    }

    private function generateContentType(): void
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $this->fileName . '"');
        header('Cache-Control: max-age=0');
    }

    /**
     * @param Opt $opt
     * @param array $providerPrices
     * @return IWriter
     * @throws \Doctrine\DBAL\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    private function createExcel(Opt $opt, array $providerPrices): IWriter
    {
        $this->fileName = $this->generateFileName($opt->getNumber());
        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);
        $aSheet = $this->header($aSheet);
        $this->body($aSheet, $opt, $providerPrices);
        return IOFactory::createWriter($spreadsheet, 'Xls');
    }

    public function generateFileName(int $optNUmber): string
    {
        return 'PartsRu_summary_' . ($optNUmber - 1) . '.xls';
    }

    private function header($aSheet)
    {
        $arStyles = $this->getStyles();
        $aSheet->getStyle('A1:E1')->applyFromArray($arStyles['headerFont']);
        $aSheet->getColumnDimension("E")->setWidth(10);
        $aSheet->setCellValue("A1", "Производитель");
        $aSheet->setCellValue("B1", "Наименование");
        $aSheet->setCellValue("C1", "Номер детали");
        $aSheet->setCellValue("D1", "Остаток");
        $aSheet->setCellValue("E1", "Цена");
        return $aSheet;
    }

    /**
     * @param $aSheet
     * @param Opt $opt
     * @param array $providerPrices
     * @throws \Doctrine\DBAL\Exception
     */
    private function body($aSheet, Opt $opt, array $providerPrices): void
    {
        $arStyles = $this->getStyles();
        $parts = $this->partPriceService->byProviderPrices($opt, $providerPrices);

        $j = 2;
        foreach ($parts as &$part) {

            $aSheet->getStyle('A' . $j . ':C' . $j)->applyFromArray($arStyles['textFont']);
            $aSheet->setCellValue("A" . $j . "", $part['createrName']);

            $aSheet->setCellValue("B" . $j . "", $part['name']);

            $aSheet->getStyle('C' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->setCellValue("C" . $j . "", " " . $part['number']);

            $aSheet->getStyle('D' . $j . ':E' . $j)->applyFromArray($arStyles['tablePriceFont']);
            $aSheet->setCellValue("D" . $j . "", $part['quantity']);

            $aSheet->getStyle('E' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->setCellValue('E' . $j . "", $part['price1']);

            $j++;
        }

    }

    private function getStyles(): array
    {
        return [
            'headerFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '10',
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'textFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '10',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'tablePriceFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '10',
                    'bold' => false
                ),
                'alignment' => array(
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            )
        ];
    }
}