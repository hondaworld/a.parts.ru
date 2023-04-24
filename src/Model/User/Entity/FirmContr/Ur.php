<?php


namespace App\Model\User\Entity\FirmContr;

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

    public function __construct(
        ?string $organization = '',
        ?string $inn = '',
        ?string $kpp = '',
        ?string $okpo = '',
        ?string $ogrn = '',
        ?bool $isNDS = false
    )
    {
        $this->organization = $organization ?: '';
        $this->inn = $inn ?: '';
        $this->kpp = $kpp ?: '';
        $this->okpo = $okpo ?: '';
        $this->ogrn = $ogrn ?: '';
        $this->isNDS = $isNDS ?: false;
    }

    public function getOrganization(): string
    {
        return $this->organization;
    }

    public function getOrganizationWithInnAndKpp(): string
    {
        return $this->getOrganization() . ', ИНН/КПП ' . $this->getInn() . '/' . $this->getKpp();
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
}
