<?php


namespace App\Model\Expense\Entity\SchetFakPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class From
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $inn;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $kpp;


    public function __construct(string $name, string $address, string $inn, string $kpp)
    {
        $this->name = $name;
        $this->address = $address;
        $this->inn = $inn;
        $this->kpp = $kpp;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getInn(): string
    {
        return $this->inn;
    }

    /**
     * @return string
     */
    public function getKpp(): string
    {
        return $this->kpp;
    }

}