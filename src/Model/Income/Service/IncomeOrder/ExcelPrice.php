<?php


namespace App\Model\Income\Service\IncomeOrder;


use App\Model\Income\Entity\Order\IncomeOrder;
use App\Service\CsvUploadHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ExcelPrice
{
    protected CsvUploadHelper $helper;
    protected IncomeOrder $incomeOrder;
    protected string $pathIncomeFiles;
    protected string $zapSklad;

    public function __construct(IncomeOrder $incomeOrder, string $pathIncomeFiles)
    {
        $this->incomeOrder = $incomeOrder;
        $this->pathIncomeFiles = $pathIncomeFiles;
        $this->helper = new CsvUploadHelper();
        $this->zapSklad = $incomeOrder->getZapSklad()->getId() == 5 ? 'Spb' : 'Msk';
    }

    public function loadXls(string $path): Spreadsheet
    {
        $inputFileType = IOFactory::identify($path);  // узнаем тип файла, excel может хранить файлы в разных форматах, xls, xlsx и другие
        $reader = IOFactory::createReader($inputFileType); // создаем объект для чтения файла
        //$objPrice->setReadDataOnly(true);
        return $reader->load($path);
    }

    public function saveXls(string $path, Spreadsheet $spreadsheet): void
    {
        $writer = new Xls($spreadsheet);
        @unlink($path);
        $writer->save($path);
    }

    public function loadCsv(string $path)
    {
        @unlink($path);
        return fopen($path, 'w');
    }

    public function saveCsv($out): void
    {
        fclose($out);
    }
}