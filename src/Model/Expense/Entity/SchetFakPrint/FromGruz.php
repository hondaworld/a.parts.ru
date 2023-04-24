<?php


namespace App\Model\Expense\Entity\SchetFakPrint;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class FromGruz
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $gruz_name;

    /**
     * @ORM\Column(type="text")
     */
    private $gruz_address;


    public function __construct(string $gruz_name, string $gruz_address)
    {
        $this->gruz_name = $gruz_name;
        $this->gruz_address = $gruz_address;
    }

    /**
     * @return string
     */
    public function getGruzName(): string
    {
        return $this->gruz_name;
    }

    /**
     * @return string
     */
    public function getGruzAddress(): string
    {
        return $this->gruz_address;
    }

}