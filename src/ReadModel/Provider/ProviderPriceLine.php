<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\Price\ProviderPrice;
use App\ReadModel\Detail\CreaterFetcher;

class ProviderPriceLine
{
    private string $number = '';
    private string $creater = '';
    private int $createrID = 0;
    private string $name = '';
    private string $price = '';
    private string $quantity = '';
    private string $creater_add = '';

    private $providerPrice;
    private $line;
    private $creaters;

    public function __construct(ProviderPrice $providerPrice, array $line, array $creaters)
    {
        $this->providerPrice = $providerPrice;
        $this->line = $line;
        $this->creaters = $creaters;

//        if ($providerPrice->getCreater()) $this->createrID = $providerPrice->getCreater()->getId();
        if ($providerPrice->getNum()->getQuantity() != "") $this->quantity = $line[intval($providerPrice->getNum()->getQuantity())] ?? '';
        if ($providerPrice->getNum()->getNumber() != "") $this->number = $line[intval($providerPrice->getNum()->getNumber())] ?? '';
        if ($providerPrice->getNum()->getPrice() != "") $this->price = $line[intval($providerPrice->getNum()->getPrice())] ?? '';
        if ($providerPrice->getNum()->getName() != "") $this->name = $line[intval($providerPrice->getNum()->getName())] ?? '';
        if ($providerPrice->getNum()->getCreaterAdd() != "") $this->creater_add = $line[intval($providerPrice->getNum()->getCreaterAdd())] ?? '';


        // Регулярное выражение не трогать!!! Оно используется для таких цен: 9,999,999.99
//            if ((!in_array($row->providerID, array(14, 52, 29, 46, 19, 1, 2, 3, 76, 21, 31, 80))) && (preg_match("/[0-9]+\,[0-9]{3}/", $price)))
//                $price = str_replace(",", "", $price);
//            else
//                $price = str_replace(",", ".", $price);


        /* Производитель */
        if ($providerPrice->getNum()->getCreater() != "") {
            $this->creater = trim($line[intval($providerPrice->getNum()->getCreater())]);
            if (!$this->is_utf($this->creater)) $this->creater = $this->iconv_text($this->creater);

            if ($this->creater != "") {
                foreach ($this->creaters as $createrID => $creater) {
                    if (strtolower($creater['name']) == strtolower($this->creater)) {
                        $this->createrID = $createrID;
//                        if ($arCreaters[$Data['createrID']]) $arCreaters[$Data['createrID']]['count']++; else $arCreaters[$Data['createrID']] = ['name' => $arAllCreater['name'], 'count' => 1];
                        break;
                    } elseif (trim($creater['alt_names']) != '') {
                        $alt_names = explode("\n", trim($creater['alt_names']));
                        if ($alt_names) {
                            foreach ($alt_names as $alt_name) {
                                if (trim($alt_name) != '' && strtolower(trim($alt_name)) == strtolower($this->creater)) {
                                    $this->createrID = $createrID;
//                                    if ($arCreaters[$Data['createrID']]) $arCreaters[$Data['createrID']]['count']++; else $arCreaters[$Data['createrID']] = ['name' => $arAllCreater['name'], 'count' => 1];
                                    break;
                                }
                            }
                        }
                    }
                    if ($this->createrID) break;
//                }
//
            }
//            if ($Data['createrID'] == 0 && $this->creater != "") {
//                if ($arCreatersNotFound[$this->creater]) $arCreatersNotFound[$this->creater]++; else $arCreatersNotFound[$this->creater] = 1;
            }
        } else {
            if ($providerPrice->getCreater()) {
                $this->createrID = $providerPrice->getCreater()->getId();
            } else {
                $this->createrID = 0;
            }

//            if ($arCreaters[$Data['createrID']]) $arCreaters[$Data['createrID']]['count']++; else $arCreaters[$Data['createrID']] = ['name' => Creater::getCreater($Data['createrID'])['name'], 'count' => 1];
        }

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
     * @return string
     */
    public function getName(): string
    {
        $name = preg_replace("/[\(\)]/", "", trim($this->name));
        if (!$this->is_utf($name)) $name = $this->iconv_text($name);
        return $name;
    }

    /**
     * @return float
     */
    public function getPrice(): float
    {
        $regexpPrice = "/[^\.\,0-9]/";
        if ($this->providerPrice->getPrice()->getRazdDecimal() == ".") $regexpPrice = "/[^\.0-9]/";
        elseif ($this->providerPrice->getPrice()->getRazdDecimal() == ",") $regexpPrice = "/[^\,0-9]/";

        $price = preg_replace($regexpPrice, "", trim($this->price));
        $price = floatval(str_replace(",", ".", $price));

        if (($this->providerPrice->getNum()->getRg() != "") && ($this->providerPrice->getPrice()->getRgValue() != "")) {
            $rg = trim($this->line[intval($this->providerPrice->getNum()->getRg())]);

            $rgValues = $this->getRgValue();

            if (isset($rgValues[$rg])) {
                $price = $price - $price * floatval($rgValues[$rg]) / 100;
            }
        }

        return $price;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return intval(preg_replace("/[^\.0-9]/", "", trim($this->quantity)));
    }

    /**
     * @return string
     */
    public function getCreaterAdd(): string
    {
        return !$this->is_utf($this->creater_add) ? $this->iconv_text($this->creater_add) : $this->creater_add;
    }

    public function getRgValue(): array
    {
        $rgValue = [];
        if ($this->providerPrice->getPrice()->getRgValue() != "") {
            $rgArr = explode("\n", $this->providerPrice->getPrice()->getRgValue());
            foreach ($rgArr as $k => $v) {
                $val = explode(";", $v);
                if (is_array($val)) {
                    $rgValue[$val[0]] = $val[1];
                }
            }
        }
        return $rgValue;
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