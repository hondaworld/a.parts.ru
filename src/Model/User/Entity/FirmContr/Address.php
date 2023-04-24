<?php


namespace App\Model\User\Entity\FirmContr;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Address
{
    /**
     * @ORM\Column(type="string", length=10)
     */
    private $zip;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $street;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $house;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $str;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $kv;

    public function __construct(?string $zip, ?string $street, ?string $house, ?string $str, ?string $kv)
    {
        $this->zip = $zip ?: '';
        $this->street = $street ?: '';
        $this->house = $house ?: '';
        $this->str = $str ?: '';
        $this->kv = $kv ?: '';
    }

    /**
     * @return string
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * @return string
     */
    public function getStreet(): string
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getHouse(): string
    {
        return $this->house;
    }

    /**
     * @return string
     */
    public function getStr(): string
    {
        return $this->str;
    }

    /**
     * @return string
     */
    public function getKv(): string
    {
        return $this->kv;
    }

    public function getFullAddress(): string
    {
        $address = '';
        if ($this->street != "") $address .= ", " . $this->street;
        if ($this->house != "") $address .= ", д." . $this->house;
        if ($this->str != "") $address .= ", стр./корп." . $this->str;
        if ($this->kv != "") $address .= ", кв./оф." . $this->kv;
        return $address;
    }
}