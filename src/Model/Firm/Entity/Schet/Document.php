<?php


namespace App\Model\Firm\Entity\Schet;


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
    private $schet_num;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $document_prefix;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private $document_sufix;

    public function __construct(?int $schet_num = null, ?string $document_prefix = '', ?string $document_sufix = '')
    {
        $this->schet_num = $schet_num ?: 0;
        $this->document_prefix = $document_prefix ?: '';
        $this->document_sufix = $document_sufix ?: '';
    }

    public function update(int $schet_num, ?string $document_prefix = '', ?string $document_sufix = '')
    {
        $this->schet_num = $schet_num;
        $this->document_prefix = $document_prefix ?: '';
        $this->document_sufix = $document_sufix ?: '';
    }

    public function updatePrefixes(?string $document_prefix = '', ?string $document_sufix = '')
    {
        $this->document_prefix = $document_prefix ?: '';
        $this->document_sufix = $document_sufix ?: '';
    }

    /**
     * @return int
     */
    public function getSchetNum(): int
    {
        return $this->schet_num;
    }

    /**
     * @return string
     */
    public function getDocumentPrefix(): string
    {
        return $this->document_prefix;
    }

    /**
     * @return string
     */
    public function getDocumentSufix(): string
    {
        return $this->document_sufix;
    }

    public function getDocumentNum(): string
    {
        return
            ($this->getDocumentPrefix() ? $this->getDocumentPrefix() . '-' : '') .
            $this->getSchetNum() .
            ($this->getDocumentSufix() ? '-' . $this->getDocumentSufix() : '');
    }
}