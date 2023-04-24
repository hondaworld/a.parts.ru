<?php


namespace App\Service\Invoice;


use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\ReadModel\Provider\ProviderInvoiceLine;
use App\Service\EmailUploader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class InvoiceUploader extends EmailUploader
{

    public function __construct(string $targetDirectory)
    {
        parent::__construct($targetDirectory);
    }

    public function uploadPriceAndDelete(ProviderInvoice $providerInvoice): array
    {
        $invoices = $this->uploadPrice($providerInvoice);
        $this->delete();
        return $invoices;
    }

    public function uploadPrice(ProviderInvoice $providerInvoice): array
    {
        $arInvoice = [];

        $DataFile = fopen($this->getFullFileName(), "r");
        while (!feof($DataFile)) {
            $line = $this->getCsvLine($DataFile, ';');

            if ($line) {
                $providerInvoiceLine = new ProviderInvoiceLine($providerInvoice, $line);

                if (($providerInvoiceLine->getNumber() != "") && (strlen($providerInvoiceLine->getNumber()) > 2) && ($providerInvoiceLine->getQuantity() > 0)) {
                    $arInvoice[] = array(
                        "number" => $providerInvoiceLine->getNumber(),
                        "price" => $providerInvoiceLine->getPrice(),
                        "quantity" => $providerInvoiceLine->getQuantity(),
                        "gtd" => $providerInvoiceLine->getGtd(),
                        "country" => $providerInvoiceLine->getCountry(),
                    );
                }
            }
        }

        return $arInvoice;
    }

    public function xlsToCsv(ProviderInvoice $providerInvoice)
    {
        $fileName = $this->fileName;

        if (in_array($this->getExtension($fileName), ['xls', 'xlsx'])) {
            $inputFileType = IOFactory::identify($this->getFullFileName());

            if (in_array($inputFileType, ['Xlsx', 'Xls'])) {
                $reader = IOFactory::createReader($inputFileType);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($this->getFullFileName());

                $this->fileName = $providerInvoice->getPrice() != ''
                    ? $providerInvoice->getPrice()
                    : substr($this->fileName, 0, strpos($this->fileName, '.')) . ".csv";

                $writer = new Csv($spreadsheet);
                $writer->setUseBOM(true);
                $writer->setDelimiter(';');
                $writer->setEnclosure('"');
                $writer->setLineEnding("\r\n");
                $writer->save($this->getFullFileName());

                @unlink($this->targetDirectory() . '/' . $fileName);
            }
        } else {
            $this->fileName = $providerInvoice->getPrice() != ''
                ? $providerInvoice->getPrice()
                : $this->fileName;
            rename($this->targetDirectory() . '/' . $fileName, $this->getFullFileName());
        }
    }

}