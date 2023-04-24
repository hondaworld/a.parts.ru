<?php

namespace App\Model\Sklad\Service;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Detail\CreaterFetcher;
use App\Service\Price\PartPriceService;
use DateTime;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ExcelHelper
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
     * @throws Exception
     */
    public function getAllFilesFromDir(): array
    {
        $arr = [];
        $files = scandir($this->fileDir);
        foreach ($files as $file) {
            if (!in_array($file, [".", ".."]) && !is_dir($this->fileDir . $file)) {
                $arr[$file] = $this->getFileNameDateTime($file);
            }
        }
        return $arr;
    }

    /**
     * @param int $optNumber
     * @param int|null $zapSkladID
     * @param bool $isSimple
     * @return string
     */
    public function generateAndGetFileName(int $optNumber, ?int $zapSkladID = null, bool $isSimple = false): string
    {
        return $this->generateFileName($optNumber, $zapSkladID, $isSimple);
    }

    /**
     * @param string $fileName
     * @return DateTime|null
     * @throws Exception
     */
    public function getFileNameDateTime(string $fileName): ?DateTime
    {
        return file_exists($this->fileDir . $fileName) ? (new DateTime())->setTimestamp(filectime($this->fileDir . $fileName)) : null;
    }

    /**
     * @param Opt $opt
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveAndGet(Opt $opt, ?ZapSklad $zapSklad = null, bool $isSimple = false): void
    {
        $writer = $this->createExcel($opt, $zapSklad, $isSimple);
        $path = $this->fileDir . $this->fileName;
        @unlink($path);
        $writer->save($path);
        $this->generateContentType();
        $writer->save('php://output');
    }

    /**
     * @param Opt $opt
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function save(Opt $opt, ?ZapSklad $zapSklad = null, bool $isSimple = false): void
    {
        $writer = $this->createExcel($opt, $zapSklad, $isSimple);
        $path = $this->fileDir . $this->fileName;
        @unlink($path);
        $writer->save($path);
    }

    /**
     * @param Opt $opt
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function get(Opt $opt, ?ZapSklad $zapSklad = null, bool $isSimple = false): void
    {
        $writer = $this->createExcel($opt, $zapSklad, $isSimple);
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
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @return IWriter
     * @throws Exception
     */
    private function createExcel(Opt $opt, ?ZapSklad $zapSklad, bool $isSimple): IWriter
    {
        $this->fileName = $this->generateFileName($opt->getNumber(), $zapSklad ? $zapSklad->getId() : null, $isSimple);
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
        $this->body($aSheet, $opt, $zapSklad, $isSimple);
        return IOFactory::createWriter($spreadsheet, 'Xls');
    }

    public function generateFileName(int $optNUmber, ?int $zapSkladID, bool $isSimple): string
    {
        $fileName = "price";
        $fileName .= '_PartsRu_' . ($optNUmber - 1);
        if ($zapSkladID) {
            $fileName .= '_' . $zapSkladID;
        }
        if ($isSimple) {
            $fileName .= '_sklad';
        }
        $fileName .= '.xls';
        return $fileName;
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
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @throws \Doctrine\DBAL\Exception
     */
    private function body($aSheet, Opt $opt, ?ZapSklad $zapSklad, bool $isSimple): void
    {
        $arStyles = $this->getStyles();
        $parts = $this->partPriceService->allInWarehouse($opt, $zapSklad, $isSimple);

        $j = 2;
        foreach ($parts as &$part) {
            if (in_array($part['createrID'], [2, 586, 587])) {
                $part['createrName'] = $this->creaters[$part['creater_weightID']];
            }

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