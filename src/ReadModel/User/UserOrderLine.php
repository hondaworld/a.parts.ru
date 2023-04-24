<?php


namespace App\ReadModel\User;


use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\User\Entity\User\User;
use App\ReadModel\Detail\CreaterFetcher;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserOrderLine
{
    private string $number = '';
    private string $creater = '';
    private int $createrID = 0;
    private string $order = '';
    private string $price = '';
    private string $quantity = '';

    private $user;
    private $line;
    private $creaters;

    public static $chars = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z"];

    public static function fromCsv(User $user, array $line, array $creaters): self
    {
        if ($user->getPrice()->getQuantityNum() != "") $quantity = intval($line[intval($user->getPrice()->getQuantityNum())] ?? ''); else $quantity = 0;
        if ($user->getPrice()->getNumberNum() != "") $number = $line[intval($user->getPrice()->getNumberNum())] ?? ''; else $number = '';
        if ($user->getPrice()->getPriceNum() != "") $price = $line[intval($user->getPrice()->getPriceNum())] ?? ''; else $price = '';
        if ($user->getPrice()->getCreaterNum() != "") $creater = $line[intval($user->getPrice()->getCreaterNum())] ?? ''; else $creater = '';
        if ($user->getPrice()->getOrderNum() != "") $order = $line[intval($user->getPrice()->getOrderNum())] ?? ''; else $order = '';

        return new self($user, $quantity, $number, $price, $creater, $order, $creaters);
    }

    public static function fromXls(User $user, Worksheet $objWorksheet, int $numRow, array $creaters): self
    {
        if ($user->getPrice()->getQuantityNum() != "") $quantity = intval(trim($objWorksheet->getCell(self::$chars[$user->getPrice()->getQuantityNum()] . $numRow)->getValue())); else $quantity = 0;
        if ($user->getPrice()->getNumberNum() != "") $number = trim($objWorksheet->getCell(self::$chars[$user->getPrice()->getNumberNum()] . $numRow)->getValue()); else $number = '';
        if ($user->getPrice()->getPriceNum() != "") $price = trim($objWorksheet->getCell(self::$chars[$user->getPrice()->getPriceNum()] . $numRow)->getValue()); else $price = '';
        if ($user->getPrice()->getCreaterNum() != "") $creater = trim($objWorksheet->getCell(self::$chars[$user->getPrice()->getCreaterNum()] . $numRow)->getValue()); else $creater = '';
        if ($user->getPrice()->getOrderNum() != "") $order = trim($objWorksheet->getCell(self::$chars[$user->getPrice()->getOrderNum()] . $numRow)->getValue()); else $order = '';

        return new self($user, $quantity, $number, $price, $creater, $order, $creaters);
    }

    public function __construct(User $user, int $quantity, string $number, string $price, string $creater, string $order, array $creaters)
    {
        $this->user = $user;
        $this->creaters = $creaters;
        $this->quantity = $quantity;
        $this->number = $number;
        $this->price = $price;
        $this->creater = $creater;
        $this->order = $order;

        if ($user->getPrice()->getCreaterNum() != "") {
            $createrID = $this->getCreaterIDFromName($creater);
        } else {
            $createrID = $user->getPriceCreater();
        }
        if ($createrID) {
            $this->createrID = $createrID;
            $this->creater = $this->creaters[$createrID]['name'];
        }

    }

    private function getCreaterIDFromName($name): ?int
    {
        foreach ($this->creaters as $createrID => $creater) {
            if (strtolower($creater['name']) == strtolower($name)) return $createrID;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getNumber(): string
    {
        return trim(strtoupper(preg_replace("/[\-\.\ \'\"\=]/", "", trim($this->number)))) ?: '';
    }

    /**
     * @return string
     */
    public function getCreater(): string
    {
        return $this->creater;
    }

    /**
     * @return int
     */
    public function getCreaterID(): int
    {
        return $this->createrID;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        $regexpPrice = "/[^\.\,0-9]/";
        $price = preg_replace($regexpPrice, "", trim($this->price));
        $price = floatval(str_replace(",", ".", $price));
        return $price;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return intval(preg_replace("/[^0-9]/", "", trim($this->quantity)));
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return trim($this->order);
    }

    public function iconv_text(string $str): string
    {
        return mb_convert_encoding($str, "UTF-8", "Windows-1251");
    }

    public function is_utf(string $str): string
    {
        if (mb_convert_encoding($str, "UTF-8", "UTF-8") == $str)
            return true;
        else
            return false;
    }

}