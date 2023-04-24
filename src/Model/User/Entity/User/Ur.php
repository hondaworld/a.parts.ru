<?php


namespace App\Model\User\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Ur
{
    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    private $organization;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $inn;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $kpp;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $okpo;

    /**
     * @var string
     * @ORM\Column(type="string", length=30, nullable=false)
     */
    private $ogrn;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isNDS")
     */
    private $isNDS;

    /**
     * @var bool
     * @ORM\Column(type="boolean", name="isUr")
     */
    private $isUr;

    /**
     * @var string
     * @ORM\Column(type="string", length=50, nullable=false)
     */
    private $dogovor_num;

    /**
     * @var \DateTime
     * @ORM\Column(type="date", nullable=false)
     */
    private $dogovor_date;

    public function __construct(
        ?string $organization = '',
        ?string $inn = '',
        ?string $kpp = '',
        ?string $okpo = '',
        ?string $ogrn = '',
        ?bool $isNDS = false,
        ?bool $isUr = false,
        ?string $dogovor_num = '',
        ?\DateTime $dogovor_date = null
    )
    {

        $this->organization = $organization ?: '';
        $this->inn = $inn ?: '';
        $this->kpp = $kpp ?: '';
        $this->okpo = $okpo ?: '';
        $this->ogrn = $ogrn ?: '';
        $this->isNDS = $isNDS ?: false;
        $this->isUr = $isUr ?: false;
        $this->dogovor_num = $dogovor_num ?: '';
        $this->dogovor_date = $dogovor_date;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function getOrganizationWithInnAndKpp(): string
    {
        $organization = $this->getOrganization();

        if ($this->getInn() && $this->getKpp()) {
            $organization .= ', ИНН/КПП ' . $this->getInn() . '/' . $this->getKpp();
        } elseif ($this->getInn()) {
            $organization .= ', ИНН ' . $this->getInn();
        } elseif ($this->getKpp()) {
            $organization .= ', КПП ' . $this->getKpp();
        }

        return $organization;
    }

    public function getInn(): string
    {
        return $this->inn;
    }

    public function getKpp(): string
    {
        return $this->kpp;
    }

    public function getOkpo(): string
    {
        return $this->okpo;
    }

    public function getOgrn(): string
    {
        return $this->ogrn;
    }

    public function isNDS(): bool
    {
        return $this->isNDS;
    }

    public function isUr(): bool
    {
        return $this->isUr;
    }

    public function getDogovorNum(): string
    {
        return $this->dogovor_num;
    }

    public function getDogovorDate(): ?\DateTime
    {
        if ($this->dogovor_date && $this->dogovor_date->format('Y') == '-0001') return null;
        return $this->dogovor_date;
    }

    public function getOsnName(): string
    {
        if ($this->getDogovorNum() == '') return '';
        $osn = 'Договор №' . $this->getDogovorNum();
        if ($this->getDogovorDate()) {
            $osn .= ' от ' . $this->getDogovorDate()->format('d.m.Y');
        }
        return $osn;
    }
}
