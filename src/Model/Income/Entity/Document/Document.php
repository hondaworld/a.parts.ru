<?php


namespace App\Model\Income\Entity\Document;


use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Document
{
    /**
     * @ORM\Column(type="integer")
     */
    private $num;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $prefix;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $sufix;

    public function __construct(int $num, ?string $prefix, ?string $sufix)
    {
        $this->num = $num;
        $this->prefix = $prefix ?: '';
        $this->sufix = $sufix ?: '';
    }

    /**
     * @return int
     */
    public function getNum(): int
    {
        return $this->num;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getSufix(): string
    {
        return $this->sufix;
    }

    public function getDocumentNum(): string
    {
        return
            ($this->getPrefix() ? $this->getPrefix() . '-' : '') .
            $this->getNum() .
            ($this->getSufix() ? '-' . $this->getSufix() : '');
    }
}