<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;

class ProviderInvoiceLine
{
    private string $number = '';
    private int $number_type = 0;
    private string $number_razd = '';
    private string $summ = '';
    private string $price = '';
    private string $quantity = '';
    private string $gtd = '';
    private string $country = '';
    private float $priceAdd = 0;

    private $providerInvoice;

    public function __construct(ProviderInvoice $providerInvoice, array $line)
    {
        $this->providerInvoice = $providerInvoice;

        if ($providerInvoice->getNum()->getQuantity() != "") $this->quantity = $line[intval($providerInvoice->getNum()->getQuantity())] ?? '';
        if ($providerInvoice->getNum()->getNumber() != "") $this->number = $line[intval($providerInvoice->getNum()->getNumber())] ?? '';
        if ($providerInvoice->getNum()->getPrice() != "") $this->price = $line[intval($providerInvoice->getNum()->getPrice())] ?? '';
        $this->number_type = $providerInvoice->getNum()->getNumberType();
        $this->number_razd = $providerInvoice->getNum()->getNumberRazd();
        $this->priceAdd = $providerInvoice->getPriceAdd();
        if ($providerInvoice->getNum()->getSumm() != "") $this->summ = $line[intval($providerInvoice->getNum()->getSumm())] ?? '';
        if ($providerInvoice->getNum()->getGtd() != "") $this->gtd = $line[intval($providerInvoice->getNum()->getGtd())] ?? '';
        if ($providerInvoice->getNum()->getCountry() != "") $this->country = $line[intval($providerInvoice->getNum()->getCountry())] ?? '';

        if ($this->number_type != 0) {
            $num_number_razd = $this->number_razd != '' ? $this->number_razd : ' ';
            $arNumber = explode($num_number_razd, $this->number);
            if ($arNumber) {
                if ($this->number_type == 1) $this->number = $arNumber[0];
                else if ($this->number_type == 2) $this->number = $arNumber[count($arNumber) - 1];
            }
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
     * @return float
     */
    public function getPrice(): float
    {
        $regexpPrice = "/[^\.\,0-9]/";

        $price = preg_replace($regexpPrice, "", trim($this->price));
        $price = floatval(str_replace(",", ".", $price));

        $summ = preg_replace($regexpPrice, "", trim($this->summ));
        $summ = floatval(str_replace(",", ".", $summ));

        if ($this->providerInvoice->getNum()->getSumm() != "") {
            if ($this->getQuantity() == 0)
                $price = 0;
            else
                $price = round($summ / $this->getQuantity() * 100) / 100;
        } else {
            if ($this->priceAdd != 0) $price = round($price * $this->priceAdd * 100) / 100;
        }

        return $price;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
//        return intval(preg_replace("/[^0-9]/", "", trim($this->quantity)));
        if (!is_numeric(trim($this->quantity))) return 0;
        return intval(trim($this->quantity));
    }

    /**
     * @return string
     */
    public function getGtd(): string
    {
        $gtd = trim($this->gtd);
        if (!$this->is_utf($gtd)) $gtd = $this->iconv_text($gtd);
        return $gtd;
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        $country = trim($this->country);
        if (!$this->is_utf($country)) $country = $this->iconv_text($country);
        return $country;
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