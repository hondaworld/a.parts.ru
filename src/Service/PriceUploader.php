<?php


namespace App\Service;


use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Provider\Entity\LogPrice\LogPrice;
use App\Model\Provider\Entity\LogPrice\LogPriceRepository;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAll;
use App\Model\Provider\Entity\LogPriceAll\LogPriceAllRepository;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Provider\PriceUploaderFetcher;
use App\ReadModel\Provider\ProviderPriceLine;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class PriceUploader extends EmailUploader
{

    public function __construct(string $targetDirectory)
    {
        parent::__construct($targetDirectory);
    }

    public function getFirstLines(ProviderPrice $providerPrice): array
    {
        $i = 1;
        $arData = [];
        $DataFile = fopen($this->getFullFileName(), "r");
        while (!feof($DataFile)) {
            $line = $this->getCsvLine($DataFile, $providerPrice->getPrice()->getRazdForUpload());
            $row = [];
            if ($line) {
                foreach ($line as $item) {
//                    $row[] = $item;
                    $row[] = !$this->is_utf($item) ? $this->iconv_text($item) : $item; // mb_convert_encoding($item, 'UTF-8', 'Windows-1251');
                }
                $arData[] = $row;
            }
            $i++;
            if ($i > 30) break;
        }
//        dump($arData);
        return $arData;
    }

    public function truncate(
        ProviderPrice         $providerPrice,
        CreaterFetcher        $createrFetcher,
        LogPriceRepository    $logPriceRepository,
        PriceUploaderFetcher  $priceUploaderFetcher,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $creaters = $createrFetcher->allArray();
        $arDeleted = $this->deletePrice($providerPrice, $priceUploaderFetcher);
        $this->log($providerPrice, $creaters, $logPriceRepository, $arDeleted, "Удалено");
        $providerPrice->updateFileData(0, [], []);
        $flusher->flush();
    }

    public function uploadPriceAndDelete(
        ProviderPrice         $providerPrice,
        CreaterFetcher        $createrFetcher,
        CreaterRepository     $createrRepository,
        PriceUploaderFetcher  $priceUploaderFetcher,
        LogPriceRepository    $logPriceRepository,
        LogPriceAllRepository $logPriceAllRepository,
        Flusher               $flusher
    )
    {
        $this->uploadPrice(
            $providerPrice,
            $createrFetcher,
            $createrRepository,
            $priceUploaderFetcher,
            $logPriceRepository,
            $logPriceAllRepository,
            $flusher
        );
        $this->delete();
    }

    public function uploadPrice(
        ProviderPrice         $providerPrice,
        CreaterFetcher        $createrFetcher,
        CreaterRepository     $createrRepository,
        PriceUploaderFetcher  $priceUploaderFetcher,
        LogPriceRepository    $logPriceRepository,
        LogPriceAllRepository $logPriceAllRepository,
        Flusher               $flusher
    )
    {
        $this->flusher = $flusher;
        $creaters = $createrFetcher->allArray();
//        $createrModels = $createrRepository->all();

        if ($providerPrice->getPrice()->getPriceCopy() != '') {
            $this->copy($providerPrice->getPrice()->getPriceCopy());
        }

        $priceUploaderFetcher->uploadingPriceBegin($providerPrice->getPrice()->getPrice());

        $providerPrice->updateFileData(0, [], []);
        $this->flusher->flush();

        $arDeleted = [];
        if (!$providerPrice->getPrice()->isUpdate()) {
            $arDeleted = $this->deletePrice($providerPrice, $priceUploaderFetcher);

            $this->log($providerPrice, $creaters, $logPriceRepository, $arDeleted, "Удалено");
        }

        $i = 1;
        $arCreaters = [];
        $arCreatersNotFound = [];


        $comment = "<table class='table table-striped border'>";

        $comment .= "<tr>";
        for ($k = 0; $k <= $providerPrice->getNum()->getMaxCol(); $k++) {
            $comment .= "<th class='table-primary'>" . $providerPrice->getNum()->getLabelFromColNum($k) . "</th>";
        }
        $comment .= "</tr>";

        $DataFile = fopen($this->getFullFileName(), "r");
        while (!feof($DataFile)) {
            $line = $this->getCsvLine($DataFile, $providerPrice->getPrice()->getRazdForUpload());

            if ($line) {
                $providerPriceLine = new ProviderPriceLine($providerPrice, $line, $creaters);
                $Data = [
                    'createrID' => $providerPriceLine->getCreaterID(),
                    'number' => $providerPriceLine->getNumber(),
                    'name' => $providerPriceLine->getName(),
                    'price' => $providerPriceLine->getPrice(),
                    'quantity' => $providerPriceLine->getQuantity(),
                    'providerPriceID' => $providerPrice->getId(),
                ];

                if (isset($arCreaters[$Data['createrID']])) {
                    $arCreaters[$Data['createrID']]++;
                } elseif (isset($creaters[$Data['createrID']])) {
                    $arCreaters[$Data['createrID']] = 1;
                }

                if ($Data['createrID'] == 0 && $providerPriceLine->getCreater() != "") {
                    if (isset($arCreatersNotFound[$providerPriceLine->getCreater()])) {
                        $arCreatersNotFound[$providerPriceLine->getCreater()]++;
                    } else {
                        $arCreatersNotFound[$providerPriceLine->getCreater()] = 1;
                    }
                }

                /* Пишем лог */
                if (($i > 10) && ($i <= 13)) {
                    $comment .= "<tr><td>";
                    if ($providerPriceLine->is_utf(implode("</td><td>", $line))) $comment .= implode("</td><td>", $line); else $comment .= $providerPriceLine->iconv_text(implode("</td><td>", $line));
                    $comment .= "</td></tr>";
                }

                $this->insertPrice($providerPrice, $creaters, $priceUploaderFetcher, $Data);

                $i++;
            }
        }


        $this->log($providerPrice, $creaters, $logPriceRepository, $arCreaters, $providerPrice->getPrice()->isUpdate() ? "Обновлено" : "Добавлено");

        $countDelete = 0;
        if (count($arDeleted) > 0) {
            $countDelete = array_sum($arDeleted);
        }

        $countInsert = 0;
        if (count($arCreaters) > 0) {
            $countInsert = array_sum($arCreaters);
        }

        $comment .= "</table>";

        $logPriceAll = new LogPriceAll($providerPrice, $countDelete, $countInsert, $comment);
        $logPriceAllRepository->add($logPriceAll);

        $providerPrice->updateFileData($countInsert, $arCreaters, $arCreatersNotFound);
        $this->flusher->flush();

        if (count($providerPrice->getChildrenProviderPrices()) > 0) {
            foreach ($providerPrice->getChildrenProviderPrices() as $childrenProviderPrice) {
                $this->deletePrice($childrenProviderPrice, $priceUploaderFetcher);
                $this->log($childrenProviderPrice, $creaters, $logPriceRepository, $arDeleted, "Удалено");
                $this->copyPrice($providerPrice, $childrenProviderPrice, $priceUploaderFetcher);
                $this->log($childrenProviderPrice, $creaters, $logPriceRepository, $arCreaters, "Добавлено");
                $logPriceAll = new LogPriceAll($childrenProviderPrice, $countDelete, $countInsert, 'Копирование с родителя');
                $childrenProviderPrice->updateFileData($countInsert, $arCreaters, $arCreatersNotFound);
                $logPriceAllRepository->add($logPriceAll);
            }
            $this->flusher->flush();
        }

//        dump($arDeleted);
//        dump($arCreaters);
//        dump($arCreatersNotFound);

        $priceUploaderFetcher->uploadingPriceEnd($providerPrice->getPrice()->getPrice());
//        return $arData;
    }

    public function log(ProviderPrice $providerPrice, array $creaters, LogPriceRepository $logPriceRepository, $arr, string $title)
    {
        if (count($arr) > 0) {
            foreach ($arr as $createrID => $count) {
                if (isset($creaters[$createrID])) {
                    $logPrice = new LogPrice($providerPrice, $creaters[$createrID]['name'], "$title $count записей");
                    $logPriceRepository->add($logPrice);
                }
            }
            $this->flusher->flush();
        }
    }

    public function deletePrice(ProviderPrice $providerPrice, PriceUploaderFetcher $priceUploaderFetcher): array
    {
        $arDeleted = $priceUploaderFetcher->getCountPrices('shopPriceN', $providerPrice->getId());

        if (count($arDeleted) > 0) $priceUploaderFetcher->deletePrices('shopPriceN', $providerPrice->getId());
        for ($i = 1; $i <= 10; $i++) {
            $arDeleted += $priceUploaderFetcher->getCountPrices('shopPrice' . $i, $providerPrice->getId());
            $priceUploaderFetcher->deletePrices('shopPrice' . $i, $providerPrice->getId());
        }

        return $arDeleted;
    }

    public function copyPrice(ProviderPrice $providerPrice, ProviderPrice $childrenProviderPrice, PriceUploaderFetcher $priceUploaderFetcher)
    {
        $priceUploaderFetcher->copyPrice('shopPriceN', $providerPrice->getId(), $childrenProviderPrice->getId());
        for ($i = 1; $i <= 10; $i++) {
            $priceUploaderFetcher->copyPrice('shopPrice' . $i, $providerPrice->getId(), $childrenProviderPrice->getId());
        }
    }

    public function insertPrice(ProviderPrice $providerPrice, array $creaters, PriceUploaderFetcher $priceUploaderFetcher, array $Data)
    {
        if (($Data['number'] != "") && ($Data['price'] != "") && is_numeric($Data['price']) && ($Data['price'] > 0) && $Data['createrID'] > 0) {
            if (($providerPrice->getPrice()->getPriceadd() > 0)) $Data['price'] = $Data['price'] * $providerPrice->getPrice()->getPriceadd();

            if ($creaters[$Data['createrID']]['isOriginal'] == 1) {
                if ($creaters[$Data['createrID']]['tableName'] != "") {
                    if ($providerPrice->getPrice()->isUpdate()) {
                        $priceUploaderFetcher->updatePrice($creaters[$Data['createrID']]['tableName'], $Data['number'], $Data['createrID'], $Data['providerPriceID'], $Data['price'], $Data['quantity']);
                    } else {
                        $priceUploaderFetcher->insertPrice($creaters[$Data['createrID']]['tableName'], $Data);
                    }
                }
            } else {
                if ($providerPrice->getPrice()->isUpdate()) {
                    $priceUploaderFetcher->updatePrice('shopPriceN', $Data['number'], $Data['createrID'], $Data['providerPriceID'], $Data['price'], $Data['quantity']);
                } else {
                    $priceUploaderFetcher->insertPrice('shopPriceN', $Data);
                }
            }
        }
    }

    public function setFileNameFromProviderPrice(ProviderPrice $providerPrice): bool
    {
        $fileName = $providerPrice->getPrice()->getPrice();
        if (file_exists($this->targetDirectory() . '/' . $fileName)) {
            $this->fileName = $fileName;
            return true;
        } elseif (file_exists($this->targetDirectory() . '/' . strtolower($fileName))) {
            $this->fileName = strtolower($fileName);
            return true;
        }
        return false;
    }

    public function xlsToCsv(ProviderPrice $providerPrice)
    {
        $fileName = $this->fileName;

        if (in_array($this->getExtension($fileName), ['xls', 'xlsx'])) {

            if ($providerPrice->getPrice()->isNotCheckExt()) {

                $this->fileName = $providerPrice->getPrice()->getPrice() != ''
                    ? $providerPrice->getPrice()->getPrice()
                    : substr($this->fileName, 0, strpos($this->fileName, '.')) . ".csv";

                rename($this->targetDirectory() . '/' . $fileName, $this->getFullFileName());

            } else {

                $inputFileType = IOFactory::identify($this->getFullFileName());

                if (in_array($inputFileType, ['Xlsx', 'Xls'])) {
                    $reader = IOFactory::createReader($inputFileType);
                    $reader->setReadDataOnly(true);
                    $spreadsheet = $reader->load($this->getFullFileName());

                    $this->fileName = $providerPrice->getPrice()->getPrice() != ''
                        ? $providerPrice->getPrice()->getPrice()
                        : substr($this->fileName, 0, strpos($this->fileName, '.')) . ".csv";

                    $writer = new Csv($spreadsheet);
                    $writer->setUseBOM(true);
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('"');
                    $writer->setLineEnding("\r\n");
                    $writer->save($this->getFullFileName());

//                    dump($this->fileName);
//                    dump($this->getFullFileName());

                    @unlink($this->targetDirectory() . '/' . $fileName);
                }
            }
        } else {
            $this->fileName = $providerPrice->getPrice()->getPrice() != ''
                ? $providerPrice->getPrice()->getPrice()
                : $this->fileName;
            rename($this->targetDirectory() . '/' . $fileName, $this->getFullFileName());
        }
    }

}