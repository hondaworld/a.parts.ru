<?php


namespace App\Model\Income\Entity\Document;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Osn
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $nakladnaya;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $schet;

    public function __construct(?string $name = '', ?string $nakladnaya = '', ?string $schet = '')
    {
        $this->name = $name ?: '';
        $this->nakladnaya = $nakladnaya ?: '';
        $this->schet = $schet ?: '';
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
    public function getNakladnaya(): string
    {
        return $this->nakladnaya;
    }

    /**
     * @return string
     */
    public function getSchet(): string
    {
        return $this->schet;
    }

}