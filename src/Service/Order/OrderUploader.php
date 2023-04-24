<?php


namespace App\Service\Order;


use App\Model\User\Entity\User\User;
use App\ReadModel\User\UserOrderLine;
use App\Service\EmailUploader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OrderUploader extends EmailUploader
{

    public function __construct(string $targetDirectory)
    {
        parent::__construct($targetDirectory);
    }

    /**
     * @param User $user
     * @param Spreadsheet $spreadsheet
     * @param array $creaters
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function readXls(User $user, Spreadsheet $spreadsheet, array $creaters): array
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $highestRow = $aSheet->getHighestRow();
        if ($highestRow > 300) $highestRow = 300;
//        $orderID = create_order($userID);

        $arr = [];

        for ($row = ($user->getPrice()->getLineForRead()); ($row <= $highestRow); $row++) {
            $arr[] = UserOrderLine::fromXls($user, $aSheet, $row, $creaters);
        }
        return $this->getLineData($arr);
    }

    /**
     * @param User $user
     * @param string $filename
     * @param array $creaters
     * @return array
     */
    public function readCsv(User $user, string $filename, array $creaters): array
    {
        $arr = [];
        $row = 1;
        $DataFile = fopen($filename, "r");
        while (!feof($DataFile)) {
            $line = $this->getCsvLine($DataFile, ';');

            if ($line && $row >= $user->getPrice()->getLineForRead()) {
                $arr[] = UserOrderLine::fromCsv($user, $line, $creaters);
            }

            $row++;
        }
        return $this->getLineData($arr);
    }

    /**
     * @param UserOrderLine[] $lines
     * @return array
     */
    private function getLineData(array $lines): array
    {
        $arr = [];
        foreach ($lines as $line) {
            if (($line->getNumber() != "") && ($line->getQuantity() != "") && is_numeric($line->getQuantity()) && ($line->getQuantity() > 0)) {
                $arr[] = [
                    "number" => $line->getNumber(),
                    "price" => $line->getPrice(),
                    "quantity" => $line->getQuantity(),
                    "createrID" => $line->getCreaterID(),
                    "creater" => $line->getCreater(),
                    "order" => $line->getOrder(),
                    "reserve" => 0,
                    "comment" => ''
                ];
            }
        }
        return $arr;
    }

    /**
     * @return Spreadsheet
     * @throws Exception
     */
    public function openStandartXls(): Spreadsheet
    {
        return $this->openXls($this->targetDirectory . '/standart.xls');
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileType(string $fileName): string
    {
        return IOFactory::identify($fileName);
    }

    /**
     * @param string $fileName
     * @return Spreadsheet
     * @throws Exception
     */
    public function openXls(string $fileName): Spreadsheet
    {
        $inputFileType = IOFactory::identify($fileName);
        $objReader = IOFactory::createReader($inputFileType); // создаем объект для чтения файла
        return $objReader->load($fileName); // загружаем данные файла в объект
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param string $fileName
     * @param string $fileType
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function saveXls(Spreadsheet $spreadsheet, string $fileName, string $fileType)
    {
        $this->fileName = $fileName;
        $objPriceWriter = IOFactory::createWriter($spreadsheet, $fileType);
        $objPriceWriter->save($this->getFullFileName());
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param array $rows
     * @param int $beginRow
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowStandart(Spreadsheet $spreadsheet, array $rows, int $beginRow): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $j = $beginRow;
        foreach ($rows as $row) {
            $aSheet->setCellValue("A" . $j . "", $row["order"]);
            $aSheet->setCellValue("B" . $j . "", $row["creater"]);
            $aSheet->setCellValue("C" . $j . "", " " . $row["number"]);
            $aSheet->setCellValue("D" . $j . "", $row["price"]);
            $aSheet->setCellValue("E" . $j . "", $row["quantity"]);
            $aSheet->setCellValue("F" . $j . "", $row["reserve"]);
            $aSheet->setCellValue("G" . $j . "", $row["price"] * $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":G" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":G" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":G" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowTest(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $chars = UserOrderLine::$chars;
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            $aSheet->setCellValue($chars[$user->getPrice()->getQuantityNum()] . $j . "", $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowAdeo(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $chars = UserOrderLine::$chars;
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            $aSheet->setCellValue($chars[$user->getPrice()->getQuantityNum()] . $j . "", $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowEmex(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $chars = UserOrderLine::$chars;
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            $aSheet->setCellValue($chars[$user->getPrice()->getQuantityNum()] . $j . "", $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":M" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowRmsAuto(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            if ($row["reserve"] < $row["quantity"]) {
                $aSheet->setCellValue("L" . $j . "", $row["quantity"]);
                $aSheet->setCellValue("M" . $j . "", $row["reserve"]);
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowAutoPiter(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            if ($row["reserve"] < $row["quantity"]) {
                $aSheet->setCellValue("F" . $j . "", ($row["quantity"] - $row["reserve"]));
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowAutodoc(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $chars = UserOrderLine::$chars;
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            $aSheet->setCellValue($chars[$user->getPrice()->getQuantityNum()] . $j . "", $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":E" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":E" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":E" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param User $user
     * @param array $rows
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function createRowExist(Spreadsheet $spreadsheet, User $user, array $rows): void
    {
        $aSheet = $spreadsheet->setActiveSheetIndex(0);
        $chars = UserOrderLine::$chars;
        $j = $user->getPrice()->getLineForRead();
        foreach ($rows as $row) {
            $aSheet->setCellValue($chars[$user->getPrice()->getQuantityNum()] . $j . "", $row["reserve"]);
            if ($row["reserve"] == 0) {
                $aSheet->getStyle("A" . $j . ":L" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FF6666');
            } else if ($row["reserve"] < $row["quantity"]) {
                $aSheet->getStyle("A" . $j . ":L" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFCA6E');
            } else {
                $aSheet->getStyle("A" . $j . ":L" . $j)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('5CFF5C');
            }
            $j++;
        }
    }

}